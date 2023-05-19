<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2022, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\LetsBox;

class License
{
    public static $license_code;

    public static function init()
    {
        add_action('wp_ajax_letsbox-license', [__CLASS__, 'ajax_call']);

        if (isset($_REQUEST['purchase_code'], $_REQUEST['plugin_id']) && ('8204640' === (string) $_REQUEST['plugin_id'])) {
            self::save($_REQUEST['purchase_code']);
            echo '<script type="text/javascript">window.opener.parent.location.reload(); window.close();</script>';

            exit;
        }
    }

    public static function save($license_code)
    {
        $license_code = sanitize_key($license_code);
        Core::save_setting('purchase_code', $license_code);

        if (is_multisite() && is_plugin_active_for_network(LETSBOX_SLUG)) {
            update_site_option('letsbox_purchaseid', $license_code);
        }

        self::$license_code = $license_code;
    }

    public static function get()
    {
        if (null !== self::$license_code) {
            return self::$license_code;
        }

        $license_code = Core::get_setting('purchase_code');

        if (is_multisite()) {
            $site_license_code = get_site_option('letsbox_purchaseid');

            if (!empty($site_license_code)) {
                $license_code = $site_license_code;
            }
        }

        self::$license_code = trim(apply_filters('letsbox_purchasecode', $license_code));

        return self::$license_code;
    }

    public static function validate($force = false, $license_code = null)
    {
        $license_code = empty($license_code) ? self::get() : $license_code;

        $cached_data = get_site_option('wpcp_license_'.$license_code);

        if (false === $force && false === empty($cached_data) && $cached_data['expires'] > time()) {
            return $cached_data['license_data'];
        }

        $response = wp_remote_get('https://www.wpcloudplugins.com/updates/?action=get_license&slug=lets-box&purchase_code='.$license_code.'&plugin_id=8204640&force='.$force.'&installed_version='.LETSBOX_VERSION.'&siteurl='.rawurldecode(get_site_url()));
        $response_code = wp_remote_retrieve_response_code($response);

        if (empty($response_code)) {
            if (is_wp_error($response)) {
                error_log($response->get_error_message());
            }

            return false;
        }

        if (401 === $response_code) {
            // Revoke license if invalid
            self::_revoke();

            return false;
        }

        $license_data = json_decode(wp_remote_retrieve_body($response), true);
        update_site_option('wpcp_license_'.$license_code, ['license_data' => $license_data, 'expires' => time() + WEEK_IN_SECONDS]);

        return $license_data;
    }

    public static function is_valid()
    {
        $license_code = self::get();

        if (empty($license_code)) {
            return false;
        }

        return false !== self::validate(false, $license_code);
    }

    public static function ajax_call()
    {
        // Check AJAX call
        check_ajax_referer('letsbox-admin-action');

        $license_code = isset($_POST['license_code']) ? sanitize_key($_POST['license_code']) : self::get();

        $return = [
            'license_code' => $license_code,
            'valid' => false,
            'support_package' => false,
            'error_message' => '',
            'data' => [],
        ];

        if (isset($_POST['type']) && 'deactivate' === $_POST['type']) {
            self::_revoke();
            echo \json_encode($return);

            exit;
        }

        $license_data = self::validate(true, $license_code);

        if (false === $license_data) {
            $return['error_message'] = esc_html__('This license is no longer valid. The plugin will soon be deactivated.', 'wpcloudplugins');

            echo \json_encode($return);

            exit;
        }

        if (isset($_POST['type']) && 'activate' === $_POST['type']) {
            self::save($license_code);
        }

        $return['valid'] = true;
        $return['data'] = $license_data;
        $supported_until_str = isset($license_data['supported_until']) ? date_i18n(get_option('date_format'), strtotime($license_data['supported_until'])) : esc_html__('today', 'wpcloudplugins');
        $return['supported_until_str'] = sprintf(esc_html__('Support package valid till %s', 'wpcloudplugins'), $supported_until_str);

        if (isset($license_data['supported_until']) && $license_data['supported_until'] < date('c')) {
            $return['error_message'] = sprintf(esc_html__('The support period for this license has expired on %s.', 'wpcloudplugins'), $supported_until_str);
        } else {
            $return['support_package'] = true;
        }

        echo \json_encode($return);

        exit;
    }

    public static function reset()
    {
        $license_code = self::get();

        if (empty($license_code)) {
            return false;
        }

        delete_site_option('wpcp_license_'.$license_code);
    }

    private static function _revoke()
    {
        self::reset();
        self::save('');

        delete_site_option('letsbox_purchaseid');

        // Remove Cache Files
        require_once ABSPATH.'wp-admin/includes/class-wp-filesystem-base.php';

        require_once ABSPATH.'wp-admin/includes/class-wp-filesystem-direct.php';

        $wp_file_system = new \WP_Filesystem_Direct(false);

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(LETSBOX_CACHEDIR, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
            if ('.htaccess' === $path->getFilename()) {
                continue;
            }

            try {
                $wp_file_system->delete($path->getPathname(), true);
            } catch (\Exception $ex) {
                continue;
            }
        }
    }
}
