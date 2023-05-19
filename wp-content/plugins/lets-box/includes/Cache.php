<?php
/**
 *
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2022, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\LetsBox;

class Cache
{
    /**
     * The single instance of the class.
     *
     * @var Cache
     */
    protected static $_instance;

    /**
     * Set after how much time the cached noded should be refreshed.
     * This value can be overwritten by Cloud Service Cache classes
     * Default:  needed for download/thumbnails urls (1 hour?).
     *
     * @var int
     */
    protected $_max_entry_age = 99999999999;

    /**
     * The file name of the requested cache. This will be set in construct.
     *
     * @var string
     */
    private $_cache_name;
    
    /**
     * Contains the location to the cache file.
     *
     * @var string
     */
    private $_cache_location;

    /**
     * Contains the file handle in case the plugin has to work
     * with a file for unlocking/locking.
     *
     * @var type
     */
    private $_cache_file_handle;

    /**
     * $_nodes contains all the cached files that are present
     * in the Cache File.
     *
     * @var \TheLion\LetsBox\CacheNode[]
     */
    private $_nodes = [];

    /**
     * Is set to true when a change has been made in the cache.
     * Forcing the plugin to save the cache when needed.
     *
     * @var bool
     */
    private $_updated = false;

    /**
     * $_last_update contains a timestamp of the latest check
     * for new updates.
     *
     * @var string
     */
    private $_last_check_for_update;

    /**
     * $_last_id contains an ID of the latest update check
     * This can be anything (e.g. a File ID or Change ID), it differs per Cloud Service.
     *
     * @var mixed
     */
    private $_last_check_token;

    /**
     * How often do we need to poll for changes? (default: 15 minutes)
     * Each Cloud service has its own optimum setting.
     * WARNING: Please don't lower this setting when you are not using your own Apps!!!
     *
     * @var int
     */
    private $_max_change_age = 900;

    public function __construct()
    {
        $cache_id = get_current_blog_id();
        if (null !== App::get_current_account()) {
            $cache_id = App::get_current_account()->get_id();
        }

        $this->_cache_name = Helpers::filter_filename($cache_id, false).'.index';
        $this->_cache_location = LETSBOX_CACHEDIR.'/'.$this->_cache_name;

        // Load Cache
        $this->load_cache();
    }

    public function __destruct()
    {
        $this->update_cache();
    }

    /**
     * Cache Instance.
     *
     * Ensures only one instance is loaded or can be loaded.
     *
     * @return Cache - Cache instance
     *
     * @static
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public static function instance_unload()
    {
        if (is_null(self::$_instance)) {
            return;
        }

        self::instance()->update_cache();
        self::$_instance = null;
    }

    public function load_cache()
    {
        $cache = $this->_read_local_cache('close');

        if (function_exists('gzdecode')) {
            $cache = @gzdecode($cache);
        }

        // 3: Unserialize the Cache, and reset if it became somehow corrupt
        if (!empty($cache) && !is_array($cache)) {
            $this->_unserialize_cache($cache);
        }

        // Set all Parent and Children
        if (count($this->_nodes) > 0) {
            foreach ($this->_nodes as $node) {
                if ($node->has_parents()) {
                    foreach ($node->get_parents() as $key => $parent_id) {
                        if (!$parent_id instanceof CacheNode) {
                            $node->remove_parent_by_id($key);
                        }
                        $parent_in_tree = $this->get_node_by_id($parent_id);
                        if (false !== $parent_in_tree) {
                            $node->set_parent($parent_in_tree);
                        }
                    }
                }

                if ($node->has_children()) {
                    foreach ($node->get_children() as $key => $child_id) {
                        if (!$child_id instanceof CacheNode) {
                            $node->remove_child_by_id($key);
                        }
                    }
                }

                // Remove leftovers
                if (!$node->has_children() && !$node->has_parents() && $node->is_expired() && !$node->is_hidden()) {
                    $this->remove_from_cache($node->get_id(), 'deleted');
                }

                // Remove trashed entries
                if ($node->get_entry() instanceof Entry) {
                    if ($node->get_entry()->get_trashed()) {
                        $this->remove_from_cache($node->get_id(), 'deleted');
                    }
                }
            }
        }
    }

    public function reset_cache()
    {
        $this->_nodes = [];

        $this->set_last_check_for_update();
        $this->set_last_check_token(null);
        $this->update_cache();
    }

    public function update_cache($clear_request_cache = true)
    {
        if ($this->is_updated()) {
            // Clear Cached Requests, not needed if we only pulled for updates without receiving any changes
            if ($clear_request_cache) {
                CacheRequest::clear_local_cache_for_shortcode(App::get_current_account()->get_id(), Processor::instance()->get_listtoken());
            }

            $saved = $this->_save_local_cache();

            $this->set_updated(false);
        }
    }

    public function is_cached($value, $findby = 'id', $as_parent = false)
    {
        // Find the node by ID/NAME
        $node = null;
        if ('id' === $findby) {
            $node = $this->get_node_by_id($value);
        } elseif ('name' === $findby) {
            $node = $this->get_node_by_name($value);
        }

        // Return if nothing can be found in the cache
        if (empty($node)) {
            return false;
        }

        if (null === $node->get_entry()) {
            return false;
        }

        if (!$as_parent && !$node->is_loaded()) {
            return false;
        }

        /* Check if the requested node is expired
         * In that case, unset the node and remove the child nodes
         *  */
        if (!$as_parent && $node->is_expired()) {
            if ($node->get_entry()->is_dir()) {
                return Client::instance()->update_expired_folder($node);
            }

            return Client::instance()->update_expired_entry($node);
        }

        // Check if the children of the node are loaded.
        if (!$as_parent && !$node->has_loaded_children()) {
            return false;
        }

        return $node;
    }

    public function build_folder_structure($entries)
    {
        // Add all files in folder to cache
        // @var $entry Entry
        foreach ($entries as $entry) {
            $cached_node = $this->add_node($entry);
            $cached_node->set_entry($entry);
            $cached_node->set_loaded(false);

            if (!$entry->has_parents()) {
                $cached_node->set_hidden(true);
            }
        }

        // Walk through the cache to linked all files to the parent
        foreach ($this->get_nodes() as $cached_node) {
            if (!$cached_node->get_entry()->has_parents()) {
                continue;
            }

            foreach ($cached_node->get_entry()->get_parents() as $parent_id) {
                $parent_in_tree = $this->is_cached($parent_id, 'id', 'as_parent');
                // Parent does already exists in our cache
                if (false !== $parent_in_tree) {
                    $cached_node->set_parent($parent_in_tree);
                    $cached_node->set_parents_found();
                } else {
                    $cached_node->set_hidden(true);
                }
            }
        }
    }

    /**
     * @param \TheLion\LetsBox\Entry $entry
     *
     * @return \TheLion\LetsBox\CacheNode
     */
    public function add_to_cache(Entry $entry)
    {
        // Check if entry is present in cache
        $cached_node = $this->get_node_by_id($entry->get_id());

        /* If entry is not yet present in the cache,
         * create a new Node
         */
        if (false === $cached_node) {
            $cached_node = $this->add_node($entry);
        } else {
            $cached_node->set_name($entry->get_name());
        }

        $this->set_updated();

        // Set new Expire date
        $cached_node->set_expired(time() + $this->get_max_entry_age());

        // Set new Entry in node
        $cached_node->set_entry($entry);
        $cached_node->set_loaded(true);

        // Set Loaded_Children to true if entry isn't a folder
        if ($entry->is_file()) {
            $cached_node->set_loaded_children(true);
        }

        // If $entry hasn't parents, it is the root
        if (!$entry->has_parents()) {
            $cached_node->set_parents_found(true);
            $cached_node->set_root();

            return $cached_node;
        }

        /*
         * If parents of $entry doesn't exist in our cache yet,
         * We need to get it via the API
         */
        $getparents = [];
        foreach ($entry->get_parents() as $parent_id) {
            $parent_in_tree = $this->is_cached($parent_id, 'id', 'as_parent');

            if (empty($parent_in_tree)) {
                $newparent = Client::instance()->get_folder($parent_id, false);

                if (empty($newparent)) {
                    // If the parent still can't be found?, set it to root node
                    $parent_in_tree = $this->get_root_node();
                } else {
                    $parent_in_tree = $newparent['folder'];
                }
            }

            $cached_node->set_parent($parent_in_tree);
        }

        $cached_node->set_parents_found(true);

        $this->set_updated();

        // Return the cached Node
        return $cached_node;
    }

    public function remove_from_cache($entry_id, $reason = 'update', $parent_id = false)
    {
        $node = $this->get_node_by_id($entry_id);

        if (false === $node) {
            return false;
        }

        if ('update' === $reason) {
            $node->remove_parents();
        } elseif ('moved' === $reason) {
            $node->remove_parents();
        } elseif ('deleted' === $reason) {
            $node->remove_parents();
            unset($this->_nodes[$entry_id]);
        }

        $this->set_updated();

        return true;
    }

    /**
     * @return bool|\TheLion\LetsBox\CacheNode
     */
    public function get_root_node()
    {
        if (0 === count($this->get_nodes())) {
            return false;
        }

        foreach ($this->get_nodes() as $node) {
            if ($node->is_root()) {
                return $node;
            }
        }

        return false;
    }

    public function get_node_by_id($id)
    {
        if (!isset($this->_nodes[$id])) {
            return false;
        }

        return $this->_nodes[$id];
    }

    public function get_node_by_name($name, $parent = null)
    {
        if (!$this->has_nodes()) {
            return false;
        }

        $parent_id = ($parent instanceof CacheNode) ? $parent->get_id() : $parent;

        /**
         * @var \TheLion\LetsBox\CacheNode $node
         */
        foreach ($this->_nodes as $node) {
            if ($node->get_name() === $name) {
                if (null === $parent) {
                    return $node;
                }

                if ($node->is_in_folder($parent_id)) {
                    return $node;
                }
            }
        }

        return false;
    }

    public function has_nodes()
    {
        return count($this->_nodes) > 0;
    }

    /**
     * @return \TheLion\LetsBox\CacheNode[]
     */
    public function get_nodes()
    {
        return $this->_nodes;
    }

    public function add_node(Entry $entry)
    {
        // TODO: Set expire based on Cloud Service
        $cached_node = new CacheNode(
            [
                '_id' => $entry->get_id(),
                '_account_id' => App::get_current_account()->get_id(),
                '_name' => $entry->get_name(),
            ]
        );

        return $this->set_node($cached_node);
    }

    public function set_node(CacheNode $node)
    {
        $id = $node->get_id();
        $this->_nodes[$id] = $node;

        return $this->_nodes[$id];
    }

    public function pull_for_changes($force_update = false, $buffer = 10)
    {
        $force = (defined('FORCE_REFRESH') ? true : $force_update);

        // Check if we need to check for updates
        $current_time = time();
        $last_check_time = $this->get_last_check_for_update();
        $update_needed = ($last_check_time + $this->get_max_change_age());
        if (($current_time < $update_needed) && !$force) {
            return false;
        }
        if (true === $force && ($last_check_time > $current_time - $buffer)) {
            // Don't pull again if the request was within $buffer seconds
            return false;
        }

        // Reset Cache if the last time we used this cache is more than a day ago
        if (!empty($last_check_time) && $last_check_time < ($current_time - 60 * 60 * 24)) {
            Processor::reset_complete_cache(false);
            $this->set_last_check_for_update();

            return $this->update_cache();
        }

        $result = Client::instance()->get_changes($this->get_last_check_token());

        if (empty($result)) {
            return false;
        }

        $this->set_last_check_token($result['new_change_token']);
        $this->set_last_check_for_update();

        if (is_array($result['changes']) && count($result['changes']) > 0) {
            $result = $this->_process_changes($result['changes']);
            if (!defined('HAS_CHANGES')) {
                define('HAS_CHANGES', true);
            }

            $this->update_cache();

            return true;
        }
        $this->update_cache(false);

        return false;
    }

    public function is_updated()
    {
        return $this->_updated;
    }

    public function set_updated($value = true)
    {
        $this->_updated = (bool) $value;

        return $this->_updated;
    }

    public function get_cache_name()
    {
        return $this->_cache_name;
    }

    public function get_cache_location()
    {
        return $this->_cache_location;
    }

    public function get_last_check_for_update()
    {
        return $this->_last_check_for_update;
    }

    public function set_last_check_for_update($time = 'now')
    {
        $this->_last_check_for_update = ('now' === $time) ? time() : $time;
        $this->set_updated();

        return $this->_last_check_for_update;
    }

    public function get_last_check_token()
    {
        return $this->_last_check_token;
    }

    public function set_last_check_token($token)
    {
        $this->_last_check_token = $token;

        return $this->_last_check_token;
    }

    public function get_max_entry_age()
    {
        return $this->_max_entry_age;
    }

    public function set_max_entry_age($value)
    {
        return $this->_max_entry_age = $value;
    }

    public function get_max_change_age()
    {
        return $this->_max_change_age;
    }

    public function set_max_change_age($value)
    {
        return $this->_max_change_age = $value;
    }

    protected function _read_local_cache($close = false)
    {
        $handle = $this->_get_cache_file_handle();
        if (empty($handle)) {
            $this->_create_local_lock(LOCK_SH);
        }

        clearstatcache();
        rewind($this->_get_cache_file_handle());

        $data = null;
        if (filesize($this->get_cache_location()) > 0) {
            $data = fread($this->_get_cache_file_handle(), filesize($this->get_cache_location()));
        }

        if (false !== $close) {
            $this->_unlock_local_cache();
        }

        return $data;
    }

    protected function _create_local_lock($type)
    {
        // Check if file exists
        $file = $this->get_cache_location();

        if (!file_exists($file)) {
            @file_put_contents($file, $this->_serialize_cache());

            if (!is_writable($file)) {
                error_log('[WP Cloud Plugin message]: '.sprintf('Cache file (%s) is not writable', $file));

                exit(sprintf('Cache file (%s) is not writable', $file));
            }
        }

        // Check if the file is more than 1 minute old.
        $requires_unlock = ((filemtime($file) + 60) < time());

        // Temporarily workaround when flock is disabled. Can cause problems when plugin is used in multiple processes
        if (false !== strpos(ini_get('disable_functions'), 'flock')) {
            $requires_unlock = false;
        }

        // Check if file is already opened and locked in this process
        $handle = $this->_get_cache_file_handle();
        if (empty($handle)) {
            $handle = fopen($file, 'c+');
            if (!is_resource($handle)) {
                error_log('[WP Cloud Plugin message]: '.sprintf('Cache file (%s) is not writable', $file));

                exit(sprintf('Cache file (%s) is not writable', $file));
            }
            $this->_set_cache_file_handle($handle);
        }

        @set_time_limit(60);

        if (!flock($this->_get_cache_file_handle(), $type)) {
            /*
             * If the file cannot be unlocked and the last time
             * it was modified was 1 minute, assume that
             * the previous process died and unlock the file manually
             */
            if ($requires_unlock) {
                $this->_unlock_local_cache();
                $handle = fopen($file, 'c+');
                $this->_set_cache_file_handle($handle);
            }
            // Try to lock the file again
            flock($this->_get_cache_file_handle(), LOCK_EX);
        }
        @set_time_limit(60);

        return true;
    }

    protected function _save_local_cache()
    {
        if (!$this->_create_local_lock(LOCK_EX)) {
            return false;
        }

        $data = $this->_serialize_cache($this);

        ftruncate($this->_get_cache_file_handle(), 0);
        rewind($this->_get_cache_file_handle());

        $result = fwrite($this->_get_cache_file_handle(), $data);

        $this->_unlock_local_cache();
        $this->set_updated(false);

        return true;
    }

    protected function _unlock_local_cache()
    {
        $handle = $this->_get_cache_file_handle();
        if (!empty($handle)) {
            flock($this->_get_cache_file_handle(), LOCK_UN);
            fclose($this->_get_cache_file_handle());
            $this->_set_cache_file_handle(null);
        }

        clearstatcache();

        return true;
    }

    protected function _set_cache_file_handle($handle)
    {
        return $this->_cache_file_handle = $handle;
    }

    protected function _get_cache_file_handle()
    {
        return $this->_cache_file_handle;
    }

    private function _process_changes($changes = [])
    {
        foreach ($changes as $entry_id => $change) {
            if ('deleted' === $change) {
                $this->remove_from_cache($entry_id, 'deleted');
            } else {
                // Update cache with new entry
                if ($change instanceof Entry) {
                    // Keep thumbnails as that isn't yet provided by API
                    // $old_cached_entry = $this->get_node_by_id($entry_id);
                    $cached_entry = $this->add_to_cache($change);
                    if ($change->is_file()) {
                        $cached_entry->set_expired(time() - 1);
                    } else {
                        $cached_entry->set_loaded(false);
                    }
                }
            }
        }

        $this->set_updated(true);
    }

    private function _serialize_cache()
    {
        $data = [
            '_nodes' => $this->_nodes,
            '_last_check_token' => $this->_last_check_token,
            '_last_check_for_update' => $this->_last_check_for_update,
        ];

        $data_str = serialize($data);

        if (function_exists('gzencode')) {
            $data_str = gzencode($data_str);
        }

        return $data_str;
    }

    private function _unserialize_cache($data)
    {
        $values = unserialize($data);
        if (false !== $values) {
            foreach ($values as $key => $value) {
                $this->{$key} = $value;
            }
        }
    }
}