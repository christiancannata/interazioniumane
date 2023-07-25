<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\LetsBox;

class App
{
    /**
     * The single instance of the class.
     *
     * @var App
     */
    protected static $_instance;

    /**
     * @var bool
     */
    private $_own_app = false;

    /**
     * @var string
     */
    private $_app_key = 'y2oy4vyqecddnzwlnz4xqxra0nqzoh2r';

    /**
     * @var string
     */
    private $_app_secret = 'Oh7MYIshWKyEsGWNuwUQeFiGRWASuzsw';

    /**
     * @var \Box\Model\Client\Client
     */
    private static $_sdk_client;

    /**
     * @var \TheLion\LetsBox\Account
     */
    private static $_current_account;

    /**
     * We don't save your data or share it.
     * It is used for an easy and one-click authorization process that will always work!
     *
     * @var string
     */
    private $_auth_url = 'https://www.wpcloudplugins.com/lets-box/authorizeApp.php';

    public function __construct()
    {
        // Call back for refresh token function in SDK client
        add_action('lets-box-refresh-token', [$this, 'refresh_token'], 10, 1);

        if (!function_exists('\Box\autoload')) {
            require_once LETSBOX_ROOTDIR.'/vendors/API/autoload.php';
        }

        $own_key = Processor::instance()->get_setting('box_app_client_id');
        $own_secret = Processor::instance()->get_setting('box_app_client_secret');

        if (
            (!empty($own_key))
            && (!empty($own_secret))
        ) {
            $this->_app_key = Processor::instance()->get_setting('box_app_client_id');
            $this->_app_secret = Processor::instance()->get_setting('box_app_client_secret');
            $this->_own_app = true;
        }
    }

    /**
     * App Instance.
     *
     * Ensures only one instance is loaded or can be loaded.
     *
     * @return App - App instance
     *
     * @static
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            $app = new self();
        } else {
            $app = self::$_instance;
        }

        if (empty($app::$_sdk_client)) {
            try {
                $app->start_sdk_client(App::get_current_account());
            } catch (\Exception $ex) {
                self::$_instance = $app;

                return self::$_instance;
            }
        }

        self::$_instance = $app;

        if (null !== App::get_current_account()) {
            $app->get_sdk_client(App::get_current_account());
        }

        return self::$_instance;
    }

    public function process_authorization()
    {
        Processor::reset_complete_cache();

        $redirect = admin_url('admin.php?page=LetsBox_settings');
        if (isset($_GET['network']) || Processor::instance()->is_network_authorized()) {
            $redirect = network_admin_url('admin.php?page=LetsBox_network_settings');
        }

        if (empty($_GET['ver'])) {
            // Close oAuth popup and refresh admin page. Only possible with inline javascript.
            echo '<script type="text/javascript">window.opener.parent.location.href = "'.$redirect.'"; window.close();</script>';

            exit;
        }

        if (isset($_GET['code'])) {
            $this->create_access_token();
        }

        // Close oAuth popup and refresh admin page. Only possible with inline javascript.
        echo '<script type="text/javascript">window.opener.parent.location.href = "'.$redirect.'"; window.close();</script>';

        exit;
    }

    public function has_plugin_own_app()
    {
        return $this->_own_app;
    }

    public function get_auth_url()
    {
        return $this->_auth_url;
    }

    public function build_auth_url()
    {
        return self::get_sdk_client()->buildAuthQuery();
    }

    /**
     * @return \Box_Client
     */
    public function start_sdk_client(Account $account = null)
    {
        try {
            self::$_sdk_client = new \Box\Model\Client\Client();
        } catch (\Exception $ex) {
            return $ex;
        }

        self::$_sdk_client->setClientId($this->get_app_key());
        self::$_sdk_client->setClientSecret($this->get_app_secret());
        self::$_sdk_client->setRedirectUri($this->get_auth_url());

        $scopes = [
            'root_readwrite',
        ];

        $scopes = apply_filters('letsbox_app_scopes', $scopes);

        self::$_sdk_client->setScopes($scopes);

        if (Processor::instance()->is_network_authorized() || is_network_admin()) {
            $state = network_admin_url('admin.php?page=LetsBox_network_settings&action=letsbox_authorization');
        } else {
            $state = admin_url('admin.php?page=LetsBox_settings&action=letsbox_authorization');
        }

        $state .= '&license='.(string) License::get();
        self::$_sdk_client->setState(strtr(base64_encode($state), '+/=', '-_~'));

        $this->set_logger();

        if (null === $account) {
            return self::$_sdk_client;
        }

        self::set_current_account($account);

        $authorization = $account->get_authorization();

        if (false === $authorization->has_access_token()) {
            return self::$_sdk_client;
        }

        $access_token = $authorization->get_access_token();

        if (empty($access_token)) {
            return self::$_sdk_client;
        }

        self::$_sdk_client->setToken($access_token);

        // Check if the AccessToken is still valid
        if (false === self::$_sdk_client->isAccessTokenExpired()) {
            return self::$_sdk_client;
        }

        // If we end up here, we have to refresh the token
        return $this->refresh_token($account);
    }

