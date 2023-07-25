<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\LetsBox;

class UserFolders
{
    /**
     * The single instance of the class.
     *
     * @var UserFolders
     */
    protected static $_instance;

    /**
     * @var string
     */
    private $_user_name_template;

    /**
     * @var string
     */
    private $_user_folder_name;

    /**
     * @var \TheLion\LetsBox\Entry
     */
    private $_user_folder_entry;

    public function __construct()
    {
        $this->_user_name_template = Processor::instance()->get_setting('userfolder_name');

        $shortcode = Processor::instance()->get_shortcode();
        if (!empty($shortcode) && !empty($shortcode['user_folder_name_template'])) {
            $this->_user_name_template = $shortcode['user_folder_name_template'];
        }
    }

    /**
     * UserFolders Instance.
     *
     * Ensures only one instance is loaded or can be loaded.
     *
     * @return UserFolders - UserFolders instance
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

    public function get_auto_linked_folder_name_for_user()
    {
        $shortcode = Processor::instance()->get_shortcode();
        if (!isset($shortcode['user_upload_folders']) || 'auto' !== $shortcode['user_upload_folders']) {
            return false;
        }

        if (!empty($this->_user_folder_name)) {
            return $this->_user_folder_name;
        }

        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $userfoldername = $this->get_user_name_template($current_user);
        } else {
            $userfoldername = $this->get_guest_user_name();
        }

        $this->_user_folder_name = $userfoldername;

        return $userfoldername;
    }

    public function get_auto_linked_folder_for_user()
    {
        $shortcode = Processor::instance()->get_shortcode();
        if (!isset($shortcode['user_upload_folders']) || 'auto' !== $shortcode['user_upload_folders']) {
            return false;
        }

        if (!empty($this->_user_folder_entry)) {
            return $this->_user_folder_entry;
        }

        // Add folder if needed
        $result = $this->create_user_folder($this->get_auto_linked_folder_name_for_user(), Processor::instance()->get_shortcode(), 0);

        do_action('letsbox_after_private_folder_added', $result, Processor::instance());

        if (false === $result) {
            error_log('[WP Cloud Plugin message]: Cannot find auto folder link for user');

            exit;
        }

        $this->_user_folder_entry = $result;

        return $this->_user_folder_entry;
    }

    public function get_manually_linked_folder_for_user()
    {
        $shortcode = Processor::instance()->get_shortcode();
        if (!isset($shortcode['user_upload_folders']) || 'manual' !== $shortcode['user_upload_folders']) {
            return false;
        }

        if (!empty($this->_user_folder_entry)) {
            return $this->_user_folder_entry;
        }

        $userfolder = get_user_option('lets_box_linkedto');
        if (is_array($userfolder) && isset($userfolder['foldertext'])) {
            if (false === isset($userfolder['accountid'])) {
                $linked_account = Accounts::instance()->get_primary_account();
            } else {
                $linked_account = Accounts::instance()->get_account_by_id($userfolder['accountid']);
            }

            App::set_current_account($linked_account);

            $this->_user_folder_entry = Client::instance()->get_entry($userfolder['folderid'], false);
        } else {
            $defaultuserfolder = get_site_option('lets_box_guestlinkedto');
            if (is_array($defaultuserfolder) && isset($defaultuserfolder['folderid'])) {
                if (false === isset($defaultuserfolder['accountid'])) {
                    $linked_account = Accounts::instance()->get_primary_account();
                } else {
                    $linked_account = Accounts::instance()->get_account_by_id($defaultuserfolder['accountid']);
                }

                App::set_current_account($linked_account);

                $this->_user_folder_entry = Client::instance()->get_entry($defaultuserfolder['folderid'], false);
            } else {
                if (is_user_logged_in()) {
                    $current_user = wp_get_current_user();
                    error_log('[WP Cloud Plugin message]: '.sprintf('Cannot find manual folder link for user: %s', $current_user->user_login));
                } else {
                    error_log('[WP Cloud Plugin message]: Cannot find manual folder link for guest user');
                }

                exit(-1);
            }
        }

        return $this->_user_folder_entry;
    }

    public function manually_link_folder($user_id, $linkedto)
    {
        App::set_current_account_by_id($linkedto['accountid']);
        $node = Client::instance()->get_folder($linkedto['folderid'], false);
        $linkedto['foldertext'] = $node['folder']->get_name();

        if ('GUEST' === $user_id) {
            $result = update_site_option('lets_box_guestlinkedto', $linkedto);
        } else {
            $result = update_user_option($user_id, 'lets_box_linkedto', $linkedto, false);
        }

        $linkedto['path'] = $node['folder']->get_path(API::get_root_folder()->get_id());
        echo json_encode($linkedto);

        exit;
    }

    public function manually_unlink_folder($user_id)
    {
        if ('GUEST' === $user_id) {
            $result = delete_site_option('lets_box_guestlinkedto');
        } else {
            $result = delete_user_option($user_id, 'lets_box_linkedto', false);
        }

        if (false !== $result) {
            exit('1');
        }
    }

    public function create_user_folder($userfoldername, $shortcode, $mswaitaftercreation = 0)
    {
        @set_time_limit(60);

        $parent_folder_data = Client::instance()->get_folder($shortcode['root'], false);

        // If root folder doesn't exists
        if (empty($parent_folder_data)) {
            return false;
        }
        $parent_folder = $parent_folder_data['folder'];

        // Create Folder structure if required (e.g. it contains a /)
        $subfolders = array_filter(explode('/', $userfoldername));
        $userfoldername = array_pop($subfolders);

        foreach ($subfolders as $subfolder) {
            $parent_folder = API::get_sub_folder_by_path($parent_folder->get_id(), $subfolder, true);
        }

        // First try to find the User Folder in Cache
        $userfolder = Cache::instance()->get_node_by_name($userfoldername, $parent_folder);

        /* If User Folder isn't in cache yet,
         * Update the parent folder to make sure the latest version is loaded */
        if (false === $userfolder) {
            Cache::instance()->pull_for_changes(true, -1);
            $userfolder = Cache::instance()->get_node_by_name($userfoldername, $parent_folder);
        }

