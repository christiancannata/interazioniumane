<?php
/**
 *
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2022, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\LetsBox;

class WebHook
{
    /**
     * Array of events that need to be send.
     *
     * @var array
     */
    public static $events = [];

    /**
     * Receiving webhook url.
     *
     * @var string
     */
    public static $endpoint;

    /**
     * The single instance of the class.
     *
     * @var WebHook
     */
    private static $_instance;

    /**
     * WebHook Instance.
     *
     * Ensures only one instance is loaded or can be loaded.
     *
     * @return WebHook - WebHook instance
     *
     * @static
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
            add_action('shutdown', [__CLASS__, 'send']);
        }

        return self::$_instance;
    }

    public static function add($type, $description = '', $data = [], $user_id = null)
    {
        $event = [
            'timestamp' => date('c'), // ISO 8601 date (2004-02-12T15:19:21+00:00)
            'type' => $type,
            'description' => trim($description),
            'data' => $data,
            'user' => self::get_user($user_id),
            'page' => Helpers::get_page_url(),
        ];

        $event = apply_filters('letsbox_webhook_add_data', $event, self::instance());

        self::$events[] = $event;
    }

    public static function send()
    {
        $endpoint = self::get_endpoint();

        if (empty($endpoint) || false === filter_var($endpoint, FILTER_VALIDATE_URL)) {
            return false;
        }

        if (empty(self::$events)) {
            return false;
        }

        $body = json_encode(
            [
                'total' => count(self::$events),
                'events' => self::$events,
            ]
        );

        $options = [
            'body' => $body,
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8',
                'X-WPCP-TIMESTAMP' => time(),
                'x-WPCP-SIGNATURE' => self::generate_signature($body),
            ],
            'data_format' => 'body',
            'method' => 'POST',
            'blocking' => false,
        ];

        $options = apply_filters('letsbox_webhook_options', $options, self::instance());

        do_action('letsbox_webhook_before_send', self::get_endpoint(), self::$events, self::instance());

        $result = wp_remote_post(self::get_endpoint(), $options);

        if (is_wp_error($result)) {
            error_log('[WP Cloud Plugin message]: '.sprintf('Webhook error on line %s: %s', __LINE__, $result->get_error_message()));
        }

        do_action('letsbox_webhook_after_send', self::get_endpoint(), self::$events, self::instance());

        return $result;
    }

    /**
     * Get the user object involved.
     *
     * @param null|int $user_id
     *
     * @return self
     */
    public static function get_user($user_id = null)
    {
        if (empty($user_id)) {
            $user_id = get_current_user_id();
        }

        $user = get_user_by('id', $user_id);

        if (empty($user)) {
            return [];
        }

        $user_data = $user->to_array();
        unset($user_data['user_pass'], $user_data['user_activation_key'], $user_data['user_status']);

        return $user_data;
    }

    /**
     * Get receiving webhook url.
     *
     * @return string
     */
    public static function get_endpoint()
    {
        if (empty(self::$endpoint)) {
            return Core::get_setting('webhook_endpoint_url');
        }

        return self::$endpoint;
    }

    /**
     * Set receiving webhook url.
     *
     * @param string $endpoint receiving webhook url
     */
    public static function set_endpoint($endpoint)
    {
        self::$endpoint = filter_var(trim($endpoint), FILTER_SANITIZE_URL);
    }

    private static function generate_signature($payload)
    {
        $challenge = hash('sha256', time().';'.Core::get_setting('webhook_endpoint_secret'));

        return 'sha256='.hash_hmac('sha256', $payload, $challenge);
    }
}