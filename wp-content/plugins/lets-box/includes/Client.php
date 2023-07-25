<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\LetsBox;

if (!function_exists('\Box\autoload')) {
    require_once LETSBOX_ROOTDIR.'/vendors/API/autoload.php';
}

class Client
{
    /**
     * The single instance of the class.
     *
     * @var Client
     */
    protected static $_instance;

    /**
     * Client Instance.
     *
     * Ensures only one instance is loaded or can be loaded.
     *
     * @return Client - Client instance
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

    /**
     * Get file.
     *
     * @param string $entry_id
     * @param bool   $checkauthorized
     *
     * @return bool|\TheLion\LetsBox\CacheNode
     */
    public function get_entry($entry_id = false, $checkauthorized = true)
    {
        if (false === $entry_id) {
            $entry_id = Processor::instance()->get_requested_entry();
        }

        // Get metadata if entry isn't cached
        $cached_node = API::get_entry($entry_id);

        if (true === $checkauthorized) {
            if ('root' !== $entry_id && !Processor::instance()->_is_entry_authorized($cached_node)) {
                return false;
            }
        }

        return $cached_node;
    }

    /**
     * Get folders and files.
     *
     * @param string $folderid
     * @param bool   $checkauthorized
     *
     * @return array|bool
     */
    public function get_folder($folderid = false, $checkauthorized = true)
    {
        if (false === $folderid) {
            $folderid = Processor::instance()->get_requested_entry();
        }

        $cachedfolder = API::get_folder($folderid);

        // Check if folder is in the shortcode-set rootfolder
        if (true === $checkauthorized) {
            if (!Processor::instance()->_is_entry_authorized($cachedfolder)) {
                return false;
            }
        }

        return ['folder' => $cachedfolder, 'contents' => $cachedfolder->get_children()];
    }

    public function update_expired_entry(CacheNode $cached_node)
    {
        $entry = $cached_node->get_entry();

        try {
            if ($entry->is_dir()) {
                $api_entry = App::instance()->get_sdk_client()->getFolderFromBox($entry->get_id());
            } else {
                $api_entry = App::instance()->get_sdk_client()->getFileFromBox($entry->get_id());
            }

            $entry = new Entry($api_entry);
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('CLIENT Error on line %s: %s', __LINE__, $ex->getMessage()));

            return false;
        }

