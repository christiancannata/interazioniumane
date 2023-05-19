<?php

namespace TheLion\LetsBox\Integrations;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class ACF
{
    // vars
    public $settings;

    public function __construct()
    {
        $this->settings = [
            'version' => '1.0.0',
            'url' => plugin_dir_url(__FILE__),
            'path' => plugin_dir_path(__FILE__),
        ];

        // include field
        add_action('acf/include_field_types', [$this, 'include_field']); // v5

        // Add support for placeholders
        add_filter('letsbox_apply_placeholders', [$this, 'add_placeholders'], 10, 3);
    }

    public function include_field($version = false)
    {
        include_once 'fields/class-ACF_LetsBox_Field-v'.$version.'.php';
    }

    public function add_placeholders($value, $context, $extra)
    {
        // Placeholders (%acf_user_{field_name}%, %acf_post_{field_name}% )
        preg_match_all('/%acf_(?<kind>.+)_(?<name>.+)%/U', $value, $acf_placeholders, PREG_SET_ORDER, 0);

        if (!empty($acf_placeholders)) {
            foreach ($acf_placeholders as $acf_placeholder_data) {
                $acf_placeholder = $acf_placeholder_data[0];
                $acf_post_id = false;

                switch ($acf_placeholder_data['kind']) {
                    case 'user':
                        $user_data = $extra['user_data'];
                        $acf_post_id = "user_{$user_data->ID}";

                        break;

                    case 'post':
                    default:
                        if ($context instanceof \TheLion\LetsBox\Processor && !is_null($context->get_shortcode_option('post_id'))) {
                            $acf_post_id = $context->get_shortcode_option('post_id');
                        }

                        break;
                }

                $acf_field_value = get_field($acf_placeholder_data['name'], $acf_post_id);
                $value = strtr($value, [
                    $acf_placeholder => !empty($acf_field_value) ? $acf_field_value : '',
                ]);
            }
        }

        return $value;
    }
}

new \TheLion\LetsBox\Integrations\ACF();