        // If User Folder still isn't found, create new folder in the Cloud
        if (false === $userfolder) {
            if (empty($shortcode['user_template_dir'])) {
                try {
                    $api_entry = App::instance()->get_sdk_client()->createNewBoxFolder($userfoldername, $parent_folder->get_id());

                    // Wait a moment in case many folders are created at once
                    usleep($mswaitaftercreation);
                } catch (\Exception $ex) {
                    error_log('[WP Cloud Plugin message]: '.sprintf('Failed to add user folder: %s', $ex->getMessage()));

                    return new \WP_Error('broke', esc_html__('Failed to add user folder', 'wpcloudplugins'));
                }
                // Add new file to our Cache
                $newentry = new Entry($api_entry);
                $userfolder = Cache::instance()->add_to_cache($newentry);
                Cache::instance()->update_cache();

                do_action('letsbox_log_event', 'letsbox_created_entry', $userfolder);
            } else {
                // 3: Get the Template folder
                $cached_template_folder = Client::instance()->get_folder($shortcode['user_template_dir'], false);

                // 4: Make sure that the Template folder can be used
                if (false === $cached_template_folder || false === $cached_template_folder['folder'] || false === $cached_template_folder['folder']->has_children()) {
                    error_log('[WP Cloud Plugin message]: Failed to add user folder as the template folder does not exist: %s');

                    return new \WP_Error('broke', esc_html__('Failed to add user folder', 'wpcloudplugins'));
                }

                // Copy the contents of the Template Folder into the User Folder
                try {
                    $api_entry = App::instance()->get_sdk_client()->copyBoxFolder($cached_template_folder['folder']->get_id(), $parent_folder->get_id(), $userfoldername, false);
                    $newentry = new Entry($api_entry);
                    $userfolder = Cache::instance()->add_to_cache($newentry);
                    Cache::instance()->update_cache();

                    do_action('letsbox_log_event', 'letsbox_created_entry', $userfolder);
                } catch (\Exception $ex) {
                    error_log('[WP Cloud Plugin message]: '.sprintf('Failed to add user folder with template folder: %s', $ex->getMessage()));

                    return new \WP_Error('broke', esc_html__('Failed to add user folder with template folder', 'wpcloudplugins'));
                }
            }
        }