        return Cache::instance()->add_to_cache($entry);
    }

    public function update_expired_folder(CacheNode $cached_folder_node)
    {
        $cached_folder_node = API::get_folder($cached_folder_node->get_id());

        Cache::instance()->update_cache();

        return $cached_folder_node;
    }

    // Search entry by name

    public function search($query)
    {
        $searchedfolder = Processor::instance()->get_requested_entry();
        $cached_searchedfolder = $this->get_folder($searchedfolder);

        // Check if requested folder is allowed
        if (empty($cached_searchedfolder)) {
            return [];
        }

        // extensions
        $search_extensions = null;
        $allowed_extensions = Processor::instance()->get_shortcode_option('include_ext');
        if ('*' != $allowed_extensions[0]) {
            $search_extensions = join(',', $allowed_extensions);
        }

        // Set search field
        $search_contents = ('1' === Processor::instance()->get_shortcode_option('searchcontents'));
        $content_types = ($search_contents) ? 'name,description,file_content,tags' : 'name';

        $max_results = ('-1' !== Processor::instance()->get_shortcode_option('max_files')) ? (int) Processor::instance()->get_shortcode_option('max_files') : 100;

        $cached_nodes = API::search($query, $searchedfolder, $content_types, $search_extensions, null, $max_results, 0);

        if (0 === count($cached_nodes)) {
            return [];
        }

        foreach ($cached_nodes as $key => $cached_node) {
            if (false === Processor::instance()->_is_entry_authorized($cached_node)) {
                unset($cached_nodes[$key]);

                continue;
            }
        }

        return $cached_nodes;
    }

    // Delete multiple files from Box

    public function delete_entries($entries_to_delete = [])
    {
        $deleted_entries = [];

        foreach ($entries_to_delete as $target_entry_path) {
            $target_cached_entry = $this->get_entry($target_entry_path);

            if (false === $target_cached_entry) {
                continue;
            }

            $target_entry = $target_cached_entry->get_entry();

            if ($target_entry->is_file() && false === User::can_delete_files()) {
                error_log('[WP Cloud Plugin message]: '.sprintf('Failed to delete %s as user is not allowed to remove files.', $target_entry->get_path()));

                continue;
            }

            if ($target_entry->is_dir() && false === User::can_delete_folders()) {
                error_log('[WP Cloud Plugin message]: '.sprintf('Failed to delete %s as user is not allowed to remove folders.', $target_entry->get_path()));

                continue;
            }

            if ('1' === Processor::instance()->get_shortcode_option('demo')) {
                continue;
            }

            try {
                API::delete_entry($target_entry->get_id(), $target_entry->is_dir());
                $deleted_entries[$target_entry->get_id()] = $target_cached_entry;
            } catch (\Exception $ex) {
                continue;
            }
        }

        if ('1' === Processor::instance()->get_shortcode_option('notificationdeletion')) {
            Processor::instance()->send_notification_email('deletion', $deleted_entries);
        }

        return $deleted_entries;
    }

    // Rename entry from Box
    public function rename_entry($new_filename = null)
    {
        if ('1' === Processor::instance()->get_shortcode_option('demo')) {
            return new \WP_Error('broke', esc_html__('Failed to rename file.', 'wpcloudplugins'));
        }

        if (null === $new_filename) {
            return new \WP_Error('broke', esc_html__('No new name set', 'wpcloudplugins'));
        }

        // Get entry meta data
        $cached_node = Cache::instance()->is_cached(Processor::instance()->get_requested_entry());

        if (false === $cached_node) {
            $cached_node = $this->get_entry(Processor::instance()->get_requested_entry());
            if (false === $cached_node) {
                return new \WP_Error('broke', esc_html__('Failed to rename file.', 'wpcloudplugins'));
            }
        }

        // Check if user is allowed to delete from this dir
        if (!$cached_node->is_in_folder(Processor::instance()->get_last_folder())) {
            return new \WP_Error('broke', esc_html__('You are not authorized to rename files in this directory', 'wpcloudplugins'));
        }

        $entry = $cached_node->get_entry();

        // Check user permission
        if (!$entry->get_permission('canrename')) {
            // return new \WP_Error('broke', esc_html__('You are not authorized to rename this file or folder', 'wpcloudplugins'));
        }

        // Check if entry is allowed
        if (!Processor::instance()->_is_entry_authorized($cached_node)) {
            return new \WP_Error('broke', esc_html__('You are not authorized to rename this file or folder', 'wpcloudplugins'));
        }

        if ($entry->is_dir() && (false === User::can_rename_folders())) {
            return new \WP_Error('broke', esc_html__('You are not authorized to rename folder', 'wpcloudplugins'));
        }

        if ($entry->is_file() && (false === User::can_rename_files())) {
            return new \WP_Error('broke', esc_html__('You are not authorized to rename this file', 'wpcloudplugins'));
        }

        $extension = $entry->get_extension();
        $name = (!empty($extension)) ? $new_filename.'.'.$extension : $new_filename;
        $updaterequest = ['name' => $name];

        try {
            $renamed_node = API::patch($entry->get_id(), $updaterequest, $entry->is_dir());

            do_action('letsbox_log_event', 'letsbox_renamed_entry', $renamed_node, ['old_name' => $entry->get_name()]);
        } catch (\Exception $ex) {
            return new \WP_Error('broke', esc_html__('Failed to rename file.', 'wpcloudplugins'));
        }

        return $renamed_node;
    }

    // Move & Copy
    public function move_entries($entries, $target, $copy = false)
    {
        $entries_to_move = [];

        $cached_target = $this->get_entry($target);
        $cached_current_folder = $this->get_entry(Processor::instance()->get_last_folder());

        if (false === $cached_target) {
            error_log('[WP Cloud Plugin message]: Failed to move as target folder is not found.');

            return $entries_to_move;
        }

        foreach ($entries as $entry_id) {
            $cached_node = $this->get_entry($entry_id);

            if (false === $cached_node) {
                continue;
            }

            $entry = $cached_node->get_entry();

            if (!$copy && $entry->is_dir() && (false === User::can_move_folders())) {
                error_log('[WP Cloud Plugin message]: '.sprintf('Failed to move %s as user is not allowed to move folders.', $cached_target->get_path(Processor::instance()->get_root_folder())));
                $entries_to_move[$cached_node->get_id()] = false;

                continue;
            }

            if ($copy && $entry->is_dir() && (false === User::can_copy_folders())) {
                error_log('[WP Cloud Plugin message]: '.sprintf('Failed to copy %s as user is not allowed to copy folders.', $cached_target->get_path(Processor::instance()->get_root_folder())));
                $entries_to_move[$cached_node->get_id()] = false;

                continue;
            }

            if (!$copy && $entry->is_file() && (false === User::can_move_files())) {
                error_log('[WP Cloud Plugin message]: '.sprintf('Failed to move %s as user is not allowed to move files.', $cached_target->get_path(Processor::instance()->get_root_folder())));
                $entries_to_move[$cached_node->get_id()] = false;

                continue;
            }

            if ($copy && $entry->is_file() && (false === User::can_copy_files())) {
                error_log('[WP Cloud Plugin message]: '.sprintf('Failed to copy %s as user is not allowed to copy files.', $cached_target->get_path(Processor::instance()->get_root_folder())));
                $entries_to_move[$cached_node->get_id()] = false;

                continue;
            }

            if ('1' === Processor::instance()->get_shortcode_option('demo')) {
                $entries_to_move[$cached_node->get_id()] = false;

                continue;
            }

            // Check if user is allowed to delete from this dir
            if (!$cached_node->is_in_folder($cached_current_folder->get_id())) {
                error_log('[WP Cloud Plugin message]: '.sprintf('Failed to move %s as user is not allowed to move items in this directory.', $cached_target->get_path(Processor::instance()->get_root_folder())));
                $entries_to_move[$cached_node->get_id()] = false;

                continue;
            }

            // Check user permission
            if (!$entry->get_permission('canmove')) {
                error_log('[WP Cloud Plugin message]: '.sprintf('Failed to move %s as the sharing permissions on it prevent this.', $cached_target->get_path(Processor::instance()->get_root_folder())));
                $entries_to_move[$cached_node->get_id()] = false;

                continue;
            }

            try {
                if ($copy) {
                    $updated_node = API::copy($entry_id, $cached_target->get_id(), $entry->is_dir());
                } else {
                    $updated_node = API::move($entry_id, $cached_target->get_id(), $entry->is_dir());

                    $entries_to_move[$cached_node->get_id()] = $updated_node;
                }
            } catch (\Exception $ex) {
                $entries_to_move[$cached_node->get_id()] = false;

                continue;
            }
        }

        return $entries_to_move;
    }

    // Edit description of entry

    public function update_description($new_description = null)
    {
        if (null === $new_description) {
            return new \WP_Error('broke', esc_html__('No new description set', 'wpcloudplugins'));
        }

        // Get entry meta data
        $cached_node = Cache::instance()->is_cached(Processor::instance()->get_requested_entry());

        if (false === $cached_node) {
            $cached_node = $this->get_entry(Processor::instance()->get_requested_entry());
            if (false === $cached_node) {
                return new \WP_Error('broke', esc_html__('Failed to edit file.', 'wpcloudplugins'));
            }
        }

        // Check if user is allowed to delete from this dir
        if (!$cached_node->is_in_folder(Processor::instance()->get_last_folder())) {
            return new \WP_Error('broke', esc_html__('You are not authorized to edit files in this directory', 'wpcloudplugins'));
        }

        $entry = $cached_node->get_entry();

        // Check if entry is allowed
        if (!Processor::instance()->_is_entry_authorized($cached_node)) {
            return new \WP_Error('broke', esc_html__('You are not authorized to edit this file or folder', 'wpcloudplugins'));
        }

        // Set new description, and update the entry
        $updaterequest = ['description' => $new_description];

        try {
            $updated_node = API::patch($entry->get_id(), $updaterequest, $entry->is_dir());

            do_action('letsbox_log_event', 'letsbox_updated_description', $updated_node, ['description' => $new_description]);
        } catch (\Exception $ex) {
            return new \WP_Error('broke', esc_html__('Failed to edit file.', 'wpcloudplugins'));
        }

        return $updated_node->get_entry()->get_description();
    }

    // Add directory to Box
    public function add_folder($new_folder_name = null)
    {
        if ('1' === Processor::instance()->get_shortcode_option('demo')) {
            return new \WP_Error('broke', esc_html__('Failed to add folder', 'wpcloudplugins'));
        }

        if (null === $new_folder_name) {
            return new \WP_Error('broke', esc_html__('No new foldername set', 'wpcloudplugins'));
        }

        // Get entry meta data of current folder
        $cached_node = Cache::instance()->is_cached(Processor::instance()->get_last_folder());

        if (false === $cached_node) {
            $cached_node = $this->get_entry(Processor::instance()->get_last_folder());
            if (false === $cached_node) {
                return new \WP_Error('broke', esc_html__('Failed to add file.', 'wpcloudplugins'));
            }
        }

        if (!Processor::instance()->_is_entry_authorized($cached_node)) {
            return new \WP_Error('broke', esc_html__('You are not authorized to add folders in this directory', 'wpcloudplugins'));
        }

        $currentfolder = $cached_node->get_entry();

        // Check user permission
        if (!$currentfolder->get_permission('canadd')) {
            return new \WP_Error('broke', esc_html__('You are not authorized to add a folder', 'wpcloudplugins'));
        }

        try {
            $folder_node = API::create_folder($new_folder_name, $currentfolder->get_id());
        } catch (\Exception $ex) {
            return new \WP_Error('broke', esc_html__('Failed to add folder', 'wpcloudplugins'));
        }

        return $folder_node;
    }

    public function preview_entry()
    {
        // Get file meta data
        $cached_node = $this->get_entry();

        if (false === $cached_node) {
            exit('-1');
        }

        $entry = $cached_node->get_entry();
        if (false === $entry->get_can_preview_by_cloud()) {
            exit('-1');
        }

        if (false === User::can_preview()) {
            exit('-1');
        }

        do_action('letsbox_log_event', 'letsbox_previewed_entry', $cached_node);

        $temporarily_link = $this->get_temporarily_embedded_link($cached_node);

        // Preview for Image files
        if (in_array($entry->get_extension(), ['jpg', 'jpeg', 'gif', 'png', 'webp', 'heic'])) {
            if ('original' === Processor::instance()->get_setting('loadimages') & false === User::can_download()) {
                // Redirect to download url
            }
        }

        if (User::can_download() && $entry->get_permission('candownload') && in_array(Processor::instance()->get_shortcode_option('mode'), ['files', 'search'])) {
            $temporarily_link .= '?showDownload=true';
        }

        header('Location: '.$temporarily_link);

        exit;
    }

    public function edit_entry()
    {
        // Get file meta data
        $cached_node = $this->get_entry();

        if (false === $cached_node) {
            exit('-1');
        }

        $entry = $cached_node->get_entry();
        if (false === $entry->get_can_edit_by_cloud()) {
            exit('-1');
        }

        // NOT YET SUPPORTED BY THE API. ENABLE THIS WHEN SUPPORT IS ADDED
        //        $edit_link = $this->get_shared_link($cached_node, 'edit');
        //
        //        if (empty($edit_link)) {
        //            error_log('[WP Cloud Plugin message]: ' . sprintf('Cannot create a editable ahared link %s', __LINE__));
        //            die();
        //        }
        //
        //        header('Location: ' . $edit_link);
        exit;
    }

    // Download file

    public function download_entry()
    {
        // Check if file is cached and still valid
        $cached = Cache::instance()->is_cached(Processor::instance()->get_requested_entry());

        if (false === $cached) {
            $cached_node = $this->get_entry(Processor::instance()->get_requested_entry());
        } else {
            $cached_node = $cached;
        }

        if (false === $cached_node) {
            exit;
        }

        $entry = $cached_node->get_entry();

        // get the last-modified-date of this very file
        $lastModified = $entry->get_last_edited();
        // get a unique hash of this file (etag)
        $etagFile = md5($lastModified);
        // get the HTTP_IF_MODIFIED_SINCE header if set
        $ifModifiedSince = (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false);
        // get the HTTP_IF_NONE_MATCH header if set (etag: unique file hash)
        $etagHeader = (isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false);

        header('Last-Modified: '.gmdate('D, d M Y H:i:s', $lastModified).' GMT');
        header("Etag: {$etagFile}");
        header('Expires: '.gmdate('D, d M Y H:i:s', time() + 60 * 5).' GMT');
        header('Cache-Control: must-revalidate');

        // check if page has changed. If not, send 304 and exit
        if (false !== $cached) {
            if (false !== $lastModified && (@strtotime($ifModifiedSince) == $lastModified || $etagHeader == $etagFile)) {
                // Send email if needed
                if ('1' === Processor::instance()->get_shortcode_option('notificationdownload')) {
                    Processor::instance()->send_notification_email('download', [$cached_node]);
                }

                do_action('letsbox_download', $cached_node);
                header('HTTP/1.1 304 Not Modified');

                exit;
            }
        }

        // Check if entry is allowed
        if (!Processor::instance()->_is_entry_authorized($cached_node)) {
            exit;
        }

        // Send email if needed
        if ('1' === Processor::instance()->get_shortcode_option('notificationdownload')) {
            Processor::instance()->send_notification_email('download', [$cached_node]);
        }

        // Get the complete file
        $extension = (isset($_REQUEST['extension'])) ? $_REQUEST['extension'] : 'default';
        $this->download_content($cached_node, $extension);

        exit;
    }

    public function download_content(CacheNode $cached_node, $extension = 'default')
    {
        // If there is a temporarily download url present for this file, just redirect the user
        $stream = (isset($_REQUEST['action']) && 'letsbox-stream' === $_REQUEST['action'] && !isset($_REQUEST['caption']));
        $stored_url = ($stream) ? get_transient('letsbox'.$cached_node->get_id().'_'.$cached_node->get_entry()->get_extension()) : get_transient('letsbox'.$cached_node->get_id().'_'.$cached_node->get_entry()->get_extension());
        if (false !== $stored_url && filter_var($stored_url, FILTER_VALIDATE_URL)) {
            do_action('letsbox_download', $cached_node, $stored_url);
            header('Location: '.$stored_url);

            exit;
        }

        $temporarily_link = $this->get_temporarily_link($cached_node, $extension);

        // Download Hook
        do_action('letsbox_download', $cached_node, $temporarily_link);

        $event_type = ($stream) ? 'letsbox_streamed_entry' : 'letsbox_downloaded_entry';
        do_action('letsbox_log_event', $event_type, $cached_node);

        header('Location: '.$temporarily_link);

        set_transient('letsbox'.(($stream) ? 'stream' : 'download').'_'.$cached_node->get_id().'_'.$cached_node->get_entry()->get_extension(), $temporarily_link, MINUTE_IN_SECONDS * 10);

        exit;
    }

    public function download_via_proxy(Entry $entry, $url)
    {
        // Stop WP from buffering
        wp_ob_end_flush_all();

        set_time_limit(500);

        $filename = basename($entry->get_name());

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; '.sprintf('filename="%s"; ', rawurlencode($filename)).sprintf("filename*=utf-8''%s", rawurlencode($filename)));
        header("Content-length: {$entry->get_size()}");

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 500);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($curl, $data) {
            echo $data;

            return strlen($data);
        });
        curl_exec($ch);
        curl_close($ch);

        exit;
    }

    public function stream_entry()
    {
        // Check if file is cached and still valid
        $cached = Cache::instance()->is_cached(Processor::instance()->get_requested_entry());

        if (false === $cached) {
            $cached_node = $this->get_entry(Processor::instance()->get_requested_entry());
        } else {
            $cached_node = $cached;
        }

        if (false === $cached_node) {
            exit;
        }

        $entry = $cached_node->get_entry();

        $extension = $entry->get_extension();
        $allowedextensions = ['mp4', 'm4v', 'ogg', 'ogv', 'webmv', 'mp3', 'm4a', 'oga', 'wav', 'webm', 'vtt', 'srt'];

        if (empty($extension) || !in_array($extension, $allowedextensions)) {
            exit;
        }

        // Download Captions directly
        if (in_array($extension, ['vtt', 'srt'])) {
            $temporarily_link = $this->get_temporarily_link($cached_node, 'default');
            $this->download_via_proxy($entry, $temporarily_link);

            exit;
        }

        $this->download_entry();
    }

    public function has_temporarily_link($cached_node, $extension = 'default')
    {
        if ($cached_node instanceof Entry) {
            $cached_node = $cached_node->get_id();
        }

        return !empty(Cache::instance()->get_node_by_id($cached_node)->get_temporarily_link());
    }

    public function get_temporarily_link($cached_node, $extension = 'default')
    {
        if ($cached_node instanceof Entry) {
            $cached_node = $this->get_entry($cached_node->get_id());
        }

        // 1: Get Temporarily link from cache
        if (false !== $cached_node) {
            if ($temporarily_link = $cached_node->get_temporarily_link($extension)) {
                return $temporarily_link;
            }
        }

        // 2: Get Temporarily link from entry itself
        $direct_download_link = $cached_node->get_entry()->get_direct_download_link();
        if (!empty($direct_download_link) && 'default' === $extension) {
            $cached_node->add_temporarily_link($direct_download_link, $extension);
            Cache::instance()->set_updated();

            return $cached_node->get_temporarily_link($extension);
        }

        // 3: Get Temporarily link via API
        try {
            // Get a Download link via the Box API
            $url = API::create_temporarily_download_url($cached_node->get_id(), $extension);

            if (!empty($url)) {
                $cached_node->add_temporarily_link($url, $extension);
            } else {
                error_log('[WP Cloud Plugin message]: '.sprintf('CLIENT Error on line %s: %s', __LINE__));

                return false;
            }
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('CLIENT Error on line %s: %s', __LINE__, $ex->getMessage()));

            return false;
        }

        Cache::instance()->set_updated();

        return $cached_node->get_temporarily_link($extension);
    }

    public function get_shared_link($cached_node, $link_settings = ['access' => 'open'])
    {
        if ($cached_node instanceof Entry) {
            $cached_node = $this->get_entry($cached_node->get_id());
        }

        // Custom link settings
        if (empty($link_settings)) {
            // Add Password
            $password = Processor::instance()->get_shortcode_option('share_password');
            if (!empty($password)) {
                $link_settings['password'] = $password;
                $link_settings['access'] = 'open';
            }

            // Add Expire date
            if (in_array(App::get_current_account()->get_type(), ['personal pro', 'business', 'enterprise'])) {
                $expire_after = Processor::instance()->get_shortcode_option('share_expire_after');
                if (!empty($expire_after)) {
                    $expire_date = current_datetime()->modify('+'.$expire_after);
                    $link_settings['unshared_at'] = $expire_date->format(DATE_RFC3339);
                }
            }

            // Read-Only?
            $share_allow_download = Processor::instance()->get_shortcode_option('share_allow_download');
            if ('0' === $share_allow_download) {
                if (!isset($link_settings['permissions'])) {
                    $link_settings['permissions'] = [];
                }
                $link_settings['permissions']['can_download'] = false;
                $link_settings['access'] = (isset($link_settings['access']) && 'company' === $link_settings['access']) ? 'company' : 'open';
            }
        }

        if (empty($link_settings['access']) && 'personal' !== App::get_current_account()->get_type()) {
            $link_settings['access'] = Processor::instance()->get_setting('link_scope');
        }

        $default_settings = [
            'access' => 'open',
            'password' => null,
            'permissions' => [
                'can_download' => true,
            ],
            'unshared_at' => null,
        ];

        $link_settings = array_merge($default_settings, $link_settings);

        if (false !== $cached_node) {
            if ($shared_link = $cached_node->get_shared_link($link_settings)) {
                return $shared_link;
            }
        }

        return $this->create_shared_link($cached_node, $link_settings);
    }

    public function get_shared_link_for_output($entry_id = false)
    {
        $cached_node = $this->get_entry($entry_id);

        if (false === $cached_node) {
            exit(-1);
        }

        $entry = $cached_node->get_entry();

        $shared_link = $this->get_shared_link($cached_node, []);

        return [
            'name' => $entry->get_name(),
            'extension' => $entry->get_extension(),
            'link' => API::shorten_url($shared_link, null, ['name' => $entry->get_name()]),
            'embeddedlink' => $this->get_embedded_link($cached_node, []),
            'size' => Helpers::bytes_to_size_1024($entry->get_size()),
            'error' => false,
        ];
    }

    public function create_embed_expire_link($cached_node)
    {
        if ($cached_node instanceof Entry) {
            $cached_node = $this->get_entry($cached_node->get_id());
        }

        try {
            $link = API::create_preview_url($cached_node->get_id());
            $link['unshared_at'] = current_datetime()->modify('+60 seconds')->format(DATE_RFC3339);
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('CLIENT Error on line %s: %s', __LINE__, $ex->getMessage()));

            return false;
        }

        do_action('letsbox_log_event', 'letsbox_updated_metadata', $cached_node, ['metadata_field' => 'Sharing Permissions']);

        return $cached_node->add_shared_link($link, ['type' => 'embed_expire']);
    }

    public function create_shared_link($cached_node, $link_settings = ['access' => 'open'])
    {
        if ($cached_node instanceof Entry) {
            $cached_node = $this->get_entry($cached_node->get_id());
        }

        $params = [
            'shared_link' => $link_settings,
        ];

        try {
            $link_data = API::create_shared_url($cached_node->get_id(), $cached_node->get_entry()->is_dir(), $params);
        } catch (\Exception $ex) {
            return false;
        }

        $url = $cached_node->add_shared_link($link_data, $link_settings);

        Cache::instance()->set_updated();

        return $url;
    }

    public function get_embedded_link($cached_node, $link_settings)
    {
        if ($cached_node instanceof Entry) {
            $cached_node = $this->get_entry($cached_node->get_id());
        }

        if (false === $cached_node->get_entry()->get_can_preview_by_cloud()) {
            return false;
        }

        $embedded_link = $this->get_shared_link($cached_node, $link_settings);

        return str_replace('.box.com/s', '.box.com/embed_widget/s', $embedded_link).'?view_file_only=yes&show_parent_path=no';
    }

    public function get_temporarily_embedded_link($cached_node)
    {
        if ($cached_node instanceof Entry) {
            $cached_node = $this->get_entry($cached_node->get_id());
        }

        if (false === $cached_node->get_entry()->get_can_preview_by_cloud()) {
            return false;
        }

        if (false !== $cached_node) {
            if ($temp_embed_link = $cached_node->get_shared_link(['type' => 'embed_expire'])) {
                return $temp_embed_link;
            }
        }

        return $this->create_embed_expire_link($cached_node);
    }

    public function get_thumbnail(Entry $entry, $aslink = false, $width = null, $height = null, $crop = true, $raw = true)
    {
        if (false === $entry->has_own_thumbnail()) {
            $url = $entry->get_icon_large();
        } else {
            $thumbnail = new \TheLion\LetsBox\Thumbnail($entry, $width, $height, $crop, $raw);
            $url = $thumbnail->get_url();
        }

        if ($aslink) {
            return $url;
        }
        header('Location: '.$url);

        exit;
    }

    public function build_thumbnail()
    {
        $src = $_REQUEST['src'];
        preg_match_all('/(.+)_(\d*)_(\d*)_c(\d)_s(\d)_q(\d+)\.([a-z]+)/', $src, $attr, PREG_SET_ORDER);

        if (1 !== count($attr) || 8 !== count($attr[0])) {
            exit;
        }

        $entry_id = $attr[0][1];
        $width = empty($attr[0][2]) ? 0 : $attr[0][2];
        $height = empty($attr[0][3]) ? 0 : $attr[0][3];
        $crop = (1 == $attr[0][4]) ? true : false;
        $raw = (1 == $attr[0][5]) ? true : false;
        $quality = $attr[0][6];
        $format = $attr[0][7];

        $cached_node = $this->get_entry($entry_id, false);

        if (false === $cached_node) {
            exit(-1);
        }

        if (false === $cached_node->get_entry()->has_own_thumbnail()) {
            $url = $cached_node->get_entry()->get_icon_large();
            header('Location: '.$url);

            exit;
        }

        $thumbnail = new Thumbnail($cached_node->get_entry(), $width, $height, $crop, $raw, $quality, $format);

        $success = true;
        if (false === $thumbnail->does_thumbnail_exist()) {
            $success = $thumbnail->build_thumbnail();
        }

        if ($success) {
            $url = $thumbnail->get_url();
        } else {
            $cached_node->get_entry()->set_has_own_thumbnail(false);
            Cache::instance()->set_updated();
            $url = $cached_node->get_entry()->get_icon_large();
        }

        header('Location: '.$url);

        exit;
    }

    public function get_folder_recursive_filtered(CacheNode $cached_node, $nodes = [])
    {
        if (false === Processor::instance()->_is_entry_authorized($cached_node)) {
            return $nodes;
        }

        if ($cached_node->get_entry()->is_file()) {
            $nodes[$cached_node->get_id()] = $cached_node;

            return $nodes;
        }

        $result = $this->get_folder($cached_node->get_id());
        if (empty($result)) {
            return $nodes;
        }

        $cached_folder = $result['folder'];

        if (false === $cached_folder->has_children()) {
            return $nodes;
        }

        foreach ($cached_folder->get_children() as $cached_child_entry) {
            $nodes = $this->get_folder_recursive_filtered($cached_child_entry, $nodes);
        }

        return $nodes;
    }

    /**
     * Get the changes on the cloud account since a certain moment in time.
     *
     * @param string $change_token
     * @param array  $params
     *
     * @return array Returns an array ['new_change_token' => '', 'changes' => []]
     */
    public function get_changes($change_token = false, $params = [])
    {
        do_action('letsbox_api_before_get_changes', $change_token, $params);

        $list_of_update_entries = [];

        if (empty($change_token)) {
            try {
                $changes = App::instance()->get_sdk_client()->getEvents('now', 'changes');

                return ['new_change_token' => $changes['next_stream_position'], 'changes' => []];
            } catch (\Exception $ex) {
                error_log(sprintf('[WP Cloud Plugin message]: CLIENT Error on line %s: %s', __LINE__, $ex->getMessage()));

                return ['new_change_token' => false, 'changes' => []];
            }
        }

        try {
            $changes = App::instance()->get_sdk_client()->getEvents($change_token, 'changes');
        } catch (\Exception $ex) {
            Cache::instance()->reset_cache();
            error_log(sprintf('[WP Cloud Plugin message]: CLIENT Error on line %s: %s', __LINE__, $ex->getMessage()));

            return ['new_change_token' => false, 'changes' => []];
        }

        foreach ($changes['events'] as $change) {
            $data = $change->getSource();

            if (empty($data) || 0 == $data->getId()) {
                continue;
            }

            switch ($change->getEventType()) {
                case 'ITEM_CREATE':
                case 'ITEM_UPLOAD':
                case 'ITEM_UPLOAD':
                case 'ITEM_MOVE':
                case 'ITEM_COPY':
                case 'ITEM_UNDELETE_VIA_TRASH':
                case 'ITEM_RENAME':
                case 'TAG_ITEM_CREATE':
                    $list_of_update_entries[$data->getId()] = new Entry($data);

                    break;

                case 'ITEM_TRASH':
                    $list_of_update_entries[$data->getId()] = 'deleted';

                    break;

                default:
                    break;
            }
        }

        do_action('letsbox_api_after_get_changes', $list_of_update_entries);

        return ['new_change_token' => $changes['next_stream_position'], 'changes' => $list_of_update_entries];
    }

    /**
     * @deprecated
     *
     * @return \TheLion\LetsBox\App
     */
    public function get_app()
    {
        Helpers::is_deprecated('function', 'get_app()', '\TheLion\LetsBox\App::instance()');

        return App::instance();
    }

    /**
     * @deprecated
     *
     * @return \Box\Model\Client\Client
     */
    public function get_library()
    {
        Helpers::is_deprecated('function', 'get_library()', '\TheLion\LetsBox\App::instance()->get_sdk_client()');

        return App::instance()->get_sdk_client();
    }
}
