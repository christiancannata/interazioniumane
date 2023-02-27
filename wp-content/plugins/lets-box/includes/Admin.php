<?php

namespace TheLion\LetsBox;

class Admin
{
    /**
     * Holds the values to be used in the fields callbacks.
     */
    private $settings_key = 'lets_box_settings';
    private $plugin_options_key = 'LetsBox_settings';
    private $plugin_network_options_key = 'LetsBox_network_settings';
    private $canconnect = true;
    private $plugin_id = 8204640;
    private $networksettingspage;
    private $settingspage;
    private $filebrowserpage;
    private $shortcodebuilderpage;
    private $dashboardpage;
    private $userpage;

    /**
     * Construct the plugin object.
     */
    public function __construct(Main $main)
    {
        $this->_main = $main;

        // Check if plugin can be used
        if (false === $main->can_run_plugin()) {
            add_action('admin_notices', [$this, 'get_admin_notice']);

            return;
        }

        // Init
        add_action('init', [$this, 'load_settings']);
        add_action('init', [$this, 'check_for_updates']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'load_admin']);

        // Add menu's
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('network_admin_menu', [$this, 'add_admin_network_menu']);

        // Network save settings call
        add_action('network_admin_edit_'.$this->plugin_network_options_key, [$this, 'save_settings_network']);

        // Save settings call
        add_filter('pre_update_option_'.$this->settings_key, [$this, 'save_settings'], 10, 2);

        // Notices
        add_action('admin_notices', [$this, 'get_admin_notice_not_authorized']);
        add_action('admin_notices', [$this, 'get_admin_notice_not_activated']);

        // Add custom Update messages in plugin dashboard
        add_action('in_plugin_update_message-'.LETSBOX_SLUG, [$this, 'in_plugin_update_message'], 10, 2);