        return $userfolder;
    }

    public function create_user_folders_for_shortcodes($user_id)
    {
        $new_user = get_user_by('id', $user_id);

        $letsboxlists = Shortcodes::instance()->get_all_shortcodes();
        $current_account = App::get_current_account();

        foreach ($letsboxlists as $list) {
            if (!isset($list['user_upload_folders']) || 'auto' !== $list['user_upload_folders']) {
                continue;
            }

            if (!isset($list['account']) || $current_account->get_id() !== $list['account']) {
                continue; // Skip shortcodes that don't belong to the account that is being processed
            }

            if (false === Helpers::check_user_role($list['view_role'], $new_user)) {
                continue; // Skip shortcodes that aren't accessible for user
            }

            if (false !== strpos($list['user_upload_folders'], 'disable-create-private-folder-on-registration')) {
                continue; // Skip shortcodes that explicitly have set to skip automatic folder creation
            }

            if (!empty($list['user_folder_name_template'])) {
                $this->_user_name_template = $list['user_folder_name_template'];
            } else {
                $this->_user_name_template = Processor::instance()->get_setting('userfolder_name');
            }

            $new_userfoldersname = $this->get_user_name_template($new_user);

            $result = $this->create_user_folder($new_userfoldersname, $list);

            do_action('letsbox_after_private_folder_added', $result, Processor::instance());
        }
    }

    public function create_user_folders($users = [])
    {
        if (0 === count($users)) {
            return;
        }

        foreach ($users as $user) {
            $userfoldersname = $this->get_user_name_template($user);
            $result = $this->create_user_folder($userfoldersname, Processor::instance()->get_shortcode(), 50);

            do_action('letsbox_after_private_folder_added', $result, Processor::instance());
        }

        Cache::instance()->pull_for_changes(true);
    }

    public function remove_user_folder($user_id)
    {
        $deleted_user = get_user_by('id', $user_id);

        $letsboxlists = Shortcodes::instance()->get_all_shortcodes();
        $current_account = App::get_current_account();

        $update_folders = [];

        foreach ($letsboxlists as $list) {
            if (!isset($list['user_upload_folders']) || 'auto' !== $list['user_upload_folders']) {
                continue;
            }

            if (!isset($list['account']) || $current_account->get_id() !== $list['account']) {
                continue; // Skip shortcodes that don't belong to the account that is being processed
            }

            // Skip shortcode if folder is already updated in an earlier call
            if (isset($update_folders[$list['root']])) {
                continue;
            }

            if (false === Helpers::check_user_role($list['view_role'], $deleted_user)) {
                continue; // Skip shortcodes that aren't accessible for user
            }

            if (!empty($list['user_folder_name_template'])) {
                $this->_user_name_template = $list['user_folder_name_template'];
            } else {
                $this->_user_name_template = Processor::instance()->get_setting('userfolder_name');
            }

            $userfoldername = $this->get_user_name_template($deleted_user);

            // 2: try to find the User Folder in Cache
            $userfolder = Cache::instance()->get_node_by_name($userfoldername, $list['root']);
            if (!empty($userfolder)) {
                try {
                    $box_entry = new \Box\Model\Folder\Folder();
                    $box_entry->setId($userfolder->get_id());
                    $deleted_entry = App::instance()->get_sdk_client()->deleteEntry($box_entry);
                    $update_folders[$list['root']] = true;
                } catch (\Exception $ex) {
                    error_log('[WP Cloud Plugin message]: '.sprintf('Failed to remove user folder: %s', $ex->getMessage()));

                    continue;
                }
            } else {
                // Find all items containing query
                try {
                    $found_entries = App::instance()->get_sdk_client()->search(stripslashes($userfoldername), $list['root'], null, 'folder', 'name');
                } catch (\Exception $ex) {
                    error_log('[WP Cloud Plugin message]: '.sprintf('Failed to remove user folder: %s', $ex->getMessage()));

                    return false;
                }

                // Stop when no User Folders are found
                if (0 === count($found_entries)) {
                    $update_folders[$list['root']] = true;

                    continue;
                }

                // Delete all the user folders that are found
                foreach ($found_entries as $api_file) {
                    if ($api_file->getName() !== $userfoldername) {
                        continue;
                    }

                    try {
                        $box_entry = new \Box\Model\Folder\Folder();
                        $box_entry->setId($api_file->getId());
                        $deleted_entry = App::instance()->get_sdk_client()->deleteEntry($box_entry);
                    } catch (\Exception $ex) {
                        error_log('[WP Cloud Plugin message]: '.sprintf('Failed to remove user folder: %s', $ex->getMessage()));

                        continue;
                    }
                }

                $update_folders[$list['root']] = true;
            }
        }

        Cache::instance()->pull_for_changes(true);

        return true;
    }

    public function update_user_folder($user_id, $old_user)
    {
        $updated_user = get_user_by('id', $user_id);

        $letsboxlists = Shortcodes::instance()->get_all_shortcodes();
        $current_account = App::get_current_account();

        Cache::instance()->pull_for_changes(true);

        foreach ($letsboxlists as $list) {
            if (!isset($list['user_upload_folders']) || 'auto' !== $list['user_upload_folders']) {
                continue;
            }

            if (!isset($list['account']) || $current_account->get_id() !== $list['account']) {
                continue; // Skip shortcodes that don't belong to the account that is being processed
            }

            if (false === Helpers::check_user_role($list['view_role'], $updated_user)) {
                continue; // Skip shortcodes that aren't accessible for user
            }

            if (!empty($list['user_folder_name_template'])) {
                $this->_user_name_template = $list['user_folder_name_template'];
            } else {
                $this->_user_name_template = Processor::instance()->get_setting('userfolder_name');
            }
            $new_userfoldersname = $this->get_user_name_template($updated_user);
            $old_userfoldersname = $this->get_user_name_template($old_user);

            if ($new_userfoldersname === $old_userfoldersname) {
                continue;
            }

            if (defined('lets_box_update_user_folder_'.$list['root'].'_'.$new_userfoldersname)) {
                continue;
            }

            define('lets_box_update_user_folder_'.$list['root'].'_'.$new_userfoldersname, true);

            // 1: Create an the entry for Patch
            $updaterequest = ['name' => $new_userfoldersname];
            $box_entry = new \Box\Model\Folder\Folder();

            // 2: try to find the User Folder in Cache
            $userfolder = Cache::instance()->get_node_by_name($old_userfoldersname, $list['root']);
            if (!empty($userfolder)) {
                try {
                    $box_entry->setId($userfolder->get_id());
                    $api_entry = App::instance()->get_sdk_client()->updateEntry($box_entry, $updaterequest);
                } catch (\Exception $ex) {
                    error_log('[WP Cloud Plugin message]: '.sprintf('Failed to update user folder: %s', $ex->getMessage()));

                    continue;
                }
            } else {
                // Find all items containing query

                try {
                    $found_entries = App::instance()->get_sdk_client()->search(stripslashes($old_userfoldersname), $list['root'], null, 'folder', 'name');
                } catch (\Exception $ex) {
                    error_log('[WP Cloud Plugin message]: '.sprintf('Failed to update user folder: %s', $ex->getMessage()));

                    return false;
                }

                // Stop when no User Folders are found
                if (0 === count($found_entries)) {
                    continue;
                }

                // Delete all the user folders that are found
                foreach ($found_entries as $api_file) {
                    if ($api_file->getName() !== $old_userfoldersname) {
                        continue;
                    }

                    try {
                        $box_entry->setId($api_file->getId());
                        $api_entry = App::instance()->get_sdk_client()->updateEntry($box_entry, $updaterequest);
                    } catch (\Exception $ex) {
                        error_log('[WP Cloud Plugin message]: '.sprintf('Failed to update user folder: %s', $ex->getMessage()));

                        continue;
                    }
                }
            }
        }

        Cache::instance()->pull_for_changes(true);

        return true;
    }

    public function get_user_name_template($user_data)
    {
        $user_folder_name = Helpers::apply_placeholders($this->_user_name_template, Processor::instance(), ['user_data' => $user_data]);

        $user_folder_name = ltrim(Helpers::clean_folder_path($user_folder_name), '/');

        return apply_filters('letsbox_private_folder_name', $user_folder_name, Processor::instance());
    }

    public function get_guest_user_name()
    {
        $username = $this->get_guest_id();

        $current_user = new \stdClass();
        $current_user->user_login = md5($username);
        $current_user->display_name = $username;
        $current_user->ID = $username;
        $current_user->user_role = esc_html__('Anonymous user', 'wpcloudplugins');

        $user_folder_name = $this->get_user_name_template($current_user);

        $prefix = Processor::instance()->get_setting('userfolder_name_guest_prefix');

        return apply_filters('letsbox_private_folder_name_guests', $prefix.$user_folder_name, Processor::instance());
    }

    public function get_guest_id()
    {
        $id = uniqid();
        if (!isset($_COOKIE['LB-ID'])) {
            $expire = time() + 60 * 60 * 24 * 7;
            Helpers::set_cookie('LB-ID', $id, $expire, COOKIEPATH, COOKIE_DOMAIN, false, false, 'strict');
        } else {
            $id = $_COOKIE['LB-ID'];
        }

        return $id;
    }
}