    public function refresh_token(Account $account = null)
    {
        $authorization = $account->get_authorization();
        $access_token = $authorization->get_access_token();

        if (!flock($authorization->get_token_file_handle(), LOCK_EX | LOCK_NB)) {
            error_log('[WP Cloud Plugin message]: '.sprintf('Wait till another process has renewed the Authorization Token'));

            /*
             * If the file cannot be unlocked and the last time
             * it was modified was 1 minute, assume that
             * the previous process died and unlock the file manually
             */
            $requires_unlock = ((filemtime($authorization->get_token_location()) + 60) < time());

            // Temporarily workaround when flock is disabled. Can cause problems when plugin is used in multiple processes
            if (false !== strpos(ini_get('disable_functions'), 'flock')) {
                $requires_unlock = false;
            }

            if ($requires_unlock) {
                $authorization->unlock_token_file();
            }

            if (flock($authorization->get_token_file_handle(), LOCK_SH)) {
                clearstatcache();
                rewind($authorization->get_token_file_handle());
                $access_token = fread($authorization->get_token_file_handle(), filesize($authorization->get_token_location()));
                error_log('[WP Cloud Plugin message]: '.sprintf('New Authorization Token has been received by another process.'));
                self::$_sdk_client->setToken($access_token);
                $authorization->unlock_token_file();

                return self::$_sdk_client;
            }
        }

        // Stop if we need to get a new AccessToken but somehow ended up without a refreshtoken
        $refresh_token = self::$_sdk_client->getToken()->getRefreshToken();

        if (empty($refresh_token)) {
            error_log('[WP Cloud Plugin message]: '.sprintf('No Refresh Token found during the renewing of the current token. We will stop the authorization completely.'));
            $authorization->set_is_valid(false);
            $authorization->unlock_token_file();
            $this->revoke_token($account);

            return false;
        }

        // Refresh token
        try {
            self::$_sdk_client->refreshToken($refresh_token);

            // Store the new token
            $new_accestoken = self::$_sdk_client->getToken();
            $authorization->set_access_token($new_accestoken);
            $authorization->unlock_token_file();

            if (false !== ($timestamp = wp_next_scheduled('letsbox_lost_authorisation_notification', ['account_id' => $account->get_id()]))) {
                wp_unschedule_event($timestamp, 'letsbox_lost_authorisation_notification', ['account_id' => $account->get_id()]);
            }
        } catch (\Exception $ex) {
            $authorization->set_is_valid(false);
            $authorization->unlock_token_file();
            error_log('[WP Cloud Plugin message]: '.sprintf('Cannot refresh Authorization Token'));

            if (!wp_next_scheduled('letsbox_lost_authorisation_notification', ['account_id' => $account->get_id()])) {
                wp_schedule_event(time(), 'daily', 'letsbox_lost_authorisation_notification', ['account_id' => $account->get_id()]);
            }

            Processor::reset_complete_cache(true);

            throw $ex;
        }

        return self::$_sdk_client;
    }

    public function set_logger()
    {
    }

