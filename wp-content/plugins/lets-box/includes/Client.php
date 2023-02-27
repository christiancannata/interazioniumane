<?php

namespace TheLion\LetsBox;

class Client
{
    /**
     * @var \TheLion\LetsBox\App
     */
    private $_app;

    /**
     * @var \TheLion\LetsBox\Processor
     */
    private $_processor;

    public function __construct(App $_app, Processor $_processor = null)
    {
        $this->_app = $_app;
        $this->_processor = $_processor;
    }

    /*
     * Get DriveInfo
     *
     * @return mixed|WP_Error
     */

    public function get_account_info()
    {
        return $this->get_app()->get_client()->getUserInfo();
    }

    public function get_entries_in_subfolders(CacheNode $cachedfolder)
    {
        return $this->_get_folder_recursive($cachedfolder);
    }

    // Get entry

    /**
     * @param type $entryid
     * @param type $checkauthorized
     *
     * @return bool|\TheLion\LetsBox\CacheNode
     */
    public function get_entry($entryid = false, $checkauthorized = true)
    {
        if (false === $entryid) {
            $entryid = $this->get_processor()->get_requested_entry();
        }

        // Load the root folder when needed
        $this->get_root_folder();

        // Get entry from cache
        $cachedentry = $this->get_cache()->is_cached($entryid);

        // Get metadata if entry isn't cached
        if (!$cachedentry) {
            $is_in_cache = $this->get_cache()->get_node_by_id($entryid);
            $type = (!empty($is_in_cache) && $is_in_cache->get_entry()->is_dir()) ? 'folder' : 'file';

            /* BOX has different calls for files and folder...
             * And we don't know on forehand if it is a file or folder
             *  */
            if ('file' === $type) {
                try {
                    $api_entry = $this->get_app()->get_client()->getFileFromBox($entryid);
                    $entry = new Entry($api_entry);
                    $cachedentry = $this->get_cache()->add_to_cache($entry);
                } catch (\Exception $ex) {
                    // Now try to get it as folder
                }
            }

            if (!$cachedentry) {
                try {
                    $api_entry = $this->get_app()->get_client()->getFolderFromBox($entryid);
                    $entry = new Entry($api_entry);
                    $cachedentry = $this->get_cache()->add_to_cache($entry);
                } catch (\Exception $ex) {
                    error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));

                    return false;
                }
            }
        }

        if (true === $checkauthorized) {
            if ('root' !== $entryid && !$this->get_processor()->_is_entry_authorized($cachedentry)) {
                return false;
            }
        }

        return $cachedentry;
    }

    public function get_root_folder()
    {
        $root_node = $this->get_cache()->get_root_node();

        if (false !== $root_node) {
            return $root_node;
        }

        // First get the root of the cloud
        try {
            $root = $this->get_folder(0, false);
            $root['folder']->set_expired(null);
            // Also make sure that we got the latest changes position
            $this->get_cache()->pull_for_changes(true);
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s not able to retreive root folder on your Box: %s', __LINE__, $ex->getMessage()));

            return false;
        }

        return $this->get_cache()->get_root_node();
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
        // Load the root folder when needed
        if (0 !== $folderid) {
            $rootfolder = $this->get_root_folder();
        }

        if (false === $folderid) {
            $folderid = $this->get_processor()->get_requested_entry();
        }

        // Load cached folder if present
        $cachedfolder = $this->get_cache()->is_cached($folderid, 'id', false);

        // If folder isn't present in cache, load it
        if (!$cachedfolder) {
            try {
                $folder_api = $this->get_app()->get_client()->getFolderFromBox($folderid);
            } catch (\Exception $ex) {
                error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));

                return false;
            }

            $folder_entry = new Entry($folder_api);
            $cachedfolder = $this->get_cache()->add_to_cache($folder_entry);

            try {
                $results_children = $this->get_app()->get_client()->getBoxFolderItems($folder_api, 100);
            } catch (\Exception $ex) {
                error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));

                return false;
            }

            $files_in_folder = $folder_api->getItems();

            // Convert the items to Framework Entry

            $folder_items = [];

            // Add all entries in folder to cache

            foreach ($files_in_folder as $entry) {
                $item = new Entry($entry);

                if (true === $item->get_trashed()) {
                    continue;
                }

                $newitem = $this->get_cache()->add_to_cache($item);
            }

            $cachedfolder->set_loaded_children(true);
            $this->get_cache()->update_cache();
        }

        $folder = $cachedfolder;
        $files_in_folder = $cachedfolder->get_children();

        // Check if folder is in the shortcode-set rootfolder
        if (true === $checkauthorized) {
            if (!$this->get_processor()->_is_entry_authorized($cachedfolder)) {
                return false;
            }
        }

        return ['folder' => $folder, 'contents' => $files_in_folder];
    }

    /**
     * Get (and create) sub folder by path.
     *
     * @param string $parent_folder_id
     * @param string $subfolder_path
     * @param bool   $create_if_not_exists
     *
     * @return bool|\TheLion\LetsBox\CacheNode
     */
    public function get_sub_folder_by_path($parent_folder_id, $subfolder_path, $create_if_not_exists = false)
    {
        $cached_parent_folder = $this->get_folder($parent_folder_id, false);

        if (empty($cached_parent_folder)) {
            return false;
        }

        if (empty($subfolder_path)) {
            return $cached_parent_folder['folder'];
        }

        $subfolders = array_filter(explode('/', $subfolder_path));
        $current_folder = array_shift($subfolders);

        //Try to load the subfolder at once
        $cached_sub_folder = $this->get_cache()->get_node_by_name($current_folder, $parent_folder_id);

        /* If folder isn't in cache yet,
         * Update the parent folder to make sure the latest version is loaded */
        if (false === $cached_sub_folder) {
            $this->get_cache()->pull_for_changes(true, -1);
            $cached_sub_folder = $this->get_cache()->get_node_by_name($current_folder, $parent_folder_id);
        }

        if (false === $cached_sub_folder && false === $create_if_not_exists) {
            return false;
        }

        // If the subfolder can't be found, create the sub folder
        if (!$cached_sub_folder) {
            try {
                $api_entry = $this->get_app()->get_client()->createNewBoxFolder($current_folder, $parent_folder_id);
                $newentry = new Entry($api_entry);
                $cached_sub_folder = $this->get_cache()->add_to_cache($newentry);

                do_action('letsbox_log_event', 'letsbox_created_entry', $cached_sub_folder);
            } catch (\Exception $ex) {
                error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));

                return false;
            }
        }

        return $this->get_sub_folder_by_path($cached_sub_folder->get_id(), implode('/', $subfolders), $create_if_not_exists);
    }

    public function update_expired_entry(CacheNode $cachedentry)
    {
        $entry = $cachedentry->get_entry();

        try {
            if ($entry->is_dir()) {
                $api_entry = $this->get_app()->get_client()->getFolderFromBox($entry->get_id());
            } else {
                $api_entry = $this->get_app()->get_client()->getFileFromBox($entry->get_id());
            }

            $entry = new Entry($api_entry);
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));

            return false;
        }

        return $this->get_cache()->add_to_cache($entry);
    }

    public function update_expired_folder(CacheNode $cachedentry)
    {
        $entry = $cachedentry->get_entry();

        try {
            $folder_api = $this->get_app()->get_client()->getFolderFromBox($entry->get_id());
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));

            return false;
        }

        $folder_entry = new Entry($folder_api);
        $cachedfolder = $this->get_cache()->add_to_cache($folder_entry);

        try {
            $folder_api = $this->get_app()->get_client()->getFolderFromBox($entry->get_id());
            $results_children = $this->get_app()->get_client()->getBoxFolderItems($folder_api, 100);
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));

            return false;
        }

        $files_in_folder = $folder_api->getItems();

        // Convert the items to Framework Entry

        $folder_items = [];

        // Add all entries in folder to cache

        foreach ($files_in_folder as $entry) {
            $item = new Entry($entry);

            if (true === $item->get_trashed()) {
                continue;
            }

            $newitem = $this->get_cache()->add_to_cache($item);
        }

        $cachedfolder->set_loaded_children(true);
        $this->get_cache()->update_cache();

        return $cachedfolder;
    }

    // Search entry by name

    public function search($query)
    {
        $searchedfolder = $this->get_processor()->get_requested_entry();
        $cached_searchedfolder = $this->get_folder($searchedfolder);

        // Check if requested folder is allowed
        if (empty($cached_searchedfolder)) {
            return [];
        }

        $entries_in_searchedfolder = [];

        // extensions
        $search_extensions = null;
        $allowed_extensions = $this->get_processor()->get_shortcode_option('include_ext');
        if ('*' != $allowed_extensions[0]) {
            $search_extensions = join(',', $allowed_extensions);
        }

        // Set search field
        $search_contents = ('1' === $this->get_processor()->get_shortcode_option('searchcontents'));
        $fields = ($search_contents) ? 'name,description,file_content,tags' : 'name';

        do_action('letsbox_log_event', 'letsbox_searched', $cached_searchedfolder['folder'], ['query' => $query]);

        try {
            $found_entries = $this->get_app()->get_client()->search(stripslashes($query), $searchedfolder, $search_extensions, null, $fields, 1000, 0);
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));

            return [];
        }

        if (0 === count($found_entries)) {
            return $entries_in_searchedfolder;
        }

        foreach ($found_entries as $file) {
            if (false === $search_contents) {
                if (false === stripos($file->getName(), $query)) {
                    // Only find files query in name */
                    continue;
                }
            }

            $entries_found[] = new Entry($file);
        }

        if (0 === count($found_entries)) {
            return $entries_in_searchedfolder;
        }

        foreach ($entries_found as $entry) {
            // Check if entries are in cache
            $cachedentry = $this->get_cache()->is_cached($entry->get_id());

            // If not found, add to cache
            if (false === $cachedentry) {
                $cachedentry = $this->get_cache()->add_to_cache($entry);
            }

            if (false === $this->get_processor()->_is_entry_authorized($cachedentry)) {
                continue;
            }

            $entries_in_searchedfolder[] = $cachedentry;
        }

        // Update the cache already here so that the Search Output is cached
        $this->get_cache()->update_cache();

        return $entries_in_searchedfolder;
    }

    // Delete multiple entries from Box

    public function delete_entries($entries_to_delete = [])
    {
        $deleted_entries = [];

        foreach ($entries_to_delete as $target_entry_path) {
            $target_cached_entry = $this->get_entry($target_entry_path);

            if (false === $target_cached_entry) {
                continue;
            }

            $target_entry = $target_cached_entry->get_entry();

            if ($target_entry->is_file() && false === $this->get_processor()->get_user()->can_delete_files()) {
                error_log('[WP Cloud Plugin message]: '.sprintf('Failed to delete %s as user is not allowed to remove files.', $target_entry->get_path()));

                continue;
            }

            if ($target_entry->is_dir() && false === $this->get_processor()->get_user()->can_delete_folders()) {
                error_log('[WP Cloud Plugin message]: '.sprintf('Failed to delete %s as user is not allowed to remove folders.', $target_entry->get_path()));

                continue;
            }

            if ('1' === $this->get_processor()->get_shortcode_option('demo')) {
                continue;
            }

            if ($target_entry->is_dir()) {
                $box_entry = new \Box\Model\Folder\Folder();
            } else {
                $box_entry = new \Box\Model\File\File();
            }
            $box_entry->setId($target_entry->get_id());

            try {
                $deleted_entry = $this->get_app()->get_client()->deleteEntry($box_entry);
                $deleted_entries[$target_entry->get_id()] = $target_cached_entry;

                do_action('letsbox_log_event', 'letsbox_deleted_entry', $target_cached_entry, []);
                $this->get_cache()->remove_from_cache($target_entry->get_id(), 'deleted');
            } catch (\Exception $ex) {
                error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));
            }
        }

        if ('1' === $this->get_processor()->get_shortcode_option('notificationdeletion')) {
            // TO DO NOTIFICATION
            $this->get_processor()->send_notification_email('deletion', $deleted_entries);
        }

        // Remove items from cache
        $this->get_cache()->pull_for_changes(true);

        // Clear Cached Requests
        CacheRequest::clear_request_cache();

        return $deleted_entries;
    }

    // Rename entry from Box

    public function rename_entry($new_filename = null)
    {
        if ('1' === $this->get_processor()->get_shortcode_option('demo')) {
            return new \WP_Error('broke', esc_html__('Failed to rename entry', 'wpcloudplugins'));
        }

        if (null === $new_filename && '1' === $this->get_processor()->get_shortcode_option('debug')) {
            return new \WP_Error('broke', esc_html__('No new name set', 'wpcloudplugins'));
        }

        // Get entry meta data
        $cachedentry = $this->get_cache()->is_cached($this->get_processor()->get_requested_entry());

        if (false === $cachedentry) {
            $cachedentry = $this->get_entry($this->get_processor()->get_requested_entry());
            if (false === $cachedentry) {
                if ('1' === $this->get_processor()->get_shortcode_option('debug')) {
                    return new \WP_Error('broke', esc_html__('Invalid entry', 'wpcloudplugins'));
                }

                return new \WP_Error('broke', esc_html__('Failed to rename entry', 'wpcloudplugins'));

                return new \WP_Error('broke', esc_html__('Failed to rename entry', 'wpcloudplugins'));
            }
        }

        // Check if user is allowed to delete from this dir
        if (!$cachedentry->is_in_folder($this->get_processor()->get_last_folder())) {
            return new \WP_Error('broke', esc_html__('You are not authorized to rename files in this directory', 'wpcloudplugins'));
        }

        $entry = $cachedentry->get_entry();

        // Check user permission
        if (!$entry->get_permission('canrename')) {
            //return new \WP_Error('broke', esc_html__('You are not authorized to rename this file or folder', 'wpcloudplugins'));
        }

        // Check if entry is allowed
        if (!$this->get_processor()->_is_entry_authorized($cachedentry)) {
            return new \WP_Error('broke', esc_html__('You are not authorized to rename this file or folder', 'wpcloudplugins'));
        }

        if ($entry->is_dir() && (false === $this->get_processor()->get_user()->can_rename_folders())) {
            return new \WP_Error('broke', esc_html__('You are not authorized to rename folder', 'wpcloudplugins'));
        }

        if ($entry->is_file() && (false === $this->get_processor()->get_user()->can_rename_files())) {
            return new \WP_Error('broke', esc_html__('You are not authorized to rename this file', 'wpcloudplugins'));
        }

        $extension = $entry->get_extension();
        $name = (!empty($extension)) ? $new_filename.'.'.$extension : $new_filename;
        $updaterequest = ['name' => $name];

        try {
            $renamed_entry = $this->update_entry($entry->get_id(), $updaterequest);

            if (false !== $renamed_entry && null !== $renamed_entry) {
                $this->get_cache()->update_cache();
            }

            do_action('letsbox_log_event', 'letsbox_renamed_entry', $renamed_entry, ['old_name' => $entry->get_name()]);
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));

            if ('1' === $this->get_processor()->get_shortcode_option('debug')) {
                return new \WP_Error('broke', $ex->getMessage());
            }

            return new \WP_Error('broke', esc_html__('Failed to rename entry', 'wpcloudplugins'));
        }

        return $renamed_entry;
    }

    // Copy entry

    public function copy_entry($cached_entry = null, $cached_parent = null, $new_name = null)
    {
        if (null === $cached_entry) {
            $cached_entry = $this->get_entry($this->get_processor()->get_requested_entry());
        }

        if (null === $cached_parent) {
            $cached_parent = $this->get_entry($this->get_processor()->get_last_folder());
        }

        if (false === $cached_entry) {
            $message = '[WP Cloud Plugin message]: '.'Failed to copy the file.';

            error_log($message);

            return new \WP_Error('broke', $message);
        }

        $entry = $cached_entry->get_entry();

        if (($entry->is_dir()) && (false === $this->get_processor()->get_user()->can_copy_folders())) {
            $message = '[WP Cloud Plugin message]: '.sprintf('Failed to move %s as user is not allowed to move folders.', $cached_parent->get_path($this->get_processor()->get_root_folder()));

            error_log($message);

            return new \WP_Error('broke', $message);
        }

        if (($entry->is_file()) && (false === $this->get_processor()->get_user()->can_copy_files())) {
            $message = '[WP Cloud Plugin message]: '.sprintf('Failed to copy %s as user is not allowed to copy files.', $cached_parent->get_path($this->get_processor()->get_root_folder()));

            error_log($message);

            return new \WP_Error('broke', $message);
        }

        if ('1' === $this->get_processor()->get_shortcode_option('demo')) {
            $message = '[WP Cloud Plugin message]: '.sprintf('Failed to copy the file %s.', $cached_entry->get_path($this->get_processor()->get_root_folder()));

            error_log($message);

            return new \WP_Error('broke', $message);
        }

        // Check if user is allowed to copy from this dir
        if (!$cached_entry->is_in_folder($cached_parent->get_id())) {
            $message = '[WP Cloud Plugin message]: '.sprintf('Failed to copy %s as user is not allowed to copy items in this directory.', $cached_parent->get_path($this->get_processor()->get_root_folder()));

            error_log($message);

            return new \WP_Error('broke', $message);
        }

        $extension = $entry->get_extension();
        $name = (!empty($extension)) ? $new_name.'.'.$extension : $new_name;

        try {
            if ($entry->is_dir()) {
                $api_entry = $this->get_app()->get_client()->copyBoxFolder($cached_entry->get_id(), $cached_parent->get_id(), $name, false);
            } else {
                $api_entry = $this->get_app()->get_client()->copyBoxFile($cached_entry->get_id(), $cached_parent->get_id(), $name);
            }

            $new_entry = new Entry($api_entry);
            $copied_entry = $this->get_cache()->add_to_cache($new_entry);

            if (false !== $copied_entry && null !== $copied_entry) {
                $this->get_cache()->update_cache();
            }

            do_action('letsbox_log_event', 'letsbox_copied_entry', $copied_entry, ['original' => $entry->get_name()]);
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));

            if ('1' === $this->get_processor()->get_shortcode_option('debug')) {
                return new \WP_Error('broke', $ex->getMessage());
            }

            return new \WP_Error('broke', esc_html__('Failed to copy entry', 'wpcloudplugins'));
        }

        // Clear Cached Requests
        CacheRequest::clear_local_cache_for_shortcode($this->get_processor()->get_listtoken());

        return $copied_entry;
    }

    // Edit descriptions entry from Box
    public function move_entries($entries, $target, $copy = false)
    {
        $entries_to_move = [];

        $cached_target = $this->get_entry($target);
        $cached_current_folder = $this->get_entry($this->get_processor()->get_last_folder());

        if (false === $cached_target) {
            error_log('[WP Cloud Plugin message]: '.'Failed to move as target folder is not found.');

            return $entries_to_move;
        }

        foreach ($entries as $entry_id) {
            $cached_entry = $this->get_entry($entry_id);

            if (false === $cached_entry) {
                continue;
            }

            $entry = $cached_entry->get_entry();

            if (($entry->is_dir()) && (false === $this->get_processor()->get_user()->can_move_folders())) {
                error_log('[WP Cloud Plugin message]: '.sprintf('Failed to move %s as user is not allowed to move folders.', $cached_target->get_path($this->get_processor()->get_root_folder())));
                $entries_to_move[$cached_entry->get_id()] = false;

                continue;
            }

            if (($entry->is_file()) && (false === $this->get_processor()->get_user()->can_move_files())) {
                error_log('[WP Cloud Plugin message]: '.sprintf('Failed to move %s as user is not allowed to remove files.', $cached_target->get_path($this->get_processor()->get_root_folder())));
                $entries_to_move[$cached_entry->get_id()] = false;

                continue;
            }

            if ('1' === $this->get_processor()->get_shortcode_option('demo')) {
                $entries_to_move[$cached_entry->get_id()] = false;

                continue;
            }

            // Check if user is allowed to delete from this dir
            if (!$cached_entry->is_in_folder($cached_current_folder->get_id())) {
                error_log('[WP Cloud Plugin message]: '.sprintf('Failed to move %s as user is not allowed to move items in this directory.', $cached_target->get_path($this->get_processor()->get_root_folder())));
                $entries_to_move[$cached_entry->get_id()] = false;

                continue;
            }

            // Check user permission
            if (!$entry->get_permission('canmove')) {
                error_log('[WP Cloud Plugin message]: '.sprintf('Failed to move %s as the sharing permissions on it prevent this.', $cached_target->get_path($this->get_processor()->get_root_folder())));
                $entries_to_move[$cached_entry->get_id()] = false;

                continue;
            }

            try {
                if ($copy) {
                    if ($entry->is_dir()) {
                        $api_entry = $this->get_app()->get_client()->copyBoxFolder($entry_id, $cached_target->get_id());
                    } else {
                        $api_entry = $this->get_app()->get_client()->copyBoxFile($entry_id, $cached_target->get_id());
                    }

                    $entry = new Entry($api_entry);
                    $updated_entry = $this->get_cache()->add_to_cache($entry);
                } else {
                    $updaterequest = ['parent' => ['id' => $cached_target->get_id()]];
                    $updated_entry = $this->update_entry($entry_id, $updaterequest);

                    $entries_to_move[$cached_entry->get_id()] = $updated_entry;
                    do_action('letsbox_log_event', 'letsbox_moved_entry', $updated_entry);
                }
            } catch (\Exception $ex) {
                error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));
                $entries_to_move[$cached_entry->get_id()] = false;

                continue;
            }
        }

        // Clear Cached Requests
        CacheRequest::clear_local_cache_for_shortcode($this->get_processor()->get_listtoken());

        return $entries_to_move;
    }

    // Edit description of entry

    public function update_description($new_description = null)
    {
        if (null === $new_description && '1' === $this->get_processor()->get_shortcode_option('debug')) {
            return new \WP_Error('broke', esc_html__('No new description set', 'wpcloudplugins'));
        }

        // Get entry meta data
        $cachedentry = $this->get_cache()->is_cached($this->get_processor()->get_requested_entry());

        if (false === $cachedentry) {
            $cachedentry = $this->get_entry($this->get_processor()->get_requested_entry());
            if (false === $cachedentry) {
                if ('1' === $this->get_processor()->get_shortcode_option('debug')) {
                    return new \WP_Error('broke', esc_html__('Invalid entry', 'wpcloudplugins'));
                }

                return new \WP_Error('broke', esc_html__('Failed to edit entry', 'wpcloudplugins'));

                return new \WP_Error('broke', esc_html__('Failed to edit entry', 'wpcloudplugins'));
            }
        }

        // Check if user is allowed to delete from this dir
        if (!$cachedentry->is_in_folder($this->get_processor()->get_last_folder())) {
            return new \WP_Error('broke', esc_html__('You are not authorized to edit files in this directory', 'wpcloudplugins'));
        }

        $entry = $cachedentry->get_entry();

        // Check if entry is allowed
        if (!$this->get_processor()->_is_entry_authorized($cachedentry)) {
            return new \WP_Error('broke', esc_html__('You are not authorized to edit this file or folder', 'wpcloudplugins'));
        }

        // Set new description, and update the entry
        $updaterequest = ['description' => $new_description];

        try {
            $edited_entry = $this->update_entry($entry->get_id(), $updaterequest);

            do_action('letsbox_log_event', 'letsbox_updated_metadata', $edited_entry, ['metadata_field' => 'Description']);
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));

            if ('1' === $this->get_processor()->get_shortcode_option('debug')) {
                return new \WP_Error('broke', $ex->getMessage());
            }

            return new \WP_Error('broke', esc_html__('Failed to edit entry', 'wpcloudplugins'));
        }

        return $edited_entry->get_entry()->get_description();
    }

    // Update entry from Box

    public function update_entry($entry_id, $updaterequest = [])
    {
        $cached_entry = $this->get_entry($entry_id);

        $updaterequest['fields'] = [
            'type',
            'id',
            'etag',
            'name',
            'description',
            'size',
            'path_collection',
            'modified_at',
            'created_by',
            'shared_link',
            'parent',
            'item_status',
            'permissions',
            'tags',
            'extension',
        ];

        if ($cached_entry->get_entry()->is_dir()) {
            $box_entry = new \Box\Model\Folder\Folder();
        } else {
            $box_entry = new \Box\Model\File\File();
        }
        $box_entry->setId($cached_entry->get_id());

        try {
            $api_entry = $this->get_app()->get_client()->updateEntry($box_entry, $updaterequest);

            // Remove item from cache if it is moved
            if (isset($updaterequest['parent'])) {
                $this->get_cache()->remove_from_cache($entry_id, 'deleted');
            }

            $entry = new Entry($api_entry);
            $cachedentry = $this->get_cache()->add_to_cache($entry);
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));

            throw $ex;
        }

        $this->get_cache()->update_cache();

        return $cachedentry;
    }

    // Add directory to Box

    public function add_folder($new_folder_name = null)
    {
        if ('1' === $this->get_processor()->get_shortcode_option('demo')) {
            return new \WP_Error('broke', esc_html__('Failed to add folder', 'wpcloudplugins'));
        }

        if (null === $new_folder_name && '1' === $this->get_processor()->get_shortcode_option('debug')) {
            return new \WP_Error('broke', esc_html__('No new foldername set', 'wpcloudplugins'));
        }

        // Get entry meta data of current folder
        $cachedentry = $this->get_cache()->is_cached($this->get_processor()->get_last_folder());

        if (false === $cachedentry) {
            $cachedentry = $this->get_entry($this->get_processor()->get_last_folder());
            if (false === $cachedentry) {
                if ('1' === $this->get_processor()->get_shortcode_option('debug')) {
                    return new \WP_Error('broke', esc_html__('Invalid entry', 'wpcloudplugins'));
                }

                return new \WP_Error('broke', esc_html__('Failed to add entry', 'wpcloudplugins'));

                return new \WP_Error('broke', esc_html__('Failed to add entry', 'wpcloudplugins'));
            }
        }

        if (!$this->get_processor()->_is_entry_authorized($cachedentry)) {
            return new \WP_Error('broke', esc_html__('You are not authorized to add folders in this directory', 'wpcloudplugins'));
        }

        $currentfolder = $cachedentry->get_entry();

        // Check user permission
        if (!$currentfolder->get_permission('canadd')) {
            return new \WP_Error('broke', esc_html__('You are not authorized to add a folder', 'wpcloudplugins'));
        }

        try {
            $api_entry = $this->get_app()->get_client()->createNewBoxFolder($new_folder_name, $currentfolder->get_id());
            // Add new file to our Cache
            $newentry = new Entry($api_entry);
            $cached_entry = $this->get_cache()->add_to_cache($newentry);

            do_action('letsbox_log_event', 'letsbox_created_entry', $cached_entry);
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));

            if ('1' === $this->get_processor()->get_shortcode_option('debug')) {
                return new \WP_Error('broke', $ex->getMessage());
            }

            return new \WP_Error('broke', esc_html__('Failed to add folder', 'wpcloudplugins'));
        }

        // Remove items from cache
        $this->get_cache()->pull_for_changes(true);

        return $cached_entry;
    }

    public function preview_entry()
    {
        // Get file meta data
        $cached_entry = $this->get_entry();

        if (false === $cached_entry) {
            exit('-1');
        }

        $entry = $cached_entry->get_entry();
        if (false === $entry->get_can_preview_by_cloud()) {
            exit('-1');
        }

        if (false === $this->get_processor()->get_user()->can_preview()) {
            exit('-1');
        }

        do_action('letsbox_log_event', 'letsbox_previewed_entry', $cached_entry);

        $temporarily_link = $this->get_temporarily_embedded_link($cached_entry);
        //$temporarily_link .= '?showAnnotations=true';

        // Preview for Image files
        if (in_array($entry->get_extension(), ['jpg', 'jpeg', 'gif', 'png','heic'])) {
            if ('original' === $this->get_processor()->get_setting('loadimages') & false === $this->get_processor()->get_user()->can_download()) {
                // Redirect to download url
            }
        }

        if ($this->get_processor()->get_user()->can_download() && $entry->get_permission('candownload') && in_array($this->get_processor()->get_shortcode_option('mode'), ['files', 'search'])) {
            $temporarily_link .= '?showDownload=true';
        }

        header('Location: '.$temporarily_link);

        exit();
    }

    public function edit_entry()
    {
        // Get file meta data
        $cached_entry = $this->get_entry();

        if (false === $cached_entry) {
            exit('-1');
        }

        $entry = $cached_entry->get_entry();
        if (false === $entry->get_can_edit_by_cloud()) {
            exit('-1');
        }

//        $edit_link = $this->get_shared_link($cached_entry, 'edit');
//
//        if (empty($edit_link)) {
//            error_log('[WP Cloud Plugin message]: ' . sprintf('Cannot create a editable ahared link %s', __LINE__));
//            die();
//        }
//
//        header('Location: ' . $edit_link);
        exit();
    }

    // Download file

    public function download_entry()
    {
        // Check if file is cached and still valid
        $cached = $this->get_cache()->is_cached($this->get_processor()->get_requested_entry());

        if (false === $cached) {
            $cachedentry = $this->get_entry($this->get_processor()->get_requested_entry());
        } else {
            $cachedentry = $cached;
        }

        if (false === $cachedentry) {
            exit();
        }

        $entry = $cachedentry->get_entry();

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
                if ('1' === $this->get_processor()->get_shortcode_option('notificationdownload')) {
                    $this->get_processor()->send_notification_email('download', [$cachedentry]);
                }

                do_action('letsbox_download', $cachedentry);
                header('HTTP/1.1 304 Not Modified');

                exit;
            }
        }

        // Check if entry is allowed
        if (!$this->get_processor()->_is_entry_authorized($cachedentry)) {
            exit();
        }

        // Send email if needed
        if ('1' === $this->get_processor()->get_shortcode_option('notificationdownload')) {
            $this->get_processor()->send_notification_email('download', [$cachedentry]);
        }

        // Get the complete file
        $extension = (isset($_REQUEST['extension'])) ? $_REQUEST['extension'] : 'default';
        $this->download_content($cachedentry, $extension);

        exit();
    }

    public function download_content(CacheNode $cachedentry, $extension = 'default')
    {
        // If there is a temporarily download url present for this file, just redirect the user
        $stream = (isset($_REQUEST['action']) && 'letsbox-stream' === $_REQUEST['action'] && !isset($_REQUEST['caption']));
        $stored_url = ($stream) ? get_transient('letsbox'.$cachedentry->get_id().'_'.$cachedentry->get_entry()->get_extension()) : get_transient('letsbox'.$cachedentry->get_id().'_'.$cachedentry->get_entry()->get_extension());
        if (false !== $stored_url && filter_var($stored_url, FILTER_VALIDATE_URL)) {
            do_action('letsbox_download', $cachedentry, $stored_url);
            header('Location: '.$stored_url);

            exit();
        }

        $temporarily_link = $this->get_temporarily_link($cachedentry, $extension);

        // Download Hook
        do_action('letsbox_download', $cachedentry, $temporarily_link);

        $event_type = ($stream) ? 'letsbox_streamed_entry' : 'letsbox_downloaded_entry';
        do_action('letsbox_log_event', $event_type, $cachedentry);

        header('Location: '.$temporarily_link);

        set_transient('letsbox'.(($stream) ? 'stream' : 'download').'_'.$cachedentry->get_id().'_'.$cachedentry->get_entry()->get_extension(), $temporarily_link, MINUTE_IN_SECONDS * 10);

        exit();
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

        exit();
    }

    public function stream_entry()
    {
        // Check if file is cached and still valid
        $cached = $this->get_cache()->is_cached($this->get_processor()->get_requested_entry());

        if (false === $cached) {
            $cachedentry = $this->get_entry($this->get_processor()->get_requested_entry());
        } else {
            $cachedentry = $cached;
        }

        if (false === $cachedentry) {
            exit();
        }

        $entry = $cachedentry->get_entry();

        $extension = $entry->get_extension();
        $allowedextensions = ['mp4', 'm4v', 'ogg', 'ogv', 'webmv', 'mp3', 'm4a', 'oga', 'wav', 'webm', 'vtt'];

        if (empty($extension) || !in_array($extension, $allowedextensions)) {
            exit();
        }

        // Download Captions directly
        if ('vtt' === $extension) {
            $temporarily_link = $this->get_temporarily_link($cachedentry, 'default');
            $this->download_via_proxy($entry, $temporarily_link);

            exit();
        }

        $this->download_entry();
    }

    public function has_temporarily_link($cached_entry, $extension = 'default')
    {
        if ($cached_entry instanceof Entry) {
            $cached_entry = $this->get_entry($cached_entry->get_id());
        }

        if (false !== $cached_entry) {
            if ($temporarily_link = $cached_entry->get_temporarily_link($extension)) {
                return true;
            }
        }

        return false;
    }

    public function get_temporarily_link($cached_entry, $extension = 'default')
    {
        if ($cached_entry instanceof Entry) {
            $cached_entry = $this->get_entry($cached_entry->get_id());
        }

        // 1: Get Temporarily link from cache
        if (false !== $cached_entry) {
            if ($temporarily_link = $cached_entry->get_temporarily_link($extension)) {
                return $temporarily_link;
            }
        }

        // 2: Get Temporarily link from entry itself
        $direct_download_link = $cached_entry->get_entry()->get_direct_download_link();
        if (!empty($direct_download_link) && 'default' === $extension) {
            $cached_entry->add_temporarily_link($direct_download_link, $extension);
            $this->get_cache()->set_updated();

            return $cached_entry->get_temporarily_link($extension);
        }

        // 3: Get Temporarily link via API
        try {
            // Get a Download link via the Box API
            if ('default' === $extension) {
                $url = $this->get_app()->get_client()->downloadFile($cached_entry->get_id(), false);
            }
            // NOT IMPLEMENTED

            if (!empty($url)) {
                $cached_entry->add_temporarily_link($url, $extension);
            } else {
                error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__));

                return false;
            }
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));

            return false;
        }

        $this->get_cache()->set_updated();

        return $cached_entry->get_temporarily_link($extension);
    }

    public function has_shared_link($cached_entry, $mode = 'view_and_download', $scope = false)
    {
        if (false === $scope) {
            $scope = ('personal' === $this->get_app()->get_account_type()) ? 'open' : $this->get_processor()->get_setting('link_scope');
        }

        if ($cached_entry instanceof Entry) {
            $cached_entry = $this->get_entry($cached_entry->get_id());
        }

        if (false !== $cached_entry) {
            if ($shared_link = $cached_entry->get_shared_link($mode, $scope)) {
                return true;
            }
        }

        return false;
    }

    public function get_shared_link($cached_entry, $type = 'view_and_download', $scope = false)
    {
        if (false === $scope) {
            $scope = ('personal' === $this->get_app()->get_account_type()) ? 'open' : $this->get_processor()->get_setting('link_scope');
        }

        if ($cached_entry instanceof Entry) {
            $cached_entry = $this->get_entry($cached_entry->get_id());
        }

        if (false !== $cached_entry) {
            if ($shared_link = $cached_entry->get_shared_link($type, $scope)) {
                return $shared_link;
            }
        }

        $shared_link = $this->create_shared_link($cached_entry, $type, $scope);

        if ('embed_expire' !== $type) {
            do_action('letsbox_log_event', 'letsbox_created_link_to_entry', $cached_entry, ['url' => $shared_link]);
        }

        return $shared_link;
    }

    public function get_shared_link_for_output($entry_id = false)
    {
        $cached_entry = $this->get_entry($entry_id);

        if (false === $cached_entry) {
            exit(-1);
        }

        $entry = $cached_entry->get_entry();

        $shared_link = $this->get_shared_link($cached_entry, 'view_and_download');

        return [
            'name' => $entry->get_name(),
            'extension' => $entry->get_extension(),
            'link' => $this->shorten_url($cached_entry, $shared_link),
            'embeddedlink' => $this->get_embedded_link($cached_entry),
            'size' => Helpers::bytes_to_size_1024($entry->get_size()),
            'error' => false,
        ];
    }

    public function create_shared_link($cached_entry, $type = 'view_and_download', $scope = false)
    {
        if (false === $scope) {
            $scope = ('personal' === $this->get_app()->get_account_type()) ? 'open' : $this->get_processor()->get_setting('link_scope');
        }

        if ($cached_entry instanceof Entry) {
            $cached_entry = $this->get_entry($cached_entry->get_id());
        }

        $expires = null;
        $url = null;

        switch ($type) {
            case 'embed_expire':
                try {
                    $link = $this->get_app()->get_client()->createShortLivedEmbed($cached_entry->get_id());
                    $url = $link['url'];
                    $expires = time() + (60); // Expires in 60 seconds
                } catch (\Exception $ex) {
                    error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));

                    return false;
                }

                break;

            case 'view_and_download':
                $params = [
                    'shared_link' => [
                        'access' => 'open', //scope
                        'permissions' => [
                            //'can_download'=> true
                        ],
                    ],
                ];

                try {
                    if ($cached_entry->get_entry()->is_dir()) {
                        $link = $this->get_app()->get_client()->createSharedLinkForFolder($cached_entry->get_id(), $params);
                    } else {
                        $link = $this->get_app()->get_client()->createSharedLinkForFile($cached_entry->get_id(), $params);
                    }
                    $url = $link['url'];

                    $expires = null;
                    if (!$link['unshared_at']) {
                        $dtime = \DateTime::createFromFormat(DATE_RFC3339, $link['unshared_at'], new \DateTimeZone('UTC'));

                        if ($dtime) {
                            $expires = $dtime->getTimestamp();
                        }
                    }
                } catch (\Exception $ex) {
                    error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));

                    return false;
                }

                break;
        }

        $cached_entry->add_shared_link($url, $type, $scope, $expires);

        if ('embed_expire' !== $type) {
            do_action('letsbox_log_event', 'letsbox_updated_metadata', $cached_entry, ['metadata_field' => 'Sharing Permissions']);
        }

        $this->get_cache()->set_updated();

        return $url;
    }

    public function get_embedded_link($cached_entry, $scope = false)
    {
        if (false === $scope) {
            $scope = ('personal' === $this->get_app()->get_account_type()) ? 'open' : $this->get_processor()->get_setting('link_scope');
        }

        if ($cached_entry instanceof Entry) {
            $cached_entry = $this->get_entry($cached_entry->get_id());
        }

        if (false === $cached_entry->get_entry()->get_can_preview_by_cloud()) {
            return false;
        }

        $embedded_link = $this->get_shared_link($cached_entry, 'view_and_download', $scope);

        return str_replace('.box.com/s', '.box.com/embed_widget/s', $embedded_link).'?view_file_only=yes&show_parent_path=no';
    }

    public function get_temporarily_embedded_link($cached_entry, $scope = false)
    {
        if (false === $scope) {
            $scope = ('personal' === $this->get_app()->get_account_type()) ? 'open' : $this->get_processor()->get_setting('link_scope');
        }

        if ($cached_entry instanceof Entry) {
            $cached_entry = $this->get_entry($cached_entry->get_id());
        }

        if (false === $cached_entry->get_entry()->get_can_preview_by_cloud()) {
            return false;
        }

        return $this->get_shared_link($cached_entry, 'embed_expire', $scope);
    }

    public function shorten_url(CacheNode $cached_entry, $url)
    {
        if (false !== strpos($url, 'localhost')) {
            // Most APIs don't support localhosts
            return $url;
        }

        try {
            switch ($this->get_processor()->get_setting('shortlinks')) {
                case 'Bit.ly':
                    $response = wp_remote_post('https://api-ssl.bitly.com/v4/shorten', [
                        'body' => json_encode(
                            [
                                'long_url' => $url,
                            ]
                        ),
                        'headers' => [
                            'Authorization' => 'Bearer '.$this->get_processor()->get_setting('bitly_apikey'),
                            'Content-Type' => 'application/json',
                        ],
                    ]);

                    $data = json_decode($response['body'], true);

                    return $data['link'];

                case 'Shorte.st':
                    $response = wp_remote_get('https://api.shorte'.'.st/s/'.$this->get_processor()->get_setting('shortest_apikey').'/'.$url);

                    $data = json_decode($response['body'], true);

                    return $data['shortenedUrl'];

                case 'Rebrandly':
                    $response = wp_remote_post('https://api.rebrandly.com/v1/links', [
                        'body' => json_encode(
                            [
                                'title' => $cached_entry->get_name(),
                                'destination' => $url,
                                'domain' => ['fullName' => $this->get_processor()->get_setting('rebrandly_domain')],
                            ]
                        ),
                        'headers' => [
                            'apikey' => $this->get_processor()->get_setting('rebrandly_apikey'),
                            'Content-Type' => 'application/json',
                            'workspace' => $this->get_processor()->get_setting('rebrandly_workspace'),
                        ],
                    ]);

                    $data = json_decode($response['body'], true);

                    return 'https://'.$data['shortUrl'];

                case 'None':
                default:
                    break;
            }
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));

            return $url;
        }

        return $url;
    }

    public function get_thumbnail(Entry $entry, $aslink = false, $width = null, $height = null, $crop = true, $raw = true)
    {
        if (false === $entry->has_own_thumbnail()) {
            $url = $entry->get_icon_large();
        } else {
            $thumbnail = new \TheLion\LetsBox\Thumbnail($this->get_processor(), $entry, $width, $height, $crop, $raw);
            $url = $thumbnail->get_url();
        }

        if ($aslink) {
            return $url;
        }
        header('Location: '.$url);

        exit();
    }

    public function build_thumbnail()
    {
        $src = $_REQUEST['src'];
        preg_match_all('/(.+)_(\d*)_(\d*)_c(\d)_s(\d)_q(\d+)\.([a-z]+)/', $src, $attr, PREG_SET_ORDER);

        if (1 !== count($attr) || 8 !== count($attr[0])) {
            exit();
        }

        $entry_id = $attr[0][1];
        $width = empty($attr[0][2]) ? 0 : $attr[0][2];
        $height = empty($attr[0][3]) ? 0 : $attr[0][3];
        $crop = (1 == $attr[0][4]) ? true : false;
        $raw = (1 == $attr[0][5]) ? true : false;
        $quality = $attr[0][6];
        $format = $attr[0][7];

        $cached_entry = $this->get_entry($entry_id, false);

        if (false === $cached_entry) {
            exit(-1);
        }

        if (false === $cached_entry->get_entry()->has_own_thumbnail()) {
            $url = $cached_entry->get_entry()->get_icon_large();
            header('Location: '.$url);

            exit();
        }

        $thumbnail = new Thumbnail($this->get_processor(), $cached_entry->get_entry(), $width, $height, $crop, $raw, $quality, $format);

        $success = true;
        if (false === $thumbnail->does_thumbnail_exist()) {
            $success = $thumbnail->build_thumbnail();
        }

        if ($success) {
            $url = $thumbnail->get_url();
        } else {
            $cached_entry->get_entry()->set_has_own_thumbnail(false);
            $this->get_cache()->set_updated();
            $url = $cached_entry->get_entry()->get_icon_large();
        }

        header('Location: '.$url);

        exit();
    }

    // Pull for changes

    public function get_changes($change_token = false)
    {
        $list_of_update_entries = [];

        if (empty($change_token)) {
            try {
                $api_response = $this->get_app()->get_client()->getEvents('now', 'changes');

                return [$api_response['next_stream_position'], $list_of_update_entries];
            } catch (\Exception $ex) {
                error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));

                return false;
            }
        }

        try {
            $api_response = $this->get_app()->get_client()->getEvents($change_token, 'changes');
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));

            return false;
        }

        foreach ($api_response['events'] as $change) {
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

        return [$api_response['next_stream_position'], $list_of_update_entries];
    }

    /**
     * @return \TheLion\LetsBox\Processor
     */
    public function get_processor()
    {
        return $this->_processor;
    }

    /**
     * @return \TheLion\LetsBox\Cache
     */
    public function get_cache()
    {
        return $this->get_processor()->get_cache();
    }

    /**
     * @return \TheLion\LetsBox\App
     */
    public function get_app()
    {
        return $this->_app;
    }

    /**
     * @return \Box\Model\Client\Client
     */
    public function get_library()
    {
        return $this->_app->get_client();
    }

    public function _get_folder_recursive(CacheNode $cached_entry, $list_of_cached_entries = [])
    {
        if (false === $this->get_processor()->_is_entry_authorized($cached_entry)) {
            return $list_of_cached_entries;
        }

        if ($cached_entry->get_entry()->is_file()) {
            $list_of_cached_entries[$cached_entry->get_id()] = $cached_entry;

            return $list_of_cached_entries;
        }

        $result = $this->get_folder($cached_entry->get_id());
        if (empty($result)) {
            return $list_of_cached_entries;
        }

        $cached_folder = $result['folder'];

        if (false === $cached_folder->has_children()) {
            return $list_of_cached_entries;
        }

        foreach ($cached_folder->get_children() as $cached_child_entry) {
            $new_of_cached_entries = $this->_get_folder_recursive($cached_child_entry, $list_of_cached_entries);

            foreach ($new_of_cached_entries as $id => $new_cached_entry) {
                $list_of_cached_entries[$id] = $new_cached_entry;
            }

            //$list_of_cached_entries = array_merge($list_of_cached_entries, $new_of_cached_entries);
        }

        return $list_of_cached_entries;
    }
}
