<?php

namespace TheLion\LetsBox;

class App
{
    /**
     * @var string
     */
    private $_account_type;

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
    private $_client;

    /**
     * We don't save your data or share it.
     * This script just simply creates a redirect with your id and secret to Box and returns the created token.
     * It is exactly the same script as the _authorizeApp.php file in the includes folder of the plugin,
     * and is used for an easy and one-click authorization process that will always work!
     *
     * If you use your own Box App, you can use your own auth_url in case you are using HTTPS.
     * The plugin will show you this on the Advanced Tab on the Settings page
     *
     * @var string
     */
    private $_auth_url = 'https://www.wpcloudplugins.com/lets-box/authorizeApp.php';

    /**
     * @var string
     */
    private $_redirect_uri;

    /**
     * Contains the location to the token file.
     *
     * @var string
     */
    private $_token_location;

    /**
     * Contains the file handle for the token file.
     *
     * @var type
     */
    private $_token_file_handle;

    /**
     * @var \TheLion\LetsBox\Processor
     */
    private $_processor;

    public function __construct(Processor $processor)
    {
        $this->_processor = $processor;

        $this->_token_location = LETSBOX_CACHEDIR.get_current_blog_id().'.access_token';
        if ($this->_processor->is_network_authorized()) {
            $this->_token_location = LETSBOX_CACHEDIR.'/network.access_token';
        }

        // Call back for refresh token function in SDK client
        add_action('lets-box-refresh-token', [$this, 'start_client']);

        if (!function_exists('\Box\autoload')) {
            require_once LETSBOX_ROOTDIR.'/vendors/API/autoload.php';
        }

        $own_key = $this->get_processor()->get_setting('box_app_client_id');
        $own_secret = $this->get_processor()->get_setting('box_app_client_secret');

        if (
                (!empty($own_key))
                && (!empty($own_secret))
        ) {
            $this->_app_key = $this->get_processor()->get_setting('box_app_client_id');
            $this->_app_secret = $this->get_processor()->get_setting('box_app_client_secret');
            $this->_own_app = true;
        }

        $this->_account_type = $this->get_processor()->get_setting('box_account_type');

        // Set right redirect URL
        $this->set_redirect_uri();

        if ($this->has_plugin_own_app() && $this->can_do_own_auth()) {
            $this->_auth_url = LETSBOX_ROOTPATH.'/includes/_authorizeApp.php';
        }
    }

    public function process_authorization()
    {
        $this->get_processor()->reset_complete_cache();

        if (isset($_GET['code'])) {
            $access_token = $this->create_access_token();
        }

        if (isset($_GET['_token'])) {
            $new_access_token = $_GET['_token'];
            $access_token = $this->set_access_token($new_access_token);
        }
        

        // Close oAuth popup and refresh admin page. Only possible with inline javascript.
        echo '<script type="text/javascript">window.opener.parent.location.href = "'.$this->get_redirect_uri().'"; window.close();</script>';

        exit();
    }

    public function can_do_own_auth()
    {
        $blog_url = parse_url(admin_url());

        return 'https' === $blog_url['scheme'];
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
        return $this->get_client()->buildAuthQuery();
    }

