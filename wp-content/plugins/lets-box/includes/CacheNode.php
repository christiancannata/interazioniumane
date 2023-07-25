<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\LetsBox;

class CacheNode
{
    /**
     * ID of the Node = ID of the Cached Entry.
     *
     * @var mixed
     */
    private $_id;

    /**
     * ID of the Account.
     *
     * @var mixed
     */
    private $_account_id;

    /**
     * The NAME of the node = NAME of the Cached Entry.
     *
     * @var string
     */
    private $_name;

    /**
     * The cached Entry.
     *
     * @var Entry
     */
    private $_entry;

    /**
     * Contains the array of parents
     * NOTICE: Some Cloud services can have multiple parents per folder.
     *
     * @var CacheNode[]
     */
    private $_parents = [];

    /**
     * Is the parent of this node already found/cached?
     *
     * @var bool
     */
    private $_parents_found = false;

    /**
     * Contains the array of children.
     *
     * @var CacheNode[]
     */
    private $_children = [];

    /**
     * Are the children already found/cached?
     *
     * @var bool
     */
    private $_children_loaded = false;

    /**
     * Are all subfolders inside this node found.
     *
     * @var bool
     */
    private $_all_childfolders_loaded = false;

    /**
     * Is the node the root of account?
     *
     * @var bool
     */
    private $_root = false;

    /**
     * When does this node expire? Value is set in the Cache of the Cloud Service.
     *
     * @var int
     */
    private $_expires;

    /**
     * Entry is only loaded via GetFolder or GetEntry, not when the tree is built.
     *
     * @var bool
     */
    private $_loaded = false;

    // In some special cases, an entry or folder should be hidden
    private $_hidden = false;
    private $_shared_links = [];
    private $_temporarily_links = [];

    public function __construct($params = null)
    {
        if (!empty($params)) {
            foreach ($params as $key => $val) {
                $this->{$key} = $val;
            }
        }
    }

    public function __serialize()
    {
        return [
            '_id' => $this->_id,
            '_account_id' => $this->_account_id,
            '_name' => $this->_name,
            '_parents' => array_keys($this->_parents),
            '_parents_found' => $this->_parents_found,
            '_children_loaded' => $this->_children_loaded,
            '_all_childfolders_loaded' => $this->_all_childfolders_loaded,
            '_root' => $this->_root,
            '_hidden' => $this->_hidden,
            '_entry' => $this->_entry,
            '_expires' => $this->_expires,
            '_loaded' => $this->_loaded,
            '_shared_links' => $this->_shared_links,
            '_temporarily_links' => $this->_temporarily_links,
        ];
    }

    public function __unserialize($data)
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function get_id()
    {
        return $this->_id;
    }

    public function get_account_id()
    {
        return $this->_account_id;
    }

    public function set_name($name)
    {
        $this->_name = $name;

        return $this;
    }

    public function get_name()
    {
        return $this->_name;
    }

    public function has_entry()
    {
        return null !== $this->get_entry();
    }

    /**
     * @return \TheLion\LetsBox\Entry
     */
    public function get_entry()
    {
        return $this->_entry;
    }

    /**
     * @param \TheLion\LetsBox\Entry $entry
     *
     * @return \TheLion\LetsBox\CacheNode
     */
    public function set_entry($entry)
    {
        $this->_entry = $entry;

        return $this;
    }

    public function has_parents()
    {
        return count($this->_parents) > 0;
    }

    /**
     * @return \TheLion\LetsBox\CacheNode
     */
    public function get_parents()
    {
        return $this->_parents;
    }

    public function get_first_parent()
    {
        return reset($this->_parents);
    }

    public function set_parent(CacheNode $pnode)
    {
        if (false === $this->get_parents_found()) {
            $this->remove_parents();
            $this->_parents_found = true;
        }

        $this->_parents[$pnode->get_id()] = $pnode;
        $this->_parents[$pnode->get_id()]->add_child($this);

        return $this;
    }

    public function remove_parent_by_id($id)
    {
        if ($this->has_parents() && isset($this->_parents[$id])) {
            $parent = $this->_parents[$id];
            if ($parent instanceof CacheNode) {
                $parent->remove_child($this);
            }

            unset($this->_parents[$id]);
        }
    }