    public function create_access_token()
    {
        Processor::reset_complete_cache();

        try {
            $code = $_REQUEST['code'];
            $state = $_REQUEST['state'];

            // Fetch the AccessToken
            self::get_sdk_client()->setAuthorizationCode($code);
            $access_token = self::get_sdk_client()->getAccessToken();

            // Get & Update User Information
            $account_data = self::get_sdk_client()->getUserInfo();
            $enterprise = $account_data->getEnterprise();

            $account = new Account(
                $account_data->getId(),
                $account_data->getName(),
                $account_data->getLogin(),
                empty($enterprise) ? 'personal' : 'business'
            );

            // Box will set an empty 1x1 pixel for users without profile image.
            $profile_image = wp_remote_retrieve_body(wp_remote_get($account_data->getAvatarUrl()));
            if (strlen($profile_image) > 100) {
                $account->set_image($account_data->getAvatarUrl());
            }

            $account->get_authorization()->set_access_token($access_token);
            $account->get_authorization()->unlock_token_file();
            Accounts::instance()->add_account($account);

            delete_transient('letsbox_'.$account->get_id().'_is_authorized');
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('Cannot generate Access Token: %s', $ex->getMessage()));

            return new \WP_Error('broke', esc_html__('Error communicating with API:', 'wpcloudplugins').$ex->getMessage());
        }

        return true;
    }

    public function revoke_token(Account $account)
    {
        error_log('[WP Cloud Plugin message]: Lost authorization');

        // Reset Private Folders Back-End if the account it is pointing to is deleted
        $private_folders_data = Processor::instance()->get_setting('userfolder_backend_auto_root', []);
        if (is_array($private_folders_data) && isset($private_folders_data['account']) && $private_folders_data['account'] === $account->get_id()) {
            Processor::instance()->set_setting('userfolder_backend_auto_root', []);
        }

        Processor::reset_complete_cache(true);

        if (false !== ($timestamp = wp_next_scheduled('letsbox_lost_authorisation_notification', ['account_id' => $account->get_id()]))) {
            wp_unschedule_event($timestamp, 'letsbox_lost_authorisation_notification', ['account_id' => $account->get_id()]);
        }

        Core::instance()->send_lost_authorisation_notification($account->get_id());

        try {
            self::get_sdk_client()->destroyToken($account->get_authorization()->get_access_token());
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.$ex->getMessage());
        }

        Accounts::instance()->remove_account($account->get_id());

        delete_transient('letsbox_'.$account->get_id().'_is_authorized');

        return true;
    }

    public function get_app_key()
    {
        return $this->_app_key;
    }

    public function get_app_secret()
    {
        return $this->_app_secret;
    }

    public function set_app_key($_app_key)
    {
        $this->_app_key = $_app_key;
    }

    public function set_app_secret($_app_secret)
    {
        $this->_app_secret = $_app_secret;
    }

    /**
     * @param null|\TheLion\LetsBox\Account $account
     *
     * @return \Box\Model\Client\Client
     */
    public static function get_sdk_client($account = null)
    {
        if (empty(self::$_sdk_client)) {
            self::$_sdk_client = self::instance()->start_sdk_client();
        }

        if (!empty($account)) {
            self::set_current_account($account);
        }

        return self::$_sdk_client;
    }

    /**
     * @deprecated
     *
     * @return \Box\Model\Client\Client
     */
    public function get_client()
    {
        Helpers::is_deprecated('function', 'get_client()', 'get_sdk_client($account = null)');

        return self::get_sdk_client();
    }

    /**
     * @return \TheLion\LetsBox\Account
     */
    public static function get_current_account()
    {
        if (empty(self::$_current_account)) {
            if (null !== Processor::instance()->get_shortcode()) {
                $account = Accounts::instance()->get_account_by_id(Processor::instance()->get_shortcode_option('account'));
                if (!empty($account)) {
                    self::set_current_account($account);
                }
            }
        }

        return self::$_current_account;
    }

    public static function set_current_account(Account $account)
    {
        if (self::$_current_account !== $account) {
            self::$_current_account = $account;
            Cache::instance_unload();

            if ($account->get_authorization()->has_access_token()) {
                if (empty(self::$_sdk_client)) {
                    self::instance();
                }

                self::$_sdk_client->setToken($account->get_authorization()->get_access_token());
            }
        }

        return self::$_current_account;
    }

    public static function set_current_account_by_id($account_id)
    {
        $account = Accounts::instance()->get_account_by_id($account_id);

        if (empty($account)) {
            error_log(sprintf('[WP Cloud Plugin message]: APP Error on line %s: Cannot use the requested account (ID: %s) as it is not linked with the plugin. Plugin falls back to primary account.', __LINE__, $account_id));
            $account = Accounts::instance()->get_primary_account();
        }

        return self::set_current_account($account);
    }

    public static function clear_current_account()
    {
        self::$_current_account = null;
        Cache::instance_unload();
    }

    public function get_auth_uri()
    {
        return $this->_auth_url;
    }
}