        // Authorization call Back
        add_action('admin_init', [$this, 'is_doing_oauth']);
    }

    public function is_doing_oauth()
    {
        if (!isset($_REQUEST['action']) || 'letsbox_authorization' !== $_REQUEST['action']) {
            return false;
        }
        if (Helpers::check_user_role($this->settings['permissions_edit_settings'])) {
            $this->get_app()->process_authorization();
        }
    }

    /**
     * @return \TheLion\LetsBox\Main
     */
    public function get_main()
    {
        return $this->_main;
    }

    /**
     * @return \TheLion\LetsBox\Processor
     */
    public function get_processor()
    {
        return $this->_main->get_processor();
    }

    /**
     * @return \TheLion\LetsBox\App
     */
    public function get_app()
    {
        return $this->get_processor()->get_app();
    }

    // Add custom Update messages in plugin dashboard

    public function in_plugin_update_message($data, $response)
    {
        if (isset($data['upgrade_notice'])) {
            printf(
                '<br /><br /><span style="display:inline-block;background-color: #522058; padding: 10px; color: white;"><span class="dashicons dashicons-warning"></span>&nbsp;<strong>UPGRADE NOTICE</strong> <br /><br />%s</span><br /><br />',
                $data['upgrade_notice']
            );
        }
    }

    public function load_admin($hook)
    {
        if ($hook == $this->networksettingspage || $hook == $this->filebrowserpage || $hook == $this->userpage || $hook == $this->settingspage || $hook == $this->shortcodebuilderpage || $hook == $this->dashboardpage) {
            $this->get_main()->load_scripts();
            $this->get_main()->load_styles();

            wp_enqueue_script('jquery-effects-fade');
            wp_enqueue_script('WPCloudplugin.Libraries');

            wp_enqueue_style('LetsBox.ShortcodeBuilder');
            wp_enqueue_style('Eva-Icons');

            wp_enqueue_style('LetsBox.CustomStyle');

            // Build Whitelist for permission selection
            if ($hook !== $this->networksettingspage) {
                $vars = [
                    'whitelist' => json_encode(\TheLion\LetsBox\Helpers::get_all_users_and_roles()),
                    'ajax_url' => LETSBOX_ADMIN_URL,
                ];

                wp_localize_script('LetsBox.ShortcodeBuilder', 'LetsBox_ShortcodeBuilder_vars', $vars);
            }
        }

        if ($hook == $this->networksettingspage || $hook == $this->settingspage) {
            wp_enqueue_script('jquery-form');
            wp_enqueue_script('LetsBox.ShortcodeBuilder');
            wp_enqueue_script('wp-color-picker-alpha', LETSBOX_ROOTPATH.'/vendors/wp-color-picker-alpha/wp-color-picker-alpha.min.js', ['wp-color-picker'], '3.0.0', true);
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('jquery-ui-accordion');
            wp_enqueue_media();
            add_thickbox();
            wp_enqueue_script('LetsBox.Admin');
        }

        if ($hook == $this->userpage) {
            add_thickbox();
        }

        if ($hook == $this->dashboardpage) {
            wp_enqueue_script('LetsBox.Dashboard');
            wp_enqueue_style('LetsBox.Datatables');
            wp_dequeue_style('LetsBox');
            wp_dequeue_style('LetsBox.CustomStyle');
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
            $this->settingspage = add_submenu_page($this->plugin_options_key, 'Lets-Box - '.esc_html__('Settings'), esc_html__('Settings'), 'read', $this->plugin_options_key, [$this, 'load_settings_page']);
        }

        if (false === $this->is_activated()) {
            return;
        }

        if (Helpers::check_user_role($this->settings['permissions_see_dashboard']) && ('Yes' === $this->settings['log_events'])) {
            if (!$menuadded) {
                $this->dashboardpage = add_menu_page('Lets-Box', 'Lets-Box', 'read', $this->plugin_options_key, [$this, 'load_dashboard_page'], plugin_dir_url(__FILE__).'../css/images/box_logo_small.png');
                $this->dashboardpage = add_submenu_page($this->plugin_options_key, esc_html__('Reports', 'wpcloudplugins'), esc_html__('Reports', 'wpcloudplugins'), 'read', $this->plugin_options_key, [$this, 'load_dashboard_page']);
                $menuadded = true;
            } else {
                $this->dashboardpage = add_submenu_page($this->plugin_options_key, esc_html__('Reports', 'wpcloudplugins'), esc_html__('Reports', 'wpcloudplugins'), 'read', $this->plugin_options_key.'_dashboard', [$this, 'load_dashboard_page']);
            }
        }

        if (Helpers::check_user_role($this->settings['permissions_add_shortcodes'])) {
            if (!$menuadded) {
                $this->shortcodebuilderpage = add_menu_page('Lets-Box', 'Lets-Box', 'read', $this->plugin_options_key, [$this, 'load_shortcodebuilder_page'], plugin_dir_url(__FILE__).'../css/images/box_logo_small.png');
                $this->shortcodebuilderpage = add_submenu_page($this->plugin_options_key, esc_html__('Shortcode Builder', 'wpcloudplugins'), esc_html__('Shortcode Builder', 'wpcloudplugins'), 'read', $this->plugin_options_key, [$this, 'load_shortcodebuilder_page']);
                $menuadded = true;
            } else {
                $this->shortcodebuilderpage = add_submenu_page($this->plugin_options_key, esc_html__('Shortcode Builder', 'wpcloudplugins'), esc_html__('Shortcode Builder', 'wpcloudplugins'), 'read', $this->plugin_options_key.'_shortcodebuilder', [$this, 'load_shortcodebuilder_page']);
            }
        }

        if (Helpers::check_user_role($this->settings['permissions_link_users'])) {
            if (!$menuadded) {
                $this->userpage = add_menu_page('Lets-Box', 'Lets-Box', 'read', $this->plugin_options_key, [$this, 'load_linkusers_page'], plugin_dir_url(__FILE__).'../css/images/box_logo_small.png');
                $this->userpage = add_submenu_page($this->plugin_options_key, esc_html__('Link Private Folders', 'wpcloudplugins'), esc_html__('Link Private Folders', 'wpcloudplugins'), 'read', $this->plugin_options_key, [$this, 'load_linkusers_page']);
                $menuadded = true;
            } else {
                $this->userpage = add_submenu_page($this->plugin_options_key, esc_html__('Link Private Folders', 'wpcloudplugins'), esc_html__('Link Private Folders', 'wpcloudplugins'), 'read', $this->plugin_options_key.'_linkusers', [$this, 'load_linkusers_page']);
            }
        }

        if (Helpers::check_user_role($this->settings['permissions_see_filebrowser'])) {
            if (!$menuadded) {
                $this->filebrowserpage = add_menu_page('Lets-Box', 'Lets-Box', 'read', $this->plugin_options_key, [$this, 'load_filebrowser_page'], plugin_dir_url(__FILE__).'../css/images/box_logo_small.png');
                $this->filebrowserpage = add_submenu_page($this->plugin_options_key, esc_html__('File Browser', 'wpcloudplugins'), esc_html__('File Browser', 'wpcloudplugins'), 'read', $this->plugin_options_key, [$this, 'load_filebrowser_page']);
                $menuadded = true;
            } else {
                $this->filebrowserpage = add_submenu_page($this->plugin_options_key, esc_html__('File Browser', 'wpcloudplugins'), esc_html__('File Browser', 'wpcloudplugins'), 'read', $this->plugin_options_key.'_filebrowser', [$this, 'load_filebrowser_page']);
            }
        }
    }

    public function add_admin_network_menu()
    {
        if (!is_plugin_active_for_network(LETSBOX_SLUG)) {
            return;
        }

        add_menu_page('Lets-Box', 'Lets-Box', 'manage_options', $this->plugin_network_options_key, [$this, 'load_settings_network_page'], plugin_dir_url(__FILE__).'../css/images/box_logo_small.png');

        $this->networksettingspage = add_submenu_page($this->plugin_network_options_key, 'Lets-Box - '.esc_html__('Settings'), esc_html__('Settings'), 'read', $this->plugin_network_options_key, [$this, 'load_settings_network_page']);

        if ($this->get_processor()->is_network_authorized()) {
            $this->filebrowserpage = add_submenu_page($this->plugin_network_options_key, esc_html__('File Browser', 'wpcloudplugins'), esc_html__('File Browser', 'wpcloudplugins'), 'read', $this->plugin_network_options_key.'_filebrowser', [$this, 'load_filebrowser_page']);
        }
    }

    public function register_settings()
    {
        register_setting($this->settings_key, $this->settings_key);
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

        if ($this->get_processor()->is_network_authorized()) {
            $this->settings = array_merge($this->settings, get_site_option('letsbox_network_settings', []));
        }
    }

    public function load_settings_page()
    {
        if (!Helpers::check_user_role($this->settings['permissions_edit_settings'])) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'wpcloudplugins'));
        }

        include sprintf('%s/templates/admin/settings.php', LETSBOX_ROOTDIR);
    }

    public function load_settings_network_page()
    {
        include sprintf('%s/templates/admin/settings_network.php', LETSBOX_ROOTDIR);
    }

    public function save_settings($new_settings, $old_settings)
    {
        foreach ($new_settings as $setting_key => &$value) {
            if ('on' === $value) {
                $value = 'Yes';
            }

            if ('box_app_own' === $setting_key && 'No' === $value) {
                $new_settings['box_app_client_id'] = '';
                $new_settings['box_app_client_secret'] = '';
            }

            if ('colors' === $setting_key) {
                $value = $this->_check_colors($value, $old_settings['colors']);
            }

            // Store the ID of fields using tagify data
            if (is_string($value) && false !== strpos($value, '[{')) {
                $value = $this->_format_tagify_data($value);
            }

            if ('userfolder_backend_auto_root' === $setting_key && isset($value['view_roles'])) {
                $value['view_roles'] = $this->_format_tagify_data($value['view_roles']);
            }
        }

        $new_settings['icon_set'] = rtrim($new_settings['icon_set'], '/').'/';

        if ($new_settings['icon_set'] !== $old_settings['icon_set']) {
            $this->get_processor()->reset_complete_cache();
        }

        // Reset Custom CSS styles
        CSS::reset_custom_css();

        // Update Cron Job settings
        if ($new_settings['event_summary'] !== $old_settings['event_summary'] || $new_settings['event_summary_period'] !== $old_settings['event_summary_period']) {
            $summary_cron_job = wp_next_scheduled('letsbox_send_event_summary');
            if (false !== $summary_cron_job) {
                wp_unschedule_event($summary_cron_job, 'letsbox_send_event_summary');
            }
        }
        // If needed, a new cron job will be set when the plugin initiates again

        return $new_settings;
    }

    public function save_settings_network()
    {
        if (current_user_can('manage_network_options')) {
            update_site_option('letsbox_purchaseid', $_REQUEST['lets_box_settings']['purchase_code']);

            $settings = get_site_option('letsbox_network_settings', []);

            if (is_plugin_active_for_network(LETSBOX_SLUG) && 'on' === $_REQUEST['lets_box_settings']['network_wide']) {
                $settings['network_wide'] = 'Yes';
            } else {
                $settings['network_wide'] = 'No';
            }

            if ('Yes' === $settings['network_wide'] && isset($_REQUEST['lets_box_settings']['lostauthorization_notification'])) {
                $settings['box_account_type'] = $_REQUEST['lets_box_settings']['box_account_type'];
                $settings['lostauthorization_notification'] = $_REQUEST['lets_box_settings']['lostauthorization_notification'];
                $settings['box_app_own'] = ('on' === $_REQUEST['lets_box_settings']['box_app_own'] ? 'Yes' : 'No');

                if ('Yes' === $settings['box_app_own']) {
                    $settings['box_app_client_id'] = ($_REQUEST['lets_box_settings']['box_app_client_id']);
                    $settings['box_app_client_secret'] = ($_REQUEST['lets_box_settings']['box_app_client_secret']);
                } else {
                    $settings['box_app_client_id'] = '';
                    $settings['box_app_client_secret'] = '';
                }

                $settings['link_scope'] = isset($_REQUEST['lets_box_settings']['link_scope']) ? $_REQUEST['lets_box_settings']['link_scope'] : 'open';
            }

            update_site_option('letsbox_network_settings', $settings);
        }

        wp_redirect(
            add_query_arg(
                ['page' => $this->plugin_network_options_key, 'updated' => 'true'],
                network_admin_url('admin.php')
            )
        );

        exit;
    }

    public function is_activated()
    {

        $purchase_code = $this->get_main()->get_purchase_code();

        if (empty($purchase_code)) {
            delete_transient('letsbox_activation_validated');
            delete_site_transient('letsbox_activation_validated');

            return false;
        }

        if (false === get_transient('letsbox_activation_validated') && false === get_site_transient('letsbox_activation_validated')) {
            return $this->validate_activation();
        }

        return true;
    }

    public function validate_activation()
    {
        $purchase_code = $this->get_main()->get_purchase_code();
        $response = wp_remote_get('https://www.wpcloudplugins.com/updates/?action=get_license&slug=lets-box&purchase_code='.$purchase_code.'&plugin_id='.$this->plugin_id.'&installed_version='.LETSBOX_VERSION.'&siteurl='.rawurldecode(get_site_url()));

        $response_code = wp_remote_retrieve_response_code($response);

        if (empty($response_code)) {
            if (is_wp_error($response)) {
                error_log($response->get_error_message());
            }

            return false;
        }

        if (401 === $response_code) {
            // Revoke license if invalid
            $this->settings['purchase_code'] = '';
            update_option($this->settings_key, $this->settings);
            delete_transient('letsbox_activation_validated');

            delete_site_transient('letsbox_activation_validated');
            delete_site_option('letsbox_purchaseid');

            // Remove Cache Files
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(LETSBOX_CACHEDIR, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
                $path->isFile() ? @unlink($path->getPathname()) : @rmdir($path->getPathname());
            }

            return false;
        }

        set_transient('letsbox_activation_validated', true, WEEK_IN_SECONDS);
        set_site_transient('letsbox_activation_validated', true, WEEK_IN_SECONDS);

        return true;
    }

    public function load_filebrowser_page()
    {
        if (!Helpers::check_user_role($this->settings['permissions_see_filebrowser'])) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'wpcloudplugins'));
        }

        include sprintf('%s/templates/admin/file_browser.php', LETSBOX_ROOTDIR);
    }

    public function load_linkusers_page()
    {
        if (!Helpers::check_user_role($this->settings['permissions_link_users'])) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'wpcloudplugins'));
        }
        $linkusers = new LinkUsers($this->get_main());
        $linkusers->render();
    }

    public function load_shortcodebuilder_page()
    {
        if (!Helpers::check_user_role($this->settings['permissions_add_shortcodes'])) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'wpcloudplugins'));
        }

        echo "<iframe src='".LETSBOX_ADMIN_URL."?action=letsbox-getpopup&type=shortcodebuilder&standalone' width='90%' height='1000' tabindex='-1' frameborder='0'></iframe>";
    }

    public function load_dashboard_page()
    {
        if (!Helpers::check_user_role($this->settings['permissions_see_dashboard'])) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'wpcloudplugins'));
        }

        include sprintf('%s/templates/admin/event_dashboard.php', LETSBOX_ROOTDIR);
    }

    public function get_plugin_activated_box()
    {
        $purchase_code = $this->get_main()->get_purchase_code();

        // Check if Auto-update is being activated
        if (isset($_REQUEST['purchase_code'], $_REQUEST['plugin_id']) && ((int) $_REQUEST['plugin_id'] === $this->plugin_id)) {
            $purchase_code = $this->settings['purchase_code'] = sanitize_key($_REQUEST['purchase_code']);
            update_option($this->settings_key, $this->settings);

            if (is_multisite() && is_plugin_active_for_network(LETSBOX_SLUG)) {
                update_site_option('letsbox_purchaseid', sanitize_key($_REQUEST['purchase_code']));
            }
        }

        $box_class = 'wpcp-updated';
        $box_text = wp_kses(__('Thanks for registering your product! The plugin is <strong>Activated</strong> and the <strong>Auto-Updater</strong> enabled', 'wpcloudplugins'), ['strong' => []]).'. '.esc_html__('Your purchase code', 'wpcloudplugins').":<br/><code style='user-select: initial;'>".esc_attr($purchase_code).'</code>';

        $box_input = '<input type="hidden" name="lets_box_settings[purchase_code]" id="purchase_code" value="'.esc_attr($purchase_code).'">';

        if (empty($purchase_code)) {
            $box_class = 'wpcp-error';
            $box_text = wp_kses(__('The plugin is <strong>Not Activated</strong> and the <strong>Auto-Updater</strong> disabled', 'wpcloudplugins'), ['strong' => []]).'. '.esc_html__('Please activate your copy in order to use the plugin', 'wpcloudplugins').'. ';

            if (false === is_plugin_active_for_network(LETSBOX_SLUG) || true === is_network_admin()) {
                $box_text .= "</p><p><button id='wpcp-activate-button' class='simple-button blue'><i class='eva eva-flash eva-lg'></i>".esc_html__('Activate via Envato Market', 'wpcloudplugins')."</button><a href='https://1.envato.market/M4B53' target='_blank' class='simple-button default'><i class='eva eva-star eva-lg'></i>".esc_html__('Buy License', 'wpcloudplugins')."</a></p><p><a href='#' onclick='jQuery(\".letsbox_purchasecode_manual\").slideToggle()'>".esc_html__('Or insert your license code manually.', 'wpcloudplugins').'</a></p>';
                $box_text .= '<div class="letsbox_purchasecode_manual" style="display:none" ><h3>License Code:</h3><input name="lets_box_settings[purchase_code]" id="purchase_code" class="letsbox-option-input-large" placeholder="XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX" value="'.esc_attr($purchase_code).'">Press Activate to validate the code.  <a href="https://florisdeleeuwnl.zendesk.com/hc/en-us/articles/201834487" target="_blank">FAQ: '.esc_html__('Where can I find my license code?', 'wpcloudplugins').'</a></div>';
                $box_input = '';
            }
        } else {
            $box_text .= "</p><p><button id='wpcp-check-updates-button' class='simple-button blue'><i class='eva eva-refresh eva-lg'></i>".esc_html__('Check for updates', 'wpcloudplugins').'</button>';
            if (false === is_plugin_active_for_network(LETSBOX_SLUG) || true === is_network_admin()) {
                $box_text .= "<button id='wpcp-deactivate-license-button' class='simple-button default'><i class='eva eva-power eva-lg'></i>".
                esc_html__('Deactivate License', 'wpcloudplugins').'</button>';
            }
            $box_text .= "<a href='https://1.envato.market/a4ggZ' target='_blank' class='simple-button default'><i class='eva eva-star eva-lg'></i>".esc_html__('Buy License', 'wpcloudplugins').'</a>';
        }

        $box_text .= '<div style="margin-top:50px;"><a href="https://1.envato.market/a4ggZ" target="_blank"><img src="'.LETSBOX_ROOTPATH.'/css/images/envato-market.svg" width="200"><br/><em>Envato Market is the only official distributor of the WP Cloud Plugins.</em></a></div>';

        return "<div id='message' class='{$box_class} letsbox-option-description'><p>{$box_text}</p>{$box_input}</div>";
    }

    public function get_plugin_authorization_box()
    {
        $revokebutton = "<div id='wpcp-revoke-account-button' type='button' class='simple-button blue'/>".esc_html__('Revoke authorization', 'wpcloudplugins')."&nbsp;<div class='wpcp-spinner'></div></div>";

        try {
            $app = $this->get_app();
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('Lets-Box has encountered an error: %s', $ex->getMessage()));

            $box_class = 'wpcp-error';
            $box_text = '<strong>'.esc_html__('WP Cloud Plugins has encountered an error', 'wpcloudplugins').'</strong> ';
            $box_text .= '<p><em>Error Details:</em> <code>'.$ex->getMessage().'</code></p>';

            return "<div id = 'message' class = '{$box_class} letsbox-option-description'><p>{$box_text}</p><p>{$revokebutton}</p></div>";
        }

        $app->set_approval_prompt('login');
        $authurl = $app->build_auth_url();
        $authorizebutton = "<div id='wpcp-add-account-button' type='button' class='simple-button blue' data-url='{$authurl}'/>".esc_html__('(Re) Authorize the Plugin!', 'wpcloudplugins')."&nbsp;<div class='wpcp-spinner'></div></div>";

        if ($app->has_access_token()) {
            try {
                $client = $this->get_processor()->get_client();

                // Update the Account data
                $account = $client->get_account_info();
                $account_name = $account->getName();
                $account_email = $account->getLogin();
                $account_space_quota_used = Helpers::bytes_to_size_1024($account->getSpaceUsed());
                $account_space_quota_total = Helpers::bytes_to_size_1024($account->getSpaceAmount());

                $box_class = 'wpcp-updated';
                $box_text = sprintf(esc_html__('Succesfully authorized and linked with account:', 'wpcloudplugins'))." <br/><code>{$account_name} - {$account_email} ({$account_space_quota_used}/{$account_space_quota_total}) - ".ucfirst($app->get_account_type()).' Account</code>';
                $box_buttons = $revokebutton;
            } catch (\Exception $ex) {
                error_log('[WP Cloud Plugin message]: '.sprintf('Lets-Box has encountered an error: %s', $ex->getMessage()));

                $box_class = 'wpcp-error';
                $box_text = '<strong>'.esc_html__('WP Cloud Plugins has encountered an error', 'wpcloudplugins').'</strong> ';
                if ($app->has_plugin_own_app()) {
                    $box_text .= '<p>'.esc_html__('Please fall back to the default App by clearing the KEY and Secret on the Advanced settings tab', 'wpcloudplugins').'.</p>';
                }

                $box_text .= '<p><em>Error Details: '.$ex->getMessage().'</em></p>';
                $box_buttons = $revokebutton.$authorizebutton;
            }
        } else {
            $box_class = 'wpcp-error';
            $box_text = '<strong>'.esc_html__("Plugin isn't linked to your account anymore... Please authorize!", 'wpcloudplugins').'</strong>';

            $box_text .= '<div class="letsbox-option-description">'.esc_html__('To authorize the plugin, you will agree with the following', 'wpcloudplugins').':<ul><li>'.
                    sprintf(esc_html__('Allow the plugin to see, edit, create, and delete all of your %s files', 'wpcloudplugins'), 'Box').'.</li>'.
                    '<li>'.esc_html__('Allow the plugin to read and update data when your users are using the plugin', 'wpcloudplugins').'.</li>'.
                    '<li>'.esc_html__('Allow the plugin to see basic user profile information', 'wpcloudplugins').'.</li></ul></div>';

            $box_buttons = $authorizebutton;
        }

        if ($this->get_processor()->is_network_authorized() && false === is_network_admin()) {
            $box_buttons = sprintf(wp_kses(__("The authorization is managed by the Network Admin via the <a href='%s'>Network Settings Page</a> of the plugin", 'wpcloudplugins'), ['a' => ['href' => []]]), network_admin_url('admin.php?page=LetsBox_network_settings')).'.';
        }

        return "<div id = 'message' class = '{$box_class} letsbox-option-description'><p>{$box_text}</p><p>{$box_buttons}</p></div>";
    }

    public function get_plugin_reset_cache_box()
    {
        $box_text = esc_html__('WP Cloud Plugins uses a cache to improve performance', 'wpcloudplugins').'. '.esc_html__('If the plugin somehow is causing issues, try to reset the cache first', 'wpcloudplugins').'.<br/>';

        $box_button = "<div id='wpcp-purge-cache' type='button' class='simple-button blue'/>".esc_html__('Purge Cache', 'wpcloudplugins')."&nbsp;<div class='wpcp-spinner'></div></div>";

        return "<div id='message'><div class='letsbox-option-description'>{$box_text}</div><p>{$box_button}</p></div>";
    }

    public function get_plugin_reset_plugin_box()
    {
        $box_text = esc_html__('Need to revert back to the default settings? This button will instantly reset your settings to the defaults', 'wpcloudplugins').'. '.esc_html__('When you reset the settings, the plugin will not longer be linked to your accounts, but their authorization will not be revoked', 'wpcloudplugins').'. '.esc_html__('You can revoke the authorization via the General tab', 'wpcloudplugins').'.<br/>';

        $box_button = "<div id='wpcp-factory-reset-button' type='button' class='simple-button blue'/>".esc_html__('Reset Plugin', 'wpcloudplugins')."&nbsp;<div class='wpcp-spinner'></div></div>";

        return "<div id='message'><div class='letsbox-option-description'>{$box_text}</div><p>{$box_button}</p></div>";
    }

    public function get_admin_notice($force = false)
    {
        global $pagenow;
        if ('index.php' == $pagenow || 'plugins.php' == $pagenow || true === $force) {
            if (version_compare(PHP_VERSION, '7.4') < 0) {
                echo '<div id="message" class="error"><p><strong>Lets-Box - Error: </strong>'.sprintf(esc_html__('You need at least PHP %s if you want to use this plugin', 'wpcloudplugins'), '7.4').'. '.
                esc_html__('You are using:', 'wpcloudplugins').' <u>'.phpversion().'</u></p></div>';
            } elseif (!function_exists('curl_init')) {
                $this->canconnect = false;
                echo '<div id="message" class="error"><p><strong>Lets-Box - Error: </strong>'.
                esc_html__("We are not able to connect to the API as you don't have the cURL PHP extension installed", 'wpcloudplugins').'. '.
                esc_html__('Please enable or install the cURL extension on your server', 'wpcloudplugins').'. '.
                '</p></div>';
            } elseif (class_exists('Box_Client') && (!method_exists('Box_Client', 'getLibraryVersion'))) {
                $this->canconnect = false;
                echo '<div id="message" class="error"><p><strong>Lets-Box - Error: </strong>'.
                esc_html__('We are not able to connect to the API as the plugin is interfering with an other plugin', 'wpcloudplugins').'. <br/><br/>'.
                esc_html__("The other plugin is using an old version of the Api-PHP-client that isn't capable of running multiple configurations", 'wpcloudplugins').'. '.
                esc_html__('Please disable this other plugin if you would like to use this plugin', 'wpcloudplugins').'. '.
                esc_html__("If you would like to use both plugins, ask the developer to update it's code", 'wpcloudplugins').'. '.
                '</p></div>';
            } elseif (!file_exists(LETSBOX_CACHEDIR) || !is_writable(LETSBOX_CACHEDIR) || !file_exists(LETSBOX_CACHEDIR.'/.htaccess')) {
                echo '<div id="message" class="error"><p><strong>Lets-Box - Error: </strong>'.sprintf(esc_html__('Cannot create the cache directory %s, or it is not writable', 'wpcloudplugins'), '<code>'.LETSBOX_CACHEDIR.'</code>').'. '.
                sprintf(esc_html__('Please check if the directory exists on your server and has %s writing permissions %s', 'wpcloudplugins'), '<a href="https://codex.wordpress.org/Changing_File_Permissions" target="_blank">', '</a>').'</p></div>';
            } elseif ((!defined('CURL_SSLVERSION_TLSv1_1') && !defined('CURL_SSLVERSION_TLSv1_2'))) {
                echo '<div id="message" class="error"><p><strong>Lets-Box - Error: </strong>'.esc_html__('The cURL library of your server does not support TLS1.1 or TLS1.2 (CURL_SSLVERSION_TLSv1_1 | CURL_SSLVERSION_TLSv1_2). This is required by the API to create a secure connection. Please contact your webhost and ask them to upgrade the curl library and/or enable TLS1.1 support or higher for this library', 'wpcloudplugins').'. '.
                '</p></div>';
            }
        }
    }

    public function get_admin_notice_not_authorized()
    {
        global $pagenow;
        if ('index.php' == $pagenow || 'plugins.php' == $pagenow) {
            if (current_user_can('manage_options') || current_user_can('edit_theme_options')) {
                $app = new \TheLion\LetsBox\App($this->get_processor());
                $location = get_admin_url(null, 'admin.php?page=LetsBox_settings');

                if (false === $app->has_access_token() || (false !== wp_next_scheduled('letsbox_lost_authorisation_notification'))) {
                    echo '<div id="message" class="error"><p><span class="dashicons dashicons-warning"></span>&nbsp;<strong>Lets-Box: </strong>'.sprintf(esc_html__("The plugin isn't linked with a %s account. Authorize the plugin or disable it if is not used on the site.", 'wpcloudplugins'), 'Box').'</p>'.
                        "<p><a href='{$location}' class='button-primary'>❱❱❱ &nbsp;".esc_html__('Authorize the plugin!', 'wpcloudplugins').'</a></p></div>';
                }
            }
        }
    }

    public function get_admin_notice_not_activated()
    {
        global $pagenow;

        if ($this->is_activated()) {
            return;
        }

        if ('index.php' == $pagenow || 'plugins.php' == $pagenow) {
            if (current_user_can('manage_options') || current_user_can('edit_theme_options')) {
                $location = get_admin_url(null, 'admin.php?page=LetsBox_settings'); ?>
                <div id="message" class="error">
                    <img src="<?php echo LETSBOX_ROOTPATH; ?>/css/images/wpcp-logo-dark.svg" height="84" width="84"
                        class="alignleft" style="padding: 20px 20px 20px 10px;">
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
    }

    public function check_for_updates()
    {
        // Updater
        $purchase_code = $this->get_main()->get_purchase_code();

        if (!empty($purchase_code)) {
            require_once LETSBOX_ROOTDIR.'/vendors/plugin-update-checker/plugin-update-checker.php';
            $updatechecker = \Puc_v4_Factory::buildUpdateChecker('https://www.wpcloudplugins.com/updates/?action=get_metadata&slug=lets-box&purchase_code='.$purchase_code.'&plugin_id='.$this->plugin_id, plugin_dir_path(__DIR__).'/lets-box.php');
        }
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

    private function _check_colors($colors, $old_colors)
    {
        $regex = '/(light|dark|transparent|#(?:[0-9a-f]{2}){2,4}|#[0-9a-f]{3}|(?:rgba?|hsla?)\((?:\d+%?(?:deg|rad|grad|turn)?(?:,|\s)+){2,3}[\s\/]*[\d\.]+%?\))/i';

        foreach ($colors as $color_id => &$color) {
            if (1 !== preg_match($regex, $color)) {
                $color = $old_colors[$color_id];
            }
        }

        return $colors;
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
