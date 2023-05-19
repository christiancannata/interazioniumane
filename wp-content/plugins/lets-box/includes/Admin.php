<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2022, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\LetsBox;

class Admin
{
    public $settings;
    private $settings_key = 'lets_box_settings';
    private $plugin_options_key = 'LetsBox_settings';
    private $plugin_network_options_key = 'LetsBox_network_settings';
    private $plugin_id = 8204640;

    /**
     * Construct the plugin object.
     */
    public function __construct()
    {
        // Check if plugin can be used
        if (false === Core::can_run_plugin()) {
            add_action('admin_notices', [$this, 'get_admin_notice']);

            return;
        }

        // Init
        add_action('init', [$this, 'load_settings']);
        add_action('init', [$this, 'check_for_updates']);

        // Add menu's
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('network_admin_menu', [$this, 'add_admin_network_menu']);

        // Ajax Calls
        add_action('wp_ajax_letsbox-save-setting', [$this, 'save_setting']);
        add_action('wp_ajax_letsbox-check-account', [$this, 'check_account']);
        add_action('wp_ajax_letsbox-reset-cache', [$this, 'start_process']);
        add_action('wp_ajax_letsbox-factory-reset', [$this, 'start_process']);
        add_action('wp_ajax_letsbox-reset-statistics', [$this, 'start_process']);
        add_action('wp_ajax_letsbox-backup', [$this, 'start_process']);
        add_action('wp_ajax_letsbox-revoke', [$this, 'start_process']);

        // Notices
        add_action('admin_notices', [$this, 'get_admin_notice_not_authorized']);
        add_action('admin_notices', [$this, 'get_admin_notice_not_activated']);

        // Add custom Update messages in plugin dashboard
        add_action('in_plugin_update_message-'.LETSBOX_SLUG, [$this, 'in_plugin_update_message'], 10, 2);

        // Authorization call Back
        add_action('admin_init', [$this, 'is_doing_oauth']);
    }

    public function start_process()
    {
        if (!isset($_REQUEST['action'])) {
            return false;
        }

        switch ($_REQUEST['action']) {
            case 'letsbox-reset-cache':
            case 'letsbox-backup':
            case 'letsbox-factory-reset':
            case 'letsbox-reset-statistics':
            case 'letsbox-revoke':
                check_ajax_referer('letsbox-admin-action', false, true);

                if (false === Helpers::check_user_role($this->settings['permissions_edit_settings'])) {
                    exit(1);
                }

                switch ($_REQUEST['action']) {
                    case 'letsbox-revoke':
                        require_once ABSPATH.'wp-includes/pluggable.php';
                        Processor::instance()->start_process();

                        break;

                    case 'letsbox-factory-reset':
                        Core::do_factory_reset();

                        break;

                    case 'letsbox-reset-cache':
                        Processor::instance()->reset_complete_cache(true, !empty($_REQUEST['thumbnails']));

                        break;

                    case 'letsbox-reset-statistics':
                        Events::truncate_database();

                        break;

                    case 'letsbox-backup':
                        if ('export' === $_REQUEST['type']) {
                            Backup::do_export();
                        }

                        if ('import' === $_REQUEST['type']) {
                            Backup::do_import();
                        }

                        exit(2);
                }

                exit(1);
        }

        exit;
    }

    public function is_doing_oauth()
    {
        if (!isset($_REQUEST['action']) || 'letsbox_authorization' !== $_REQUEST['action']) {
            return false;
        }
        if (Helpers::check_user_role($this->settings['permissions_edit_settings'])) {
            App::instance()->process_authorization();
        }
    }

    // Add custom Update messages in plugin dashboard

    public function in_plugin_update_message($data, $response)
    {
        if (isset($data['upgrade_notice'])) {
            printf(
                '<br /><br /><span style="display:inline-block;background-color: #590e54; padding: 10px; color: white;"><span class="dashicons dashicons-warning"></span>&nbsp;<strong>UPGRADE NOTICE</strong> <br /><br />%s</span><br /><br />',
                $data['upgrade_notice']
            );
        }
    }