    public function remove_parents()
    {
        if ($this->has_parents()) {
            foreach ($this->get_parents() as $parent) {
                $this->remove_parent($parent);
            }
        }

        return $this;
    }

    public function remove_parent($pnode)
    {
        if ($pnode instanceof CacheNode) {
            return $this->remove_parent_by_id($pnode->get_id());
        }

        return $this->remove_parent_by_id($pnode);
    }

    public function is_in_folder($parent_id)
    {
        // If parent folder is the root of the account
        if (0 == $parent_id) {
            return true;
        }

        // Is node just the folder?
        if ($this->get_id() === $parent_id) {
            return true;
        }

        // Has the node Parents?
        if (false === $this->has_parents()) {
            return false;
        }

        foreach ($this->get_parents() as $parent) {
            // First check if one of the parents is the root folder
            if (true === $parent->is_in_folder($parent_id)) {
                return true;
            }
        }

        return false;
    }

    public function set_root($value = true)
    {
        $this->_root = $value;

        return $this;
    }

    public function is_root()
    {
        return $this->_root;
    }

    public function set_parents_found($value = true)
    {
        $this->_parents_found = $value;

        return $this;
    }

    public function get_parents_found()
    {
        return $this->_parents_found;
    }

    public function has_children()
    {
        return count($this->_children) > 0;
    }

    /**
     * @return \TheLion\LetsBox\CacheNode[]
     */
    public function get_children()
    {
        return $this->_children;
    }

    public function add_child(CacheNode $cnode)
    {
        $this->_children[$cnode->get_id()] = $cnode;

        return $this;
    }

    public function remove_child_by_id($id)
    {
        unset($this->_children[$id]);
    }

    public function remove_child(CacheNode $cnode)
    {
        unset($this->_children[$cnode->get_id()]);

        return $this;
    }

    public function remove_children()
    {
        foreach ($this->get_children() as $child) {
            $this->remove_child($child);
        }

        return $this;
    }

    public function has_loaded_children()
    {
        return $this->_children_loaded;
    }

    public function set_loaded_children($value = true)
    {
        $this->_children_loaded = $value;

        return $this->_children_loaded;
    }

    public function has_loaded_all_childfolders()
    {
        return $this->_all_childfolders_loaded;
    }

    public function set_loaded_all_childfolders($value = true)
    {
        foreach ($this->get_all_sub_folders() as $child_folder) {
            $child_folder->set_loaded_all_childfolders($value);
        }

        $this->_all_childfolders_loaded = $value;

        return $this->_all_childfolders_loaded;
    }

    public function is_expired()
    {
        if (null === $this->get_entry()) {
            return true;
        }

        if (!$this->is_loaded()) {
            return true;
        }
        // Folders itself cannot expire
        if ($this->get_entry()->is_dir() && !$this->has_children()) {
            return false;
        }

        // Check if the entry needs to be refreshed
        if ($this->get_entry()->is_file() && $this->_expires < time()) {
            return true;
        }

        // Also check if the files in a folder are still OK
        if ($this->has_children()) {
            foreach ($this->get_children() as $child) {
                if (!$child->has_entry()) {
                    return true;
                }

                if ($child->get_entry()->is_file() && $child->_expires < time()) {
                    return true;
                }
            }
        }

        return false;
    }

    public function get_all_child_folders()
    {
        $list = [];
        if ($this->has_children()) {
            foreach ($this->get_children() as $child) {
                if ($child->has_entry() && $child->get_entry()->is_dir()) {
                    $list[$child->get_id()] = $child;
                }

                if ($child->has_children()) {
                    $folders_in_child = $child->get_all_child_folders();
                    $list = array_merge($list, $folders_in_child);
                }
            }
        }

        return $list;
    }

    public function get_all_sub_folders()
    {
        $list = [];
        if ($this->has_children()) {
            foreach ($this->get_children() as $child) {
                if ($child->has_entry() && $child->get_entry()->is_dir()) {
                    $list[$child->get_id()] = $child;
                }
            }
        }

        return $list;
    }

    public function get_all_parent_folders()
    {
        $list = [];
        if ($this->has_parents()) {
            foreach ($this->get_parents() as $parent) {
                $list[$parent->get_id()] = $parent;
                $list = array_merge($list, $parent->get_all_parent_folders());
            }
        }

        return $list;
    }