    /**
     * @return \Box_Client
     */
    public function start_client()
    {
        try {
            $this->_client = new \Box\Model\Client\Client();
        } catch (\Exception $ex) {
            return $ex;
        }

        $this->_client->setClientId($this->get_app_key());
        $this->_client->setClientSecret($this->get_app_secret());

        $this->_client->setRedirectUri($this->get_auth_url());

        $state = $this->get_redirect_uri().'&action=letsbox_authorization';
        $state .= '&license='.(string) $this->get_processor()->get_main()->get_purchase_code();
        $this->_client->setState(strtr(base64_encode($state), '+/=', '-_~'));

        if (false === $this->has_access_token()) {
            return $this->_client;
        }

        $access_token = $this->get_access_token();
        $access_token = $this->get_client()->setTokenData(new \Box\Model\Connection\Token\Token(), (array) json_decode($access_token));

        if (empty($access_token)) {
            $this->_unlock_token_file();

            return $this->_client;
        }

        if (!empty($access_token)) {
            $this->_client->setToken($access_token);

            // Check if the AccessToken is still valid
            if (false === $this->_client->isAccessTokenExpired()) {
                $this->_unlock_token_file();

                return $this->_client;
            }
        }

        if (!flock($this->_get_token_file_handle(), LOCK_EX | LOCK_NB)) {
            error_log('[WP Cloud Plugin message]: '.sprintf('Wait till another process has renewed the Authorization Token'));

            /*
             * If the file cannot be unlocked and the last time
             * it was modified was 1 minute, assume that
             * the previous process died and unlock the file manually
             */
            $requires_unlock = ((filemtime($this->get_token_location()) + 60) < (time()));

            // Temporarily workaround when flock is disabled. Can cause problems when plugin is used in multiple processes
            if (false !== strpos(ini_get('disable_functions'), 'flock')) {
                $requires_unlock = false;
            }

            if ($requires_unlock) {
                $this->_unlock_token_file();
            }

            if (flock($this->_get_token_file_handle(), LOCK_SH)) {
                clearstatcache();
                rewind($this->_get_token_file_handle());
                $token = fread($this->_get_token_file_handle(), filesize($this->get_token_location()));
                error_log('[WP Cloud Plugin message]: '.sprintf('New Authorization Token has been received by another process'));
                $this->_client->setToken($access_token);
                $this->_unlock_token_file();

                return $this->_client;
            }
        }

        // Stop if we need to get a new AccessToken but somehow ended up without a refreshtoken
        $refresh_token = $access_token->getRefreshToken();

        if (empty($refresh_token)) {
            error_log('[WP Cloud Plugin message]: '.sprintf('No Refresh Token found during the renewing of the current token. We will stop the authorization completely.'));
            $this->_unlock_token_file();
            $this->revoke_token();

            return false;
        }

        // Refresh token
        try {
            $this->_client->refreshToken($refresh_token);

            // Store the new token
            $new_accesstoken = $this->_client->getToken();
            $this->set_access_token($new_accesstoken);

            $this->_unlock_token_file();

            if (false !== ($timestamp = wp_next_scheduled('letsbox_lost_authorisation_notification'))) {
                wp_unschedule_event($timestamp, 'letsbox_lost_authorisation_notification');
            }
        } catch (\Exception $ex) {
            $this->_unlock_token_file();
            error_log('[WP Cloud Plugin message]: '.sprintf('Cannot refresh Authorization Token'));

            if (!wp_next_scheduled('letsbox_lost_authorisation_notification')) {
                wp_schedule_event(time(), 'daily', 'letsbox_lost_authorisation_notification');
            }

            throw $ex;
        }

        return $this->_client;
    }

    public function set_approval_prompt($approval_prompt = 'none')
    {
        //$this->get_client()->setApprovalPrompt($approval_prompt);
    }

    public function set_logger()
    {
    }

    public function create_access_token()
    {
        $this->get_processor()->reset_complete_cache();

        try {
            $code = $_REQUEST['code'];

            //Fetch the AccessToken
            $this->get_client()->setAuthorizationCode($code);
            $access_token = $this->get_client()->getAccessToken();
            $this->set_access_token($access_token);

            $user = $this->get_client()->getUserInfo();
            $enterprise = $user->getEnterprise();
            $this->set_account_type((empty($enterprise) ? 'personal' : 'business'));
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('Cannot generate Access Token: %s', $ex->getMessage()));

            return new \WP_Error('broke', esc_html__('Error communicating with API:', 'wpcloudplugins').$ex->getMessage());
        }