    /**
     * add a menu.
     */
    public function add_admin_menu()
    {
        // Add a page to manage this plugin's settings
        $menuadded = false;

        if (Helpers::check_user_role($this->settings['permissions_edit_settings'])) {
            add_menu_page('Lets-Box', 'Lets-Box', 'read', $this->plugin_options_key, [$this, 'load_settings_page'], plugin_dir_url(__FILE__).'../css/images/box_logo_small.png');
            $menuadded = true;
            add_submenu_page($this->plugin_options_key, 'Lets-Box - '.esc_html__('Settings'), esc_html__('Settings'), 'read', $this->plugin_options_key, [$this, 'load_settings_page']);
        }

        if (false === License::is_valid()) {
            return;
        }

        if (Helpers::check_user_role($this->settings['permissions_see_dashboard']) && ('Yes' === $this->settings['log_events'])) {
            if (!$menuadded) {
                add_menu_page('Lets-Box', 'Lets-Box', 'read', $this->plugin_options_key, [$this, 'load_dashboard_page'], plugin_dir_url(__FILE__).'../css/images/box_logo_small.png');
                add_submenu_page($this->plugin_options_key, esc_html__('Reports', 'wpcloudplugins'), esc_html__('Reports', 'wpcloudplugins'), 'read', $this->plugin_options_key, [$this, 'load_dashboard_page']);
                $menuadded = true;
            } else {
                add_submenu_page($this->plugin_options_key, esc_html__('Reports', 'wpcloudplugins'), esc_html__('Reports', 'wpcloudplugins'), 'read', $this->plugin_options_key.'_dashboard', [$this, 'load_dashboard_page']);
            }
        }

        if (Helpers::check_user_role($this->settings['permissions_add_shortcodes'])) {
            if (!$menuadded) {
                add_menu_page('Lets-Box', 'Lets-Box', 'read', $this->plugin_options_key, [$this, 'load_shortcodebuilder_page'], plugin_dir_url(__FILE__).'../css/images/box_logo_small.png');
                add_submenu_page($this->plugin_options_key, esc_html__('Shortcode Builder', 'wpcloudplugins'), esc_html__('Shortcode Builder', 'wpcloudplugins'), 'read', $this->plugin_options_key, [$this, 'load_shortcodebuilder_page']);
                $menuadded = true;
            } else {
                add_submenu_page($this->plugin_options_key, esc_html__('Shortcode Builder', 'wpcloudplugins'), esc_html__('Shortcode Builder', 'wpcloudplugins'), 'read', $this->plugin_options_key.'_shortcodebuilder', [$this, 'load_shortcodebuilder_page']);
            }
        }

        if (Helpers::check_user_role($this->settings['permissions_link_users'])) {
            if (!$menuadded) {
                add_menu_page('Lets-Box', 'Lets-Box', 'read', $this->plugin_options_key, [$this, 'load_linkusers_page'], plugin_dir_url(__FILE__).'../css/images/box_logo_small.png');
                add_submenu_page($this->plugin_options_key, esc_html__('Link Private Folders', 'wpcloudplugins'), esc_html__('Link Private Folders', 'wpcloudplugins'), 'read', $this->plugin_options_key, [$this, 'load_linkusers_page']);
                $menuadded = true;
            } else {
                add_submenu_page($this->plugin_options_key, esc_html__('Link Private Folders', 'wpcloudplugins'), esc_html__('Link Private Folders', 'wpcloudplugins'), 'read', $this->plugin_options_key.'_linkusers', [$this, 'load_linkusers_page']);
            }
        }

        if (Helpers::check_user_role($this->settings['permissions_see_filebrowser'])) {
            if (!$menuadded) {
                add_menu_page('Lets-Box', 'Lets-Box', 'read', $this->plugin_options_key, [$this, 'load_filebrowser_page'], plugin_dir_url(__FILE__).'../css/images/box_logo_small.png');
                add_submenu_page($this->plugin_options_key, esc_html__('File Browser', 'wpcloudplugins'), esc_html__('File Browser', 'wpcloudplugins'), 'read', $this->plugin_options_key, [$this, 'load_filebrowser_page']);
                $menuadded = true;
            } else {
                add_submenu_page($this->plugin_options_key, esc_html__('File Browser', 'wpcloudplugins'), esc_html__('File Browser', 'wpcloudplugins'), 'read', $this->plugin_options_key.'_filebrowser', [$this, 'load_filebrowser_page']);
            }
        }
    }

