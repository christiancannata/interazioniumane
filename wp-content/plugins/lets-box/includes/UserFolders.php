<?php

namespace TheLion\LetsBox;

class UserFolders
{
    /**
     * @var \TheLion\LetsBox\Client
     */
    private $_client;

    /**
     * @var \TheLion\LetsBox\Processor
     */
    private $_processor;

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

    public function __construct(Processor $_processor = null)
    {
        $this->_client = $_processor->get_client();
        $this->_processor = $_processor;
        $this->_user_name_template = $this->get_processor()->get_setting('userfolder_name');

        $shortcode = $this->get_processor()->get_shortcode();
        if (!empty($shortcode) && !empty($shortcode['user_folder_name_template'])) {
            $this->_user_name_template = $shortcode['user_folder_name_template'];
        }
    }

    public function get_auto_linked_folder_name_for_user()
    {
        $shortcode = $this->get_processor()->get_shortcode();
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
        $shortcode = $this->get_processor()->get_shortcode();
        if (!isset($shortcode['user_upload_folders']) || 'auto' !== $shortcode['user_upload_folders']) {
            return false;
        }

        if (!empty($this->_user_folder_entry)) {
            return $this->_user_folder_entry;
        }

        // Add folder if needed
        $result = $this->create_user_folder($this->get_auto_linked_folder_name_for_user(), $this->get_processor()->get_shortcode(), 0);

        do_action('letsbox_after_private_folder_added', $result, $this->_processor);

        if (false === $result) {
            error_log('[WP Cloud Plugin message]: '.'Cannot find auto folder link for user');

            exit();
        }

        $this->_user_folder_entry = $result;

        return $this->_user_folder_entry;
    }

    public function get_manually_linked_folder_for_user()
    {
        $shortcode = $this->get_processor()->get_shortcode();
        if (!isset($shortcode['user_upload_folders']) || 'manual' !== $shortcode['user_upload_folders']) {
            return false;
        }

        if (!empty($this->_user_folder_entry)) {
            return $this->_user_folder_entry;
        }

        $userfolder = get_user_option('lets_box_linkedto');
        if (is_array($userfolder) && isset($userfolder['foldertext'])) {
            $this->_user_folder_entry = $this->get_client()->get_entry($userfolder['folderid'], false);
        } else {
            $defaultuserfolder = get_site_option('lets_box_guestlinkedto');
            if (is_array($defaultuserfolder) && isset($defaultuserfolder['folderid'])) {
                $this->_user_folder_entry = $this->get_client()->get_entry($defaultuserfolder['folderid'], false);
            } else {
                if (is_user_logged_in()) {
                    $current_user = wp_get_current_user();
                    error_log('[WP Cloud Plugin message]: '.sprintf('Cannot find manual folder link for user: %s', $current_user->user_login));
                } else {
                    error_log('[WP Cloud Plugin message]: '.'Cannot find manual folder link for guest user');
                }

                exit(-1);
            }
        }

        return $this->_user_folder_entry;
    }