        return true;
    }

    public function revoke_token()
    {
        try {
            // No Endpoint in the BOX API to revoke tokens
            $this->get_client()->destroyToken($this->get_client()->getToken());
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.$ex->getMessage());
        }

        error_log('[WP Cloud Plugin message]: '.'Lost authorization');

        unlink($this->get_token_location());

        $this->get_processor()->set_setting('userfolder_backend_auto_root', null);
        $this->set_account_type(null);

        $this->get_processor()->reset_complete_cache();

        if (false !== ($timestamp = wp_next_scheduled('letsbox_lost_authorisation_notification'))) {
            wp_unschedule_event($timestamp, 'letsbox_lost_authorisation_notification');
        }

        $this->get_processor()->get_main()->send_lost_authorisation_notification();

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

    public function get_access_token()
    {
        $this->_get_lock();
        clearstatcache();
        rewind($this->_get_token_file_handle());

        $filesize = filesize($this->get_token_location());
        if ($filesize > 0) {
            $token = fread($this->_get_token_file_handle(), filesize($this->get_token_location()));
        } else {
            $token = '';
        }

        $this->_unlock_token_file();

        if (empty($token)) {
            return null;
        }

        // Update function to encrypt tokens
        $token = $this->update_from_plain_token($token);

        return Helpers::decrypt($token);
    }

    public function set_access_token($_access_token)
    {
        // Remove Lost Authorisation message
        if (false !== ($timestamp = wp_next_scheduled('letsbox_lost_authorisation_notification'))) {
            wp_unschedule_event($timestamp, 'letsbox_lost_authorisation_notification');
        }

        if (is_object($_access_token)) {
            $_access_token = json_encode($_access_token->toArray());
        }

        ftruncate($this->_get_token_file_handle(), 0);
        rewind($this->_get_token_file_handle());

        $access_token = Helpers::encrypt($_access_token);
        fwrite($this->_get_token_file_handle(), $access_token);

        return $access_token;
    }

    public function has_access_token()
    {
        $access_token = $this->get_access_token();

        return !empty($access_token);
    }

    public function get_account_type()
    {
        return $this->_account_type;
    }

    public function set_account_type($_account_type)
    {
        $this->_account_type = $_account_type;

        if ($this->get_processor()->is_network_authorized()) {
            $settings = get_site_option('letsbox_network_settings', []);
            $settings['box_account_type'] = $_account_type;
            update_site_option('letsbox_network_settings', $settings);
        }

        return $this->get_processor()->set_setting('box_account_type', $_account_type);
    }

    public function _get_lock($type = LOCK_SH)
    {
        if (!flock($this->_get_token_file_handle(), $type)) {
            /*
             * If the file cannot be unlocked and the last time
             * it was modified was 1 minute, assume that
             * the previous process died and unlock the file manually
             */
            $requires_unlock = ((filemtime($this->get_token_location()) + 60) < (time()));

            // Temporarily workaround when flock is disabled. Can cause problems when plugin is used in multiple processes
            if (false !== strpos(ini_get('disable_functions'), 'flock')) {
                $requires_unlock = false;
            }

            if ($requires_unlock) {
                $this->_unlock_token_file();
            }
            // Try to lock the file again
            flock($this->_get_token_file_handle(), $type);
        }

        return $this->_get_token_file_handle();
    }

    public function get_token_location()
    {
        return $this->_token_location;
    }

    /**
     * @return \TheLion\LetsBox\Processor
     */
    public function get_processor()
    {
        return $this->_processor;
    }

    /**
     * @return \Box\Model\Client\Client
     */
    public function get_client()
    {
        if (empty($this->_client)) {
            $this->_client = $this->start_client();
        }

        return $this->_client;
    }

    public function get_redirect_uri()
    {
        return $this->_redirect_uri;
    }

    public function set_redirect_uri()
    {
        $this->_redirect_uri = admin_url('admin.php?page=LetsBox_settings');
        if ($this->get_processor()->is_network_authorized()) {
            $this->_redirect_uri = network_admin_url('admin.php?page=LetsBox_network_settings');
        }

        return $this->_redirect_uri;
    }

    public function update_from_plain_token($token)
    {
        $decrypted_token = Helpers::decrypt($token);
        if (false === $decrypted_token) {
            return $this->set_access_token($token);
        }

        return $token;
    }

    protected function _unlock_token_file()
    {
        $handle = $this->_get_token_file_handle();
        if (!empty($handle)) {
            flock($this->_get_token_file_handle(), LOCK_UN);
            fclose($this->_get_token_file_handle());
            $this->_set_token_file_handle(null);
        }

        clearstatcache();

        return true;
    }

    protected function _set_token_file_handle($handle)
    {
        return $this->_token_file_handle = $handle;
    }

    protected function _get_token_file_handle()
    {
        if (empty($this->_token_file_handle)) {
            // Check if cache dir is writeable
            // Moving from DB storage to file storage
            if (!file_exists($this->get_token_location())) {
                $token = $this->get_processor()->get_setting('box_app_current_token');
                $this->get_processor()->set_setting('box_app_current_token', null);
                $this->get_processor()->set_setting('box_app_refresh_token', null);
                file_put_contents($this->get_token_location(), $token);
            }

            if (!is_writable($this->get_token_location())) {
                @chmod($this->get_token_location(), 0755);

                if (!is_writable($this->get_token_location())) {
                    error_log('[WP Cloud Plugin message]: '.sprintf('Cache file (%s) is not writable', $this->get_token_location()));

                    exit(sprintf('Cache file (%s) is not writable', $this->get_token_location()));
                }

                file_put_contents($this->get_token_location(), $token);
            }

            $this->_token_file_handle = fopen($this->get_token_location(), 'c+');
            if (!is_resource($this->_token_file_handle)) {
                error_log('[WP Cloud Plugin message]: '.sprintf('Cache file (%s) is not writable', $this->get_token_location()));

                exit(sprintf('Cache file (%s) is not writable', $this->get_token_location()));
            }
        }

        return $this->_token_file_handle;
    }
}