    public function add_admin_network_menu()
    {
        if (!is_plugin_active_for_network(LETSBOX_SLUG)) {
            return;
        }

        add_menu_page('Lets-Box', 'Lets-Box', 'manage_options', $this->plugin_network_options_key, [$this, 'load_settings_network_page'], plugin_dir_url(__FILE__).'../css/images/box_logo_small.png');

        add_submenu_page($this->plugin_network_options_key, 'Lets-Box - '.esc_html__('Settings'), esc_html__('Settings'), 'read', $this->plugin_network_options_key, [$this, 'load_settings_network_page']);

        if (Processor::instance()->is_network_authorized()) {
            add_submenu_page($this->plugin_network_options_key, esc_html__('File Browser', 'wpcloudplugins'), esc_html__('File Browser', 'wpcloudplugins'), 'read', $this->plugin_network_options_key.'_filebrowser', [$this, 'load_filebrowser_page']);
        }
    }

    public function load_settings()
    {
        $this->settings = (array) get_option($this->settings_key);

        $update = false;
        if (!isset($this->settings['box_app_client_id'])) {
            $this->settings['box_app_client_id'] = '';
            $this->settings['box_app_client_secret'] = '';
            $update = true;
        }

        if ($update) {
            update_option($this->settings_key, $this->settings);
        }

        if (Processor::instance()->is_network_authorized()) {
            $this->settings = array_merge($this->settings, get_site_option('letsbox_network_settings', []));
        }
    }

    public function save_setting()
    {
        // Check AJAX call
        check_ajax_referer('letsbox-admin-action');

        // Get setting data
        $setting_key = $_REQUEST['key'];
        $new_value = wp_unslash($_REQUEST['value']);
        $is_network_setting = (true == $_REQUEST['network']);

        if ($is_network_setting) {
            $current_settings = $old_settings = get_site_option('letsbox_network_settings', []);
            $old_value = $old_settings[$setting_key] ?? null;
        } else {
            $old_value = Core::get_setting($setting_key);
            $current_settings = $old_settings = $this->settings;
        }

        // Process setting value
        if ('true' === $new_value) {
            $new_value = 'Yes';
        } elseif ('false' === $new_value) {
            $new_value = 'No';
        }

        if (is_string($new_value)) {
            $new_value = trim($new_value);
        }

        // Store the ID of fields using tagify data
        if (is_string($new_value) && false !== strpos($new_value, '[{')) {
            $new_value = $this->_format_tagify_data($new_value);
        }

        if ('box_app_own' === $setting_key && false === $new_value) {
            $current_settings['box_app_client_id'] = '';
            $current_settings['box_app_client_secret'] = '';
            $return['box_app_client_id'] = '';
            $return['box_app_client_secret'] = '';
        }

        if ('webhook_active' === $setting_key && false === $new_value) {
            $current_settings['webhook_endpoint_url'] = '';
            $return['webhook_endpoint_url'] = '';
        }

        if ('icon_set' === $setting_key) {
            if ($new_value !== $old_value) {
                Processor::reset_complete_cache();
            }

            $new_value = rtrim($new_value, '/').'/';
        }

        if ('network_wide' === $setting_key) {
            $return['reload'] = true;
        }

        if (1 == preg_match('/(.*?)\[(.*?)\]/', $setting_key, $setting_keys)) {
            $current_settings[$setting_keys[1]][$setting_keys[2]] = $new_value;
        } else {
            $current_settings[$setting_key] = $new_value;
        }

        // Save new settings
        if ($new_value === $old_value || empty($new_value) && empty($old_value)) {
            // do nothing
            $return[$setting_key] = $new_value;
            echo json_encode($return, JSON_PRETTY_PRINT);

            exit;
        }

        if ($is_network_setting) {
            if ('purchase_code' === $setting_key) {
                $saved = update_site_option('letsbox_purchaseid', $new_value);
            } else {
                $saved = update_site_option('letsbox_network_settings', $current_settings);
            }
        } else {
            $saved = update_option($this->settings_key, $current_settings);
        }

        if ($saved) {
            $this->load_settings();
            $return[$setting_key] = $new_value;
        } else {
            $this->settings = $old_settings;

            http_response_code(500);

            exit('-1');
        }

        if (false === $is_network_setting) {
            // Update Cron Job settings
            if ($this->settings['event_summary'] !== $old_settings['event_summary'] || $this->settings['event_summary_period'] !== $old_settings['event_summary_period']) {
                $summary_cron_job = wp_next_scheduled('letsbox_send_event_summary');
                if (false !== $summary_cron_job) {
                    wp_unschedule_event($summary_cron_job, 'letsbox_send_event_summary');
                }
            }
            // If needed, a new cron job will be set when the plugin initiates again

            // Keep account data
            if (!isset($this->settings['accounts'])) {
                $this->settings['accounts'] = $old_settings['accounts'] ?? [];
            }
        }

        echo json_encode($return, JSON_PRETTY_PRINT);

        exit;
    }