    public function get_linked_users()
    {
        $linked_users = [];
        $all_parent_folders = $this->get_all_parent_folders();

        // First obtain all users that are manually linked to the entry or its parents
        global $wpdb;

        $meta_query = [
            'relation' => 'OR',
            [
                'key' => $wpdb->prefix.'lets_box_linkedto',
                'value' => '"'.$this->get_id().'"',
                'compare' => 'LIKE',
            ],
        ];

        if (count($all_parent_folders) > 0) {
            foreach ($all_parent_folders as $parent_folder) {
                $meta_query[] = [
                    'key' => $wpdb->prefix.'lets_box_linkedto',
                    'value' => '"'.$parent_folder->get_id().'"',
                    'compare' => 'LIKE',
                ];
            }
        }

        $manually_linked_users = get_users(['meta_query' => $meta_query]);

        foreach ($manually_linked_users as $userdata) {
            $linked_users[$userdata->ID] = $userdata;
        }

        /* Secondly obtain all users that are automatically linked to the entry or its parents
         * The folder has to contain the email address of the user */

        $all_parent_folders[] = $this; // Add current entry to prevent duplicate code

        foreach ($all_parent_folders as $parent) {
            $extracted_email = Helpers::extract_email_from_string($parent->get_name());

            if (false === $extracted_email) {
                continue;
            }

            $userdata = \WP_User::get_data_by('email', $extracted_email);

            if (!$userdata) {
                continue;
            }

            $linked_users[$userdata->ID] = $userdata;
        }

        return $linked_users;
    }

    public function get_path($to_parent_id)
    {
        if ($to_parent_id === $this->get_id()) {
            return '/'.$this->get_entry()->get_name();
        }

        if ($this->has_parents()) {
            foreach ($this->get_parents() as $parent) {
                if ($parent->get_id() === $to_parent_id) {
                    return '/'.$this->get_entry()->get_name();
                }

                $path = $parent->get_path($to_parent_id);

                if (false !== $path) {
                    return $path.'/'.$this->get_entry()->get_name();
                }
            }
        }

        return false;
    }

    public function set_expired($value)
    {
        return $this->_expires = $value;
    }

    public function get_expired()
    {
        return $this->_expires;
    }

    public function set_loaded($value)
    {
        return $this->_loaded = $value;
    }

    public function is_loaded()
    {
        return $this->_loaded;
    }

    public function set_hidden($value)
    {
        return $this->_hidden = $value;
    }

    public function is_hidden()
    {
        return $this->_hidden;
    }

    public function add_temporarily_link($link, $format = 'default')
    {
        $this->_temporarily_links[$format] = [
            'url' => $link,
            'expires' => time() + (1 * 60 * 15), // According to the docs it is valid for 15 min
        ];
    }

    public function get_temporarily_link($format = 'default')
    {
        if (!isset($this->_temporarily_links[$format])) {
            return false;
        }

        if (!isset($this->_temporarily_links[$format]['url']) || empty($this->_temporarily_links[$format]['url'])) {
            return false;
        }

        if (!(empty($this->_temporarily_links[$format]['expires'])) && $this->_temporarily_links[$format]['expires'] < time() + 60) {
            return false;
        }

        return $this->_temporarily_links[$format]['url'];
    }

    public function add_shared_link($shared_link, $link_settings)
    {
        $url = $shared_link['url'];
        $expires = null;

        if (!empty($shared_link['unshared_at'])) {
            $dtime = \DateTime::createFromFormat(DATE_RFC3339, $shared_link['unshared_at'], new \DateTimeZone('UTC'));

            if ($dtime) {
                $expires = $dtime->getTimestamp();
            }
        }

        // Don't store shared links with expire date. Those are unique anyway
        if (!empty($link_settings['unshared_at'])) {
            return $url;
        }

        $hash = md5(serialize($link_settings));

        $this->_shared_links[$hash] = array_merge($link_settings, [
            'url' => $url,
            'expires' => $expires,
        ]);

        return $this->get_shared_link($link_settings);
    }

    public function get_shared_link($link_settings)
    {
        $hash = md5(serialize($link_settings));

        if (!isset($this->_shared_links[$hash])) {
            return false;
        }

        if (!empty($this->_shared_links[$hash]['expires'])) {
            if ($this->_shared_links[$hash]['expires'] < time() + 30) {
                return false;
            }
        }

        return $this->_shared_links[$hash]['url'];
    }
}
