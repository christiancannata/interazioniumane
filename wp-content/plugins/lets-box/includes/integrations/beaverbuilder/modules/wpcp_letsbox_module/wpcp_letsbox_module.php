<?php

namespace TheLion\LetsBox\Integrations;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class FL_WPCP_LetsBox_Module extends \FLBuilderModule
{
    public function __construct()
    {
        parent::__construct([
            'name' => 'Box',
            'description' => sprintf(\esc_html__('Insert your %s content', 'wpcloudplugins'), 'Box'),
            'category' => 'WP Cloud Plugins',
            'dir' => LETSBOX_ROOTDIR.'/includes/integrations/beaverbuilder/modules/wpcp_letsbox_module/',
            'url' => LETSBOX_ROOTPATH.'/includes/integrations/beaverbuilder/modules/wpcp_letsbox_module/',
            'icon' => LETSBOX_ROOTDIR.'/css/images/box_logo.svg',
        ]);
    }

    public function get_icon($icon = '')
    {
        return file_get_contents($icon);
    }

    public function enqueue_scripts(){

        \TheLion\LetsBox\Core::instance()->load_scripts();
        \TheLion\LetsBox\Core::instance()->load_styles();

        wp_enqueue_script('WPCloudplugin.Libraries');
        wp_enqueue_script('LetsBox.ShortcodeBuilder');
        wp_enqueue_style('LetsBox');
    }
}

// Register the module and its form settings.
\FLBuilder::register_module('\TheLion\LetsBox\Integrations\FL_WPCP_LetsBox_Module', [
    'general' => [ // Tab
        'title' => esc_html__('General'), // Tab title
        'sections' => [ // Tab Sections
            'general' => [ // Section
                'title' => esc_html__('Module configuration', 'wpcloudplugins'), // Section Title
                'fields' => [ // Section Fields
                    'raw_shortcode' => [
                        'type' => 'wpcp_letsbox',
                        'label' => esc_html__('Raw shortcode', 'wpcloudplugins'),
                        'default' => '[letsbox mode="files"]',
                    ],
                ],
            ],
        ],
    ],
]);