    public function manually_link_folder($user_id, $linkedto)
    {
        if ('GUEST' === $user_id) {
            $result = update_site_option('lets_box_guestlinkedto', $linkedto);
        } else {
            $result = update_user_option($user_id, 'lets_box_linkedto', $linkedto, false);
        }

        if (false !== $result) {
            exit('1');
        }
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

        $parent_folder_data = $this->get_client()->get_folder($shortcode['root'], false);

        // If root folder doesn't exists
        if (empty($parent_folder_data)) {
            return false;
        }
        $parent_folder = $parent_folder_data['folder'];

        // Create Folder structure if required (e.g. it contains a /)
        $subfolders = array_filter(explode('/', $userfoldername));
        $userfoldername = array_pop($subfolders);

        foreach ($subfolders as $subfolder) {
            $parent_folder = $this->get_client()->get_sub_folder_by_path($parent_folder->get_id(), $subfolder, true);
        }

        // First try to find the User Folder in Cache
        $userfolder = $this->get_client()->get_cache()->get_node_by_name($userfoldername, $parent_folder);

        /* If User Folder isn't in cache yet,
         * Update the parent folder to make sure the latest version is loaded */
        if (false === $userfolder) {
            $this->get_client()->get_cache()->pull_for_changes(true, -1);
            $userfolder = $this->get_client()->get_cache()->get_node_by_name($userfoldername, $parent_folder);
        }

        // If User Folder still isn't found, create new folder in the Cloud
        if (false === $userfolder) {
            if (empty($shortcode['user_template_dir'])) {
                try {
                    $api_entry = $this->get_app()->get_client()->createNewBoxFolder($userfoldername, $parent_folder->get_id());

                    // Wait a moment in case many folders are created at once
                    usleep($mswaitaftercreation);
                } catch (\Exception $ex) {
                    error_log('[WP Cloud Plugin message]: '.sprintf('Failed to add user folder: %s', $ex->getMessage()));

                    return new \WP_Error('broke', esc_html__('Failed to add user folder', 'wpcloudplugins'));
                }
                // Add new file to our Cache
                $newentry = new Entry($api_entry);
                $userfolder = $this->get_client()->get_cache()->add_to_cache($newentry);
                $this->get_client()->get_cache()->update_cache();

                do_action('letsbox_log_event', 'letsbox_created_entry', $userfolder);
            } else {
                // 3: Get the Template folder
                $cached_template_folder = $this->get_client()->get_folder($shortcode['user_template_dir'], false);

                // 4: Make sure that the Template folder can be used
                if (false === $cached_template_folder || false === $cached_template_folder['folder'] || false === $cached_template_folder['folder']->has_children()) {
                    error_log('[WP Cloud Plugin message]: '.'Failed to add user folder as the template folder does not exist: %s');

                    return new \WP_Error('broke', esc_html__('Failed to add user folder', 'wpcloudplugins'));
                }

                // Copy the contents of the Template Folder into the User Folder
                try {
                    $api_entry = $this->get_app()->get_client()->copyBoxFolder($cached_template_folder['folder']->get_id(), $parent_folder->get_id(), $userfoldername, false);
                    $newentry = new Entry($api_entry);
                    $userfolder = $this->get_client()->get_cache()->add_to_cache($newentry);
                    $this->get_client()->get_cache()->update_cache();

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
        $letsboxlists = $this->get_processor()->get_shortcodes()->get_all_shortcodes();

        foreach ($letsboxlists as $list) {
            if (!isset($list['user_upload_folders']) || 'auto' !== $list['user_upload_folders']) {
                continue;
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
                $this->_user_name_template = $this->get_processor()->get_setting('userfolder_name');
            }

            $new_userfoldersname = $this->get_user_name_template($new_user);

            $result = $this->create_user_folder($new_userfoldersname, $list);

            do_action('letsbox_after_private_folder_added', $result, $this->_processor);
        }
    }

    public function create_user_folders($users = [])
    {
        if (0 === count($users)) {
            return;
        }

        foreach ($users as $user) {
            $userfoldersname = $this->get_user_name_template($user);
            $result = $this->create_user_folder($userfoldersname, $this->get_processor()->get_shortcode(), 50);

            do_action('letsbox_after_private_folder_added', $result, $this->_processor);
        }

        $this->get_client()->get_cache()->pull_for_changes(true);
    }

    public function remove_user_folder($user_id)
    {
        $deleted_user = get_user_by('id', $user_id);

        $letsboxlists = $this->get_processor()->get_shortcodes()->get_all_shortcodes();
        $update_folders = [];

        foreach ($letsboxlists as $list) {
            if (!isset($list['user_upload_folders']) || 'auto' !== $list['user_upload_folders']) {
                continue;
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
                $this->_user_name_template = $this->get_processor()->get_setting('userfolder_name');
            }

            $userfoldername = $this->get_user_name_template($deleted_user);

            // 2: try to find the User Folder in Cache
            $userfolder = $this->get_client()->get_cache()->get_node_by_name($userfoldername, $list['root']);
            if (!empty($userfolder)) {
                try {
                    $box_entry = new \Box\Model\Folder\Folder();
                    $box_entry->setId($userfolder->get_id());
                    $deleted_entry = $this->get_app()->get_client()->deleteEntry($box_entry);
                    $update_folders[$list['root']] = true;
                } catch (\Exception $ex) {
                    error_log('[WP Cloud Plugin message]: '.sprintf('Failed to remove user folder: %s', $ex->getMessage()));

                    continue;
                }
            } else {
                // Find all items containing query
                try {
                    $found_entries = $this->get_app()->get_client()->search(stripslashes($userfoldername), $list['root'], null, 'folder', 'name');
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
                        $deleted_entry = $this->get_app()->get_client()->deleteEntry($box_entry);
                    } catch (\Exception $ex) {
                        error_log('[WP Cloud Plugin message]: '.sprintf('Failed to remove user folder: %s', $ex->getMessage()));

                        continue;
                    }
                }

                $update_folders[$list['root']] = true;
            }
        }

        $this->get_client()->get_cache()->pull_for_changes(true);

        return true;
    }

    public function update_user_folder($user_id, $old_user)
    {
        $updated_user = get_user_by('id', $user_id);

        $letsboxlists = $this->get_processor()->get_shortcodes()->get_all_shortcodes();
        $update_folders = [];

        $this->get_client()->get_cache()->pull_for_changes(true);

        foreach ($letsboxlists as $list) {
            if (!isset($list['user_upload_folders']) || 'auto' !== $list['user_upload_folders']) {
                continue;
            }

            if (false === Helpers::check_user_role($list['view_role'], $updated_user)) {
                continue; // Skip shortcodes that aren't accessible for user
            }

            if (!empty($list['user_folder_name_template'])) {
                $this->_user_name_template = $list['user_folder_name_template'];
            } else {
                $this->_user_name_template = $this->get_processor()->get_setting('userfolder_name');
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
            $userfolder = $this->get_client()->get_cache()->get_node_by_name($old_userfoldersname, $list['root']);
            if (!empty($userfolder)) {
                try {
                    $box_entry->setId($userfolder->get_id());
                    $api_entry = $this->get_app()->get_client()->updateEntry($box_entry, $updaterequest);
                } catch (\Exception $ex) {
                    error_log('[WP Cloud Plugin message]: '.sprintf('Failed to update user folder: %s', $ex->getMessage()));

                    continue;
                }
            } else {
                // Find all items containing query

                try {
                    $found_entries = $this->get_app()->get_client()->search(stripslashes($old_userfoldersname), $list['root'], null, 'folder', 'name');
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
                        $api_entry = $this->get_app()->get_client()->updateEntry($box_entry, $updaterequest);
                    } catch (\Exception $ex) {
                        error_log('[WP Cloud Plugin message]: '.sprintf('Failed to update user folder: %s', $ex->getMessage()));

                        continue;
                    }
                }
            }
        }

        $this->get_client()->get_cache()->pull_for_changes(true);

        return true;
    }

    public function get_user_name_template($user_data)
    {
        $user_folder_name = Helpers::apply_placeholders($this->_user_name_template, $this->get_processor(), ['user_data' => $user_data]);

        $user_folder_name = ltrim(Helpers::clean_folder_path($user_folder_name), '/');

        return apply_filters('letsbox_private_folder_name', $user_folder_name, $this->get_processor());
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

        return apply_filters('letsbox_private_folder_name_guests', esc_html__('Guests', 'wpcloudplugins').' - '.$user_folder_name, $this->get_processor());
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

    /**
     * @return \TheLion\LetsBox\Processor
     */
    public function get_processor()
    {
        return $this->_processor;
    }

    /**
     * @return \TheLion\LetsBox\App
     */
    public function get_app()
    {
        return $this->get_processor()->get_app();
    }

    /**
     * @return \TheLion\LetsBox\Client
     */
    public function get_client()
    {
        return $this->get_processor()->get_client();
    }
}