    public function load_settings_page()
    {
        if (!Helpers::check_user_role($this->settings['permissions_edit_settings'])) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'wpcloudplugins'));
        }

        Core::instance()->load_scripts();
        wp_enqueue_script('LetsBox.AdminSettings');

        Core::instance()->load_styles();
        wp_enqueue_style('WPCloudPlugins.AdminUI');

        wp_enqueue_media();

        // Build Whitelist for permission selection
        $vars = [
            'whitelist' => json_encode(Helpers::get_all_users_and_roles()),
            'ajax_url' => LETSBOX_ADMIN_URL,
        ];

        wp_localize_script('LetsBox.AdminUI', 'WPCloudplugin_AdminUI_vars', $vars);

        include sprintf('%s/templates/admin/settings.php', LETSBOX_ROOTDIR);
    }

    public function load_settings_network_page()
    {
        Core::instance()->load_scripts();
        wp_enqueue_script('LetsBox.AdminSettings');

        Core::instance()->load_styles();
        wp_enqueue_style('WPCloudPlugins.AdminUI');

        // Build Whitelist for permission selection
        $vars = [
            'whitelist' => json_encode(Helpers::get_all_users_and_roles()),
            'ajax_url' => LETSBOX_ADMIN_URL,
        ];

        wp_localize_script('LetsBox.AdminUI', 'WPCloudplugin_AdminUI_vars', $vars);

        include sprintf('%s/templates/admin/settings_network.php', LETSBOX_ROOTDIR);
    }

    public function load_filebrowser_page()
    {
        if (!Helpers::check_user_role($this->settings['permissions_see_filebrowser'])) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'wpcloudplugins'));
        }

        Core::instance()->load_scripts();
        wp_enqueue_script('LetsBox.AdminUI');

        Core::instance()->load_styles();
        wp_enqueue_style('WPCloudPlugins.AdminUI');

        include sprintf('%s/templates/admin/file_browser.php', LETSBOX_ROOTDIR);
    }

    public function load_linkusers_page()
    {
        if (!Helpers::check_user_role($this->settings['permissions_link_users'])) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'wpcloudplugins'));
        }

        LinkUsers::render();
    }

    public function load_shortcodebuilder_page()
    {
        if (!Helpers::check_user_role($this->settings['permissions_add_shortcodes'])) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'wpcloudplugins'));
        }

        Core::instance()->load_scripts();
        wp_enqueue_script('LetsBox.AdminUI');

        Core::instance()->load_styles();
        wp_enqueue_style('WPCloudPlugins.AdminUI');

        include sprintf('%s/templates/admin/shortcode_standalone.php', LETSBOX_ROOTDIR);
    }

    public function load_dashboard_page()
    {
        if (!Helpers::check_user_role($this->settings['permissions_see_dashboard'])) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'wpcloudplugins'));
        }

        Core::instance()->load_scripts();
        wp_enqueue_script('LetsBox.Dashboard');

        Core::instance()->load_styles();
        wp_enqueue_style('WPCloudPlugins.AdminUI');
        wp_dequeue_style('LetsBox');

        include sprintf('%s/templates/admin/event_dashboard.php', LETSBOX_ROOTDIR);
    }

    public function check_account()
    {
        // Check AJAX call
        check_ajax_referer('letsbox-admin-action');

        // Get Account
        $account_id = \sanitize_key($_POST['account_id']);
        $account = Accounts::instance()->get_account_by_id($account_id);

        // Get App
        $app = App::instance();

        // Check Authorization
        $has_token = true === $account->get_authorization()->has_access_token();
        $transient_name = 'letsbox_'.$account->get_id().'_is_authorized';
        $is_authorized = !empty(get_transient($transient_name));

        // Set return data
        $return = [
            'id' => $account->get_id(),
            'email' => $account->get_email(),
            'image' => $account->get_image(),
            'has_token' => $has_token,
            'is_authorized' => $is_authorized,
            'quota_used' => '',
            'quota_total' => '',
            'quota_used_percentage' => '',
            'auth_url' => $app->get_auth_url(),
            'error_message' => '',
            'error_details' => '',
        ];

        // Check if authorization token is available
        if (false === $has_token) {
            $return['error_message'] = esc_html__('Account is not linked to the plugin anymore.', 'wpcloudplugins').' '.esc_html__('Please re-authorize!', 'wpcloudplugins');
            echo \json_encode($return);

            exit;
        }

        // Re-Check authorization if needed
        if (false === $is_authorized) {
            try {
                App::set_current_account($account);
                API::get_account_info();
                set_transient($transient_name, true, 5 * MINUTE_IN_SECONDS);
                $return['is_authorized'] = true;
            } catch (\Exception $ex) {
                App::get_current_account()->get_authorization()->set_is_valid(false);
                set_transient($transient_name, false, 5 * MINUTE_IN_SECONDS);
                $return['error_message'] = esc_html__('Account is not linked to the plugin anymore.', 'wpcloudplugins').' '.esc_html__('Please refresh the authorization or remove the account from the list.', 'wpcloudplugins');

                if ($app->has_plugin_own_app()) {
                    $return['error_message'] .= ' '.esc_html__('If the problem persists, fall back to the default App via the settings on the Advanced tab.', 'wpcloudplugins');
                }

                $return['error_details'] = '<pre>Error Details: '.$ex->getMessage().'</pre>';

                echo \json_encode($return);

                exit;
            }
        }

        try {
            $storageinfo = $account->get_storage_info();
            $return['quota_total'] = $storageinfo->get_quota_total();
            $return['quota_used'] = $storageinfo->get_quota_used();
            $return['quota_used_percentage'] = $storageinfo->get_quota_used_percentage_used();
        } catch (\Exception $ex) {
            $return['error_message'] = esc_html__('Cannot get account storage information.', 'wpcloudplugins');
            $return['error_details'] = '<p>Error Details:</p><pre>'.$ex->getMessage().'</pre>';
        }

        echo \json_encode($return);

        exit;
    }

    public function get_admin_notice($force = false)
    {
        // Check if cURL is present and its functions can be used
        $disabled_php_functions = explode(',', ini_get('disable_functions'));

        if (version_compare(PHP_VERSION, '7.4') < 0) {
            echo '<div id="message" class="error"><p><strong>Lets-Box - Error: </strong>'.sprintf(esc_html__('You need at least PHP %s if you want to use this plugin', 'wpcloudplugins'), '7.4').'. '.
            esc_html__('You are using:', 'wpcloudplugins').' <u>'.phpversion().'</u></p></div>';
        } elseif (!function_exists('curl_init') || !function_exists('curl_exec')) {
            echo '<div id="message" class="error"><p><strong>Lets-Box - Error: </strong>'.
            esc_html__("We are not able to connect to the API as you don't have the cURL PHP extension installed", 'wpcloudplugins').'. '.
            esc_html__('Please enable or install the cURL extension on your server', 'wpcloudplugins').'. '.
            '</p></div>';
        } elseif (in_array('curl_init', $disabled_php_functions) || in_array('curl_exec', $disabled_php_functions)) {
            echo '<div id="message" class="error"><p><strong>Lets-Box - Error: </strong>'.
            esc_html__('We are not able to connect to the API as cURL PHP functions curl_init and/or curl_exec are on the list of disabled functions in your PHP configuration.', 'wpcloudplugins').' '.
            esc_html__('To resolve this, please remove those functions from the "disabled_functions" PHP configuration.', 'wpcloudplugins').' '.
            '</p></div>';
        } elseif (class_exists('Box_Client') && (!method_exists('Box_Client', 'getLibraryVersion'))) {
            echo '<div id="message" class="error"><p><strong>Lets-Box - Error: </strong>'.
            esc_html__('We are not able to connect to the API as the plugin is interfering with an other plugin', 'wpcloudplugins').'. <br/><br/>'.
            esc_html__("The other plugin is using an old version of the Api-PHP-client that isn't capable of running multiple configurations", 'wpcloudplugins').'. '.
            esc_html__('Please disable this other plugin if you would like to use this plugin', 'wpcloudplugins').'. '.
            esc_html__("If you would like to use both plugins, ask the developer to update it's code", 'wpcloudplugins').'. '.
            '</p></div>';
        } elseif (!file_exists(LETSBOX_CACHEDIR) || !is_writable(LETSBOX_CACHEDIR) || (!file_exists(LETSBOX_CACHEDIR.'/.htaccess') && false === strpos(strtolower($_SERVER['SERVER_SOFTWARE']), 'microsoft-iis'))) {
            echo '<div id="message" class="error"><p><strong>Lets-Box - Error: </strong>'.sprintf(esc_html__('Cannot create the cache directory %s, or it is not writable', 'wpcloudplugins'), '<code>'.LETSBOX_CACHEDIR.'</code>').'. '.
            sprintf(esc_html__('Please check if the directory exists on your server and has %s writing permissions %s', 'wpcloudplugins'), '<a href="https://codex.wordpress.org/Changing_File_Permissions" target="_blank">', '</a>').'</p></div>';
        } elseif (!defined('CURL_SSLVERSION_TLSv1_1') && !defined('CURL_SSLVERSION_TLSv1_2')) {
            echo '<div id="message" class="error"><p><strong>Lets-Box - Error: </strong>'.esc_html__('The cURL library of your server does not support TLS1.1 or TLS1.2 (CURL_SSLVERSION_TLSv1_1 | CURL_SSLVERSION_TLSv1_2). This is required by the API to create a secure connection. Please contact your webhost and ask them to upgrade the curl library and/or enable TLS1.1 support or higher for this library', 'wpcloudplugins').'. '.
            '</p></div>';
        }
    }

    public function get_admin_notice_not_authorized()
    {
        global $pagenow;
        if ('index.php' == $pagenow || 'plugins.php' == $pagenow) {
            if (current_user_can('manage_options') || current_user_can('edit_theme_options')) {
                $location = get_admin_url(null, 'admin.php?page=LetsBox_settings');

                $accounts = Accounts::instance()->list_accounts();

                if (empty($accounts)) {
                    echo '<div id="message" class="error"><p><span class="dashicons dashicons-warning"></span>&nbsp;<strong>Lets-Box: </strong>'.sprintf(esc_html__("The plugin isn't linked with a %s account. Authorize the plugin or disable it if is not used on the site.", 'wpcloudplugins'), 'Box').'</p>'.
                        "<p><a href='{$location}' class='button-primary'>❱❱❱ &nbsp;".esc_html__('Authorize the plugin!', 'wpcloudplugins').'</a></p></div>';

                    return;
                }

                $accounts_that_require_attention = [];
                foreach ($accounts as $account_id => $account) {
                    if (false === $account->get_authorization()->has_access_token() || (false !== wp_next_scheduled('letsbox_lost_authorisation_notification', ['account_id' => $account_id]))) {
                        $accounts_that_require_attention[] = $account->get_email();
                    }
                }

                if (!empty($accounts_that_require_attention)) {
                    echo '<div id="message" class="error"><p><span class="dashicons dashicons-warning"></span>&nbsp;<strong>Lets-Box: </strong>'.sprintf(esc_html__("The plugin isn't longer linked to the account(s): %s", 'wpcloudplugins'), '<strong>'.implode('</strong>, <strong>', $accounts_that_require_attention).'</strong>').'.</p>'.
                        "<p><a href='{$location}' class='button-primary'>❱❱❱ &nbsp;".esc_html__('Refresh the authorization!', 'wpcloudplugins').'</a></p></div>';
                }
            }
        }
    }

    public function get_admin_notice_not_activated()
    {
        global $pagenow;

        if ('index.php' != $pagenow && 'plugins.php' != $pagenow) {
            return;
        }

        if (License::is_valid()) {
            return;
        }

        if (current_user_can('manage_options') || current_user_can('edit_theme_options')) {
            $location = get_admin_url(null, 'admin.php?page=LetsBox_settings'); ?>
                <div id="message" class="error">
                    <img src="<?php echo LETSBOX_ROOTPATH; ?>/css/images/wpcp-logo-dark.svg" height="84" width="84" class="alignleft" style="padding: 20px 20px 20px 10px;">
                    <h3>Lets-Box: <?php esc_html_e('Inactive License', 'wpcloudplugins'); ?></h3>
                    <p><?php
                                    esc_html_e('The plugin is not yet activated. This means you’re missing out on updates and support! Please activate the plugin in order to start using the plugin, or disable the plugin.', 'wpcloudplugins'); ?>
                    </p>
                    <p>
                        <a href='<?php echo $location; ?>' class='button-primary'>❱❱❱ &nbsp;<?php esc_html_e('Activate the plugin!', 'wpcloudplugins'); ?></a>
                        &nbsp;
                        <a href="https://1.envato.market/a4ggZ" target="_blank" class="button button-secondary"><?php esc_html_e('Buy License', 'wpcloudplugins'); ?></a>
                    </p>
                </div>
                <?php

        }
    }

    public function check_for_updates()
    {
        require_once LETSBOX_ROOTDIR.'/vendors/plugin-update-checker/plugin-update-checker.php';
        \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker('https://www.wpcloudplugins.com/updates/?action=get_metadata&slug=lets-box&purchase_code='.License::get().'&plugin_id='.$this->plugin_id, plugin_dir_path(__DIR__).'/lets-box.php');
    }

    public function get_system_information()
    {
        // Figure out cURL version, if installed.
        $curl_version = '';
        if (function_exists('curl_version')) {
            $curl_version = curl_version();
            $curl_version = $curl_version['version'].', '.$curl_version['ssl_version'];
        } elseif (extension_loaded('curl')) {
            $curl_version = esc_html__('cURL installed but unable to retrieve version.', 'wpcloudplugins');
        }

        // WP memory limit.
        $wp_memory_limit = Helpers::return_bytes(WP_MEMORY_LIMIT);
        if (function_exists('memory_get_usage')) {
            $wp_memory_limit = max($wp_memory_limit, Helpers::return_bytes(@ini_get('memory_limit')));
        }

        // Return all environment info. Described by JSON Schema.
        $environment = [
            'home_url' => get_option('home'),
            'site_url' => get_option('siteurl'),
            'version' => LETSBOX_VERSION,
            'cache_directory' => LETSBOX_CACHEDIR,
            'cache_directory_writable' => (bool) @fopen(LETSBOX_CACHEDIR.'/test-cache.log', 'a'),
            'wp_version' => get_bloginfo('version'),
            'wp_multisite' => is_multisite(),
            'wp_memory_limit' => $wp_memory_limit,
            'wp_debug_mode' => (defined('WP_DEBUG') && WP_DEBUG),
            'wp_cron' => !(defined('DISABLE_WP_CRON') && DISABLE_WP_CRON),
            'language' => get_locale(),
            'external_object_cache' => wp_using_ext_object_cache(),
            'server_info' => isset($_SERVER['SERVER_SOFTWARE']) ? wp_unslash($_SERVER['SERVER_SOFTWARE']) : '',
            'php_version' => phpversion(),
            'php_post_max_size' => Helpers::return_bytes(ini_get('post_max_size')),
            'php_max_execution_time' => ini_get('max_execution_time'),
            'php_max_input_vars' => ini_get('max_input_vars'),
            'curl_version' => $curl_version,
            'max_upload_size' => wp_max_upload_size(),
            'default_timezone' => date_default_timezone_get(),
            'curl_enabled' => (function_exists('curl_init') && function_exists('curl_exec')),
            'allow_url_fopen' => ini_get('allow_url_fopen'),
            'gzip_compression_enabled' => extension_loaded('zlib'),
            'mbstring_enabled' => extension_loaded('mbstring'),
            'flock' => (false === strpos(ini_get('disable_functions'), 'flock')),
            'secure_connection' => is_ssl(),
            'openssl_encrypt' => (function_exists('openssl_encrypt') && in_array('aes-256-cbc', openssl_get_cipher_methods())),
            'hide_errors' => !(defined('WP_DEBUG') && defined('WP_DEBUG_DISPLAY') && WP_DEBUG && WP_DEBUG_DISPLAY) || 0 === intval(ini_get('display_errors')),
            'gravity_forms' => class_exists('GFForms'),
            'formidableforms' => class_exists('FrmAppHelper'),
            'gravity_pdf' => class_exists('GFPDF_Core'),
            'gravity_wpdatatables' => class_exists('WPDataTable'),
            'elementor' => defined('ELEMENTOR_VERSION'),
            'wpforms' => defined('WPFORMS_VERSION'),
            'fluentforms' => defined('FLUENTFORM_VERSION'),
            'contact_form_7' => defined('WPCF7_PLUGIN'),
            'acf' => class_exists('ACF'),
            'beaver_builder' => class_exists('FLBuilder'),
            'divi_page_builder' => defined('ET_BUILDER_VERSION'),
            'woocommerce' => class_exists('WC_Integration'),
            'woocommerce_product_documents' => class_exists('WC_Product_Documents'),
        ];

        // Get Theme info
        $active_theme = wp_get_theme();

        // Get parent theme info if this theme is a child theme, otherwise
        // pass empty info in the response.
        if (is_child_theme()) {
            $parent_theme = wp_get_theme($active_theme->template);
            $parent_theme_info = [
                'parent_name' => $parent_theme->name,
                'parent_version' => $parent_theme->version,
                'parent_author_url' => $parent_theme->{'Author URI'},
            ];
        } else {
            $parent_theme_info = [
                'parent_name' => '',
                'parent_version' => '',
                'parent_version_latest' => '',
                'parent_author_url' => '',
            ];
        }

        $active_theme_info = [
            'name' => $active_theme->name,
            'version' => $active_theme->version,
            'author_url' => esc_url_raw($active_theme->{'Author URI'}),
            'is_child_theme' => is_child_theme(),
        ];

        $theme = array_merge($active_theme_info, $parent_theme_info);

        // Get Active plugins
        require_once ABSPATH.'wp-admin/includes/plugin.php';

        if (!function_exists('get_plugin_data')) {
            return [];
        }

        $active_plugins = (array) get_option('active_plugins', []);
        if (is_multisite()) {
            $network_activated_plugins = array_keys(get_site_option('active_sitewide_plugins', []));
            $active_plugins = array_merge($active_plugins, $network_activated_plugins);
        }

        $active_plugins_data = [];

        foreach ($active_plugins as $plugin) {
            $data = get_plugin_data(WP_PLUGIN_DIR.'/'.$plugin);
            $active_plugins_data[] = [
                'plugin' => $plugin,
                'name' => $data['Name'],
                'version' => $data['Version'],
                'url' => $data['PluginURI'],
                'author_name' => $data['AuthorName'],
                'author_url' => esc_url_raw($data['AuthorURI']),
                'network_activated' => $data['Network'],
            ];
        }

        include sprintf('%s/templates/admin/system_information.php', LETSBOX_ROOTDIR);
    }

    private function _format_tagify_data($data, $field = 'id')
    {
        if (is_array($data)) {
            return $data;
        }

        $data_obj = json_decode($data);

        if (null === $data_obj) {
            return $data;
        }

        $new_data = [];

        foreach ($data_obj as $value) {
            $new_data[] = $value->{$field};
        }

        return $new_data;
    }
}
