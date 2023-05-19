<?php
/*
 * API Class.
 *
 * Use the API to execute calls directly for the set cloud account.
 * You can use the API using WPCP_BOX_API::get_entry(...)
 *
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2022, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\LetsBox;

if (!function_exists('\Box\autoload')) {
    require_once LETSBOX_ROOTDIR.'/vendors/API/autoload.php';
}

defined('ABSPATH') || exit; // Exit if accessed directly.

class API
{
    /**
     * Set which cloud account should be used.
     *
     * @param string $account_id
     *
     * @return Account|false - Account
     */
    public static function set_account_by_id($account_id)
    {
        $account = Accounts::instance()->get_account_by_id($account_id);
        if (null === $account) {
            error_log(sprintf('[WP Cloud Plugin message]: API Error on line %s: Cannot use the requested account (ID: %s) as it is not linked with the plugin', __LINE__, $account_id));

            return false;
        }

        return App::set_current_account($account);
    }

    /**
     * @param string $id     ID of the entry that should be loaded
     * @param array  $params
     *
     * @return API_Exception|CacheNode
     */
    public static function get_entry($id, $params = [])
    {
        // Load the root folder when needed
        if (0 !== $id) {
            self::get_root_folder();
        }

        // Get entry from cache
        $cached_node = Cache::instance()->is_cached($id);

        if (!empty($cached_node)) {
            return $cached_node;
        }

        do_action('letsbox_api_before_get_entry', $id);

        $is_in_cache = Cache::instance()->get_node_by_id($id);
        $type = (!empty($is_in_cache) && $is_in_cache->get_entry()->is_dir()) ? 'folder' : 'file';

        try {
            if ('file' === $type) {
                $api_entry = App::instance()->get_sdk_client()->getFileFromBox($id);
            } else {
                return self::get_folder($id);
            }
        } catch (\Exception $ex) {
            return self::get_folder($id);
            // Now try to get it as folder
        }

        $entry = new Entry($api_entry);
        $node = Cache::instance()->add_to_cache($entry);

        do_action('letsbox_api_after_get_entry', $node);

        return $node;
    }

    /**
     * Get folder information. Metadata of direct child files are loaded as well.
     *
     * @param string $id     ID of the folder that should be loaded
     * @param array  $params
     *
     * @return API_Exception|CacheNode
     */
    public static function get_folder($id, $params = [])
    {
        // Load the root folder when needed
        if (0 !== $id) {
            self::get_root_folder();
        }

        // Load cached folder if present
        $cached_node = Cache::instance()->is_cached($id, 'id', false);

        if (!empty($cached_node)) {
            return $cached_node;
        }

        do_action('letsbox_api_before_get_folder', $id);

        try {
            $api_entry = App::instance()->get_sdk_client()->getFolderFromBox($id);
        } catch (\Exception $ex) {
            Cache::instance()->reset_cache();
            error_log(sprintf('[WP Cloud Plugin message]: API Error on line %s: %s', __LINE__, $ex->getMessage()));

            throw new API_Exception(esc_html__('Failed to load file.', 'wpcloudplugins'));
        }

        $entry = new Entry($api_entry);
        $node = Cache::instance()->add_to_cache($entry);

        try {
            App::instance()->get_sdk_client()->getBoxFolderItems($api_entry, 1000);
        } catch (\Exception $ex) {
            Cache::instance()->reset_cache();
            error_log(sprintf('[WP Cloud Plugin message]: API Error on line %s: %s', __LINE__, $ex->getMessage()));

            return $node;
        }

        // Convert the items to Entry & and them to cache
        foreach ($api_entry->getItems() as $entry) {
            $item = new Entry($entry);

            if (true === $item->get_trashed()) {
                continue;
            }

            Cache::instance()->add_to_cache($item);
        }

        $node->set_loaded_children(true);

        Cache::instance()->update_cache();

        do_action('letsbox_api_after_get_folder', $node);

        return $node;
    }

    /**
     * Get the root folder of the Box account. Metadata of direct child files are loaded as well.
     *
     * @return API_Exception|CacheNode
     */
    public static function get_root_folder()
    {
        $root_node = Cache::instance()->get_root_node();

        if (false !== $root_node) {
            return $root_node;
        }

        // First get the root of the cloud
        try {
            $root = self::get_folder(0);
            $root->set_expired(null);
            // Also make sure that we got the latest changes position
            Cache::instance()->pull_for_changes(true);
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s not able to retreive root folder on your Box: %s', __LINE__, $ex->getMessage()));

            return false;
        }

        return Cache::instance()->get_root_node();
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
    public static function get_sub_folder_by_path($parent_folder_id, $subfolder_path, $create_if_not_exists = false)
    {
        $cached_parent_folder = self::get_folder($parent_folder_id);

        if (empty($cached_parent_folder)) {
            return false;
        }

        if (empty($subfolder_path)) {
            return $cached_parent_folder;
        }

        $subfolders = array_filter(explode('/', $subfolder_path));
        $current_folder = array_shift($subfolders);

        // Try to load the subfolder at once
        $cached_sub_folder = Cache::instance()->get_node_by_name($current_folder, $parent_folder_id);

        /* If folder isn't in cache yet,
         * Update the parent folder to make sure the latest version is loaded */
        if (false === $cached_sub_folder) {
            Cache::instance()->pull_for_changes(true, -1);
            $cached_sub_folder = Cache::instance()->get_node_by_name($current_folder, $parent_folder_id);
        }

        if (false === $cached_sub_folder && false === $create_if_not_exists) {
            return false;
        }

        // If the subfolder can't be found, create the sub folder
        if (!$cached_sub_folder) {
            try {
                $cached_sub_folder = API::create_folder($current_folder, $parent_folder_id);
            } catch (\Exception $ex) {
                return false;
            }
        }

        return self::get_sub_folder_by_path($cached_sub_folder->get_id(), implode('/', $subfolders), $create_if_not_exists);
    }

    /**
     * Create a new folder in the Cloud Account.
     *
     * @param string $new_name  the name for the newly created folder
     * @param string $parent_id ID of the folder where the new folder should be created
     * @param array  $params
     *
     * @return API_Exception|CacheNode
     */
    public static function create_folder($new_name, $parent_id, $params = [])
    {
        $parent_id = apply_filters('letsbox_api_create_folder_set_parent_id', $parent_id);
        $params = apply_filters('letsbox_api_create_folder_set_params', $params);

        do_action('letsbox_api_before_create_folder', $new_name, $parent_id, $params);

        try {
            $api_entry = App::instance()->get_sdk_client()->createNewBoxFolder($new_name, $parent_id);
            $newentry = new Entry($api_entry);
            $node = Cache::instance()->add_to_cache($newentry);

            do_action('letsbox_log_event', 'letsbox_created_entry', $node);
        } catch (\Exception $ex) {
            Cache::instance()->reset_cache();
            error_log(sprintf('[WP Cloud Plugin message]: API Error on line %s: %s', __LINE__, $ex->getMessage()));

            throw new API_Exception(esc_html__('Failed to create folder.', 'wpcloudplugins'));
        }

        Cache::instance()->pull_for_changes(true);

        do_action('letsbox_api_after_create_folder', $node);

        return $node;
    }

    /**
     * Create a temporarily download url for a file or folder.
     *
     * @param string $id     ID of the entry for which you want to create the temporarily download url
     * @param string $format Format for the downloaded file. Only 'default' currently supported
     * @param array  $params
     *
     * @return API_Exception|string
     */
    public static function create_temporarily_download_url($id, $format = 'default', $params = [])
    {
        do_action('letsbox_api_before_create_temporarily_download_url', $id, $format, $params);

        try {
            // Get a Download link via the Box API
            switch ($format) {
                case 'default':
                default:
                    $url = App::instance()->get_sdk_client()->downloadFile($id, false);
            }

            if (empty($url)) {
                error_log(sprintf('Cannot generate temporarily download link:', __LINE__));

                return false;
            }
        } catch (\Exception $ex) {
            error_log(sprintf('[WP Cloud Plugin message]: API Error on line %s: %s', __LINE__, $ex->getMessage()));

            return false;
        }

        $url = apply_filters('letsbox_api_create_temporarily_download_url_set_url', $url);

        do_action('letsbox_api_after_create_temporarily_download_url', $id, $format, $url);

        return $url;
    }

    /**
     * Create a public shared url for a file or folder.
     *
     * @param string $id     ID of the entry for which you want to create the shared url
     * @param bool   $is_dir Is the entry a folder or a file
     * @param string $params Link permissions (scope, expire date, password, etc)
     *
     * @return API_Exception|array Returns an array with shared link information
     */
    public static function create_shared_url($id, $is_dir, $params = ['access' => 'open'])
    {
        $params = [
            'shared_link' => $params,
        ];

        $params = apply_filters('letsbox_api_create_shared_url_set_params', $params);

        do_action('letsbox_api_before_create_shared_url', $id, $is_dir, $params);

        try {
            if ($is_dir) {
                $link = App::instance()->get_sdk_client()->createSharedLinkForFolder($id, $params);
            } else {
                $link = App::instance()->get_sdk_client()->createSharedLinkForFile($id, $params);
            }
        } catch (\Exception $ex) {
            error_log(sprintf('[WP Cloud Plugin message]: API Error on line %s: %s', __LINE__, $ex->getMessage()));

            return false;
        }

        $link = apply_filters('letsbox_api_create_shared_url_set_link', $link);

        do_action('letsbox_log_event', 'letsbox_created_link_to_entry', $id, ['url' => $link['url']]);
        do_action('letsbox_log_event', 'letsbox_updated_metadata', $id, ['metadata_field' => 'Sharing Permissions']);
        do_action('letsbox_api_after_create_shared_url', $link);

        return $link;
    }

    /**
     * Create a public embed url for a file.
     *
     * @param string $id         ID of the entry for which you want to create the embed url
     * @param bool   $is_dir     Is the entry a folder or a file
     * @param array  $params     Link permissions (scope, expire date, password, etc)
     * @param bool   $shortlived Should the embedded url expire (~ 60 secondes)
     *
     * @return API_Exception|array Returns an array with shared link information
     */
    public static function create_embed_url($id, $is_dir, $params = [], $shortlived = true)
    {
        if ($shortlived) {
            try {
                $link = App::instance()->get_sdk_client()->createShortLivedEmbed($id, $params);
                $link['unshared_at'] = current_datetime()->modify('+60 seconds')->format(DATE_RFC3339);
            } catch (\Exception $ex) {
                error_log(sprintf('[WP Cloud Plugin message]: API Error on line %s: %s', __LINE__, $ex->getMessage()));

                return false;
            }
        } else {
            $link = self::create_shared_url($id, $is_dir, $params);
        }

        $link['url'] = str_replace('.box.com/s', '.box.com/embed_widget/s', $link['url']).'?view_file_only=yes&show_parent_path=no';

        $embedded_link = apply_filters('letsbox_api_create_embed_url_set_link', $link);

        do_action('letsbox_api_after_create_embedded_url', $embedded_link);

        return $embedded_link;
    }

    /**
     * Create an url to an editable view of the file.
     * NOT SUPPORTED BY BOX.
     *
     * @param string $id     ID of the entry for which you want to create the editable url
     * @param array  $params
     *
     * @return API_Exception|string
     */
    public static function create_edit_url($id, $params = [])
    {
        $params = apply_filters('letsbox_api_create_edit_url_set_params', $params);

        do_action('letsbox_api_before_create_edit_url', $id, $params);

        $link = null; // NOT SUPPORTED BY BOX.

        do_action('letsbox_api_after_create_edit_url', $link);

        return $link;
    }

    /**
     * Create an url to a preview of the file.
     *
     * @param string $id     ID of the entry for which you want to create the preview
     * @param array  $params
     *
     * @return API_Exception|string
     */
    public static function create_preview_url($id, $params = [])
    {
        do_action('letsbox_api_before_create_preview_url', $id, $params);
        $params = apply_filters('letsbox_api_create_preview_url_set_params', $params);

        try {
            $link = App::instance()->get_sdk_client()->createShortLivedEmbed($id, $params);
        } catch (\Exception $ex) {
            error_log(sprintf('[WP Cloud Plugin message]: API Error on line %s: %s', __LINE__, $ex->getMessage()));

            return false;
        }

        $link = apply_filters('letsbox_api_create_preview_url_set_link', $link);

        do_action('letsbox_api_after_create_preview_url', $link);

        return $link;
    }

    /**
     * Copy an entry to a new location.
     *
     * @param string $id            ID of the entry that should be copied
     * @param string $new_parent_id ID of the folder where the entry should be copied to
     * @param string $new_name      The new name for the copied file. No value means that the same name will be used
     * @param bool   $is_dir        Is the entry a folder or a file
     * @param array  $params        ['original_name'=>'...']
     *
     * @return API_Exception|CacheNode
     */
    public static function copy($id, $new_parent_id, $is_dir, $new_name = null, $params = [])
    {
        $new_parent_id = apply_filters('letsbox_api_copy_set_new_parent_id', $new_parent_id);
        $new_name = apply_filters('letsbox_api_copy_set_new_name', $new_name);
        $params = apply_filters('letsbox_api_copy_set_params', $params);

        do_action('letsbox_api_before_copy', $id, $new_parent_id, $is_dir, $new_name, $params);

        try {
            if ($is_dir) {
                $api_entry = App::instance()->get_sdk_client()->copyBoxFolder($id, $new_parent_id, $new_name, false);
            } else {
                $api_entry = App::instance()->get_sdk_client()->copyBoxFile($id, $new_parent_id, $new_name);
            }

            $new_entry = new Entry($api_entry);
            $node = Cache::instance()->add_to_cache($new_entry);

            if (false !== $node && null !== $node) {
                Cache::instance()->update_cache();
                CacheRequest::clear_local_cache_for_shortcode(App::get_current_account()->get_id(), Processor::instance()->get_listtoken());
            }

            do_action('letsbox_log_event', 'letsbox_copied_entry', $node, ['original' => !empty($params['original_name']) ? $params['original_name'] : $id]);
        } catch (\Exception $ex) {
            Cache::instance()->reset_cache();
            error_log(sprintf('[WP Cloud Plugin message]: API Error on line %s: %s', __LINE__, $ex->getMessage()));

            throw new API_Exception(esc_html__('Failed to copy file.', 'wpcloudplugins'));
        }

        do_action('letsbox_api_after_copy', $node);

        return $node;
    }

    /**
     * Move an entry to a new location.
     *
     * @param string $id            ID of the entry that should be moved
     * @param string $new_parent_id ID of the folder where the entry should be copied to
     * @param bool   $is_dir        Is the entry a folder or a file
     * @param array  $params
     *
     * @return API_Exception|CacheNode
     */
    public static function move($id, $new_parent_id, $is_dir, $params = [])
    {
        $new_parent_id = apply_filters('letsbox_api_move_set_new_parent_id', $new_parent_id);
        $params = apply_filters('letsbox_api_move_set_params', $params);

        do_action('letsbox_api_before_move', $id, $new_parent_id, $is_dir, $params);

        try {
            $update_request = [
                'parent' => [
                    'id' => $new_parent_id,
                ],
            ];

            $node = self::patch($id, $update_request, $is_dir);

            do_action('letsbox_log_event', 'letsbox_moved_entry', $node);
        } catch (\Exception $ex) {
            Cache::instance()->reset_cache();
            error_log(sprintf('[WP Cloud Plugin message]: API Error on line %s: %s', __LINE__, $ex->getMessage()));

            throw new API_Exception(esc_html__('Failed to move file.', 'wpcloudplugins'));
        }

        Cache::instance()->update_cache();

        // Clear Cached Requests
        CacheRequest::clear_local_cache_for_shortcode(App::get_current_account()->get_id(), Processor::instance()->get_listtoken());

        do_action('letsbox_api_after_move', $node);

        return $node;
    }

    /**
     * Update an file. This can be e.g. used to rename a file.
     *
     * @param string $id             ID of the entry that should be updated
     * @param array  $update_request The content that should be patched. E.g. ['name'=>'new_name']
     * @param bool   $is_dir         Is the entry a folder or a file
     *
     * @return API_Exception|CacheNode
     */
    public static function patch($id, $update_request, $is_dir)
    {
        $default_request = [
            'fields' => [
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
            ], ];

        $update_request = array_merge($default_request, $update_request);
        $update_request = apply_filters('letsbox_api_patch_set_update_request', $update_request);

        if ($is_dir) {
            $box_entry = App::instance()->get_sdk_client()->getNewFolder();
        } else {
            $box_entry = App::instance()->get_sdk_client()->getNewFile();
        }
        $box_entry->setId($id);

        do_action('letsbox_api_before_patch', $id, $update_request, $is_dir);

        try {
            $api_entry = App::instance()->get_sdk_client()->updateEntry($box_entry, $update_request);

            // Remove item from cache if it is moved
            if (isset($update_request['parent'])) {
                Cache::instance()->remove_from_cache($id, 'deleted');
            }

            $entry = new Entry($api_entry);
            $node = Cache::instance()->add_to_cache($entry);
            Cache::instance()->update_cache();
        } catch (\Exception $ex) {
            Cache::instance()->reset_cache();
            error_log(sprintf('[WP Cloud Plugin message]: API Error on line %s: %s', __LINE__, $ex->getMessage()));

            throw new API_Exception(esc_html__('Failed to patch file.', 'wpcloudplugins'));
        }

        do_action('letsbox_api_after_patch', $node);

        return $node;
    }

    /**
     * Delete an file.
     *
     * @param string $id     ID of the entry that should be deleted
     * @param bool   $is_dir Is the entry a folder or a file
     * @param array  $params
     *
     * @return API_Exception|CacheNode
     */
    public static function delete_entry($id, $is_dir, $params = [])
    {
        if ($is_dir) {
            $box_entry = App::instance()->get_sdk_client()->getNewFolder();
        } else {
            $box_entry = App::instance()->get_sdk_client()->getNewFile();
        }
        $box_entry->setId($id);

        do_action('letsbox_api_before_delete', $id, $params);

        try {
            $is_deleted = App::instance()->get_sdk_client()->deleteEntry($box_entry);

            if (!$is_deleted) {
                return false;
            }

            do_action('letsbox_log_event', 'letsbox_deleted_entry', $id, []);
            Cache::instance()->remove_from_cache($id, 'deleted');
        } catch (\Exception $ex) {
            Cache::instance()->reset_cache();
            error_log(sprintf('[WP Cloud Plugin message]: API Error on line %s: %s', __LINE__, $ex->getMessage()));

            throw new API_Exception(esc_html__('Failed to delete file.', 'wpcloudplugins'));
        }

        // Remove items from cache
        Cache::instance()->pull_for_changes(true);

        // Clear Cached Requests
        CacheRequest::clear_request_cache();

        do_action('letsbox_api_after_delete', $id, $params);

        return true;
    }

    /**
     * Get the account information.
     *
     * @return \Box\Model\User
     */
    public static function get_account_info()
    {
        $cache_key = 'letsbox_account_'.App::get_current_account()->get_id();
        if (empty($account_info = get_transient($cache_key, false))) {
            $account_info = App::instance()->get_sdk_client()->getUserInfo();
            \set_transient($cache_key, $account_info, HOUR_IN_SECONDS);
        }

        return $account_info;
    }

    /**
     * Get the information about the available space.
     *
     * @return \Box\Model\User
     */
    public static function get_space_info()
    {
        return self::get_account_info();
    }

    /**
     * Search Cloud account.
     *
     * @param string $query         The search query itself
     * @param string $folder_id     ID of the folder where the search should take place
     * @param array  $content_types Array of metadata fields that will be matched against the search query. E.g. name,description
     * @param array  $extensions    Array of extra search data. E.g. ['jpg','png','tiff','etc']
     * @param string $type          Limits the search results to any items of this type. This parameter only takes one value. By default the API returns items that match any of these types: 'file', 'folder','web_link'
     * @param int    $limit         defines the maximum number of items to return as part of a page of results
     * @param int    $offset        the offset of the item at which to begin the response
     * @param array  $params
     *
     * @return API_Exception|CacheNode[]
     */
    public static function search($query, $folder_id, $content_types = [], $extensions = null, $type = null, $limit = 200, $offset = 0, $params = [])
    {
        // Set all params
        $query = apply_filters('letsbox_api_search_set_query', stripslashes($query));
        $content_types = apply_filters('letsbox_api_search_set_content_types', $content_types);
        $extensions = apply_filters('letsbox_api_search_set_extensions', $extensions);
        $type = apply_filters('letsbox_api_search_set_type', $type);
        $limit = apply_filters('letsbox_api_search_set_limit', $limit);
        $offset = apply_filters('letsbox_api_search_set_offset', $offset);
        $params = apply_filters('letsbox_api_search_set_params', $params);

        $content_types = is_array($content_types) ? join(',', $content_types) : $content_types;
        $extensions = is_array($extensions) ? join(',', $extensions) : $extensions;

        // Do the search
        do_action('letsbox_api_before_search', $query, $folder_id, $content_types, $extensions, $type, $limit, $offset, $params);

        $searched_folder = self::get_folder($folder_id);

        do_action('letsbox_log_event', 'letsbox_searched', $searched_folder, ['query' => $query]);

        try {
            $api_entries = App::instance()->get_sdk_client()->search($query, $folder_id, $extensions, $type, $content_types, $limit, $offset);
        } catch (\Exception $ex) {
            error_log(sprintf('[WP Cloud Plugin message]: API Error on line %s: %s', __LINE__, $ex->getMessage()));

            return [];
        }

        $cached_entries = [];
        foreach ($api_entries as $file) {
            // Only return files that match the name if that is the requested content type

            if ('name' === $content_types) {
                if (false === stripos($file->getName(), $query)) {
                    continue;
                }
            }

            $node = Cache::instance()->is_cached($file->getId());

            if (false === $node) {
                $node = Cache::instance()->add_to_cache(new Entry($file));
            }

            $cached_entries[] = $node;
        }

        Cache::instance()->update_cache();

        do_action('letsbox_api_after_search', $cached_entries);

        return $cached_entries;
    }

    /**
     * Upload a file to the cloud using a simple file object.
     *
     * @param string      $upload_folder_id ID of the upload folder
     * @param null|string $description      Add a description to the file
     * @param bool        $overwrite        should we overwrite an existing file with the same name? If false, the file will be renamed
     * @param stdClass    $file             Object containing the file details. Same as file object in $_FILES.
     *                                      <code>
     *                                      $file = object {
     *                                      'name' : 'filename.ext',
     *                                      'type' : 'image/jpeg',
     *                                      'tmp_name'=> '...\php8D2C.tmp
     *                                      'size' => 1274994
     *                                      }
     *                                      </code>
     */
    public static function upload_file($file, $upload_folder_id, $description = null, $overwrite = false)
    {
        $upload_folder_id = apply_filters('letsbox_api_upload_set_upload_folder_id', $upload_folder_id);
        $file->name = apply_filters('letsbox_api_upload_set_file_name', $file->name);
        $file = apply_filters('letsbox_api_upload_set_file', $file);
        $description = apply_filters('letsbox_api_upload_set_description', $description);
        $overwrite = apply_filters('letsbox_api_upload_set_overwrite', $overwrite);

        do_action('letsbox_api_before_upload', $upload_folder_id, $file, $description, $overwrite);

        // If we need to overwrite a file, we first have to get the ID of that file.
        $overwrite_id = false;
        if ($overwrite) {
            $node = Cache::instance()->get_node_by_name($file->name, $upload_folder_id);

            if ($node) {
                $overwrite_id = $node->get_id();
            } else {
                $existing_entry_nodes = API::search($file->name, $upload_folder_id, null, null, 'file', 'name,id');

                if (!empty($existing_entry_nodes)) {
                    foreach ($existing_entry_nodes as $existing_entry_node) {
                        if ($existing_entry_node->get_name() === $file->name) {
                            $overwrite_id = $existing_entry_node->get_id();
                        }
                    }
                }
            }
        }

        // Do the actual upload
        try {
            $api_result = App::instance()->get_sdk_client()->uploadFileToBox($file, $upload_folder_id, $overwrite_id);
            $entry = new Entry($api_result);
            $node = Cache::instance()->add_to_cache($entry);

            // Set new description, and update the entry
            if (!empty($description)) {
                $node = API::patch($entry->get_id(), ['description' => $description], $entry->is_dir());
            }
        } catch (\Exception $ex) {
            Cache::instance()->reset_cache();
            error_log(sprintf('Not uploaded to the cloud on line %s: %s', __LINE__, $ex->getMessage()));

            return false;
        }

        do_action('letsbox_log_event', 'letsbox_uploaded_entry', $node);

        do_action('letsbox_api_after_upload', $node);

        return $node;
    }

    /**
     * Get a shortened url via the requested service.
     *
     * @param string $url
     * @param string $service
     * @param array  $params  Add extra data that can be used for certain services, e.g. ['name' => $node->get_name()]
     *
     * @return API_Exception|string The shortened url
     */
    public static function shorten_url($url, $service = null, $params = [])
    {
        if (empty($service)) {
            $service = Core::get_setting('shortlinks');
        }

        $service = apply_filters('letsbox_api_shorten_url_set_service', $service);

        do_action('letsbox_api_before_shorten_url', $url, $service, $params);

        if (false !== strpos($url, 'localhost')) {
            // Most APIs don't support localhosts
            return $url;
        }

        try {
            switch ($service) {
                case 'Bit.ly':
                    $response = wp_remote_post('https://api-ssl.bitly.com/v4/shorten', [
                        'body' => json_encode(
                            [
                                'long_url' => $url,
                            ]
                        ),
                        'headers' => [
                            'Authorization' => 'Bearer '.Core::get_setting('bitly_apikey'),
                            'Content-Type' => 'application/json',
                        ],
                    ]);

                    $data = json_decode($response['body'], true);

                    return $data['link'];

                case 'Shorte.st':
                    $response = wp_remote_get('https://api.shorte.st/s/'.Core::get_setting('shortest_apikey').'/'.$url);

                    $data = json_decode($response['body'], true);

                    return $data['shortenedUrl'];

                case 'Tinyurl':
                    $response = wp_remote_post('https://api.tinyurl.com/create?api_token='.Core::get_setting('tinyurl_apikey'), [
                        'body' => json_encode(
                            [
                                'url' => $url,
                                'domain' => Core::get_setting('tinyurl_domain'),
                            ]
                        ),
                        'headers' => [
                            'Content-Type' => 'application/json',
                        ],
                    ]);

                    $data = json_decode($response['body'], true);

                    return (!empty($data['errors'])) ? htmlspecialchars(reset($data['errors']), ENT_QUOTES) : $data['data']['tiny_url'];

                case 'Rebrandly':
                    $response = wp_remote_post('https://api.rebrandly.com/v1/links', [
                        'body' => json_encode(
                            [
                                'title' => isset($params['name']) ? $params['name'] : '',
                                'destination' => $url,
                                'domain' => ['fullName' => Core::get_setting('rebrandly_domain')],
                            ]
                        ),
                        'headers' => [
                            'apikey' => Core::get_setting('rebrandly_apikey'),
                            'Content-Type' => 'application/json',
                            'workspace' => Core::get_setting('rebrandly_workspace'),
                        ],
                    ]);

                    $data = json_decode($response['body'], true);

                    return 'https://'.$data['shortUrl'];

                case 'None':
                default:
                    break;
            }
        } catch (\Exception $ex) {
            error_log(sprintf('[WP Cloud Plugin message]: API Error on line %s: %s', __LINE__, $ex->getMessage()));

            return $url;
        }

        $shortened_url = apply_filters('letsbox_api_shorten_url_set_shortened_url', $url);

        do_action('letsbox_api_after_shorten_url', $shortened_url);

        return $shortened_url;
    }

    /**
     * Get a a list of files inside a folder.
     *
     * @param string $id    The folder ID
     * @param array  $nodes Contains an array of nodes found so far. Default: []
     *
     * @return array
     */
    public static function get_folder_recursive($id, $nodes = [])
    {
        $node = self::get_folder($id);

        if ($node->get_entry()->is_file()) {
            $nodes[$node->get_id()] = $node;

            return $nodes;
        }

        $folder_node = self::get_folder($node->get_id());
        if (empty($folder_node)) {
            return $nodes;
        }

        if (false === $folder_node->has_children()) {
            return $nodes;
        }

        foreach ($folder_node->get_children() as $cached_child_entry) {
            $nodes = self::get_folder_recursive($cached_child_entry->get_id(), $nodes);
        }

        return $nodes;
    }
}

/**
 * API_Exception Class.
 *
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2022, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */
class API_Exception extends \Exception
{
}
