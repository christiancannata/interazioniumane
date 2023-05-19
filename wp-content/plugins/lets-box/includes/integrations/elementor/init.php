<?php

namespace TheLion\LetsBox\Integrations;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Elementor block with live preview.
 */
class Elementor
{
    public const VERSION = \LETSBOX_VERSION;
    public const MINIMUM_ELEMENTOR_VERSION = '3.5.0';
    public const MINIMUM_PHP_VERSION = '7.4';

    private static $_instance;

    public function __construct()
    {
        // Add Plugin actions
        \add_action('elementor/elements/categories_registered', [$this, 'add_elementor_category']);
        \add_action('elementor/widgets/register', [$this, 'init_widgets']);
    }

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function add_elementor_category($elements_manager)
    {
        $elements_manager->add_category(
            'wpcloudplugins',
            [
                'title' => 'WP Cloud Plugins',
                'icon' => 'fa fa-plug',
            ]
        );
    }

    /*
    * Init Widgets
    *
    * Include widgets files and register them
    */

    public function init_widgets()
    {
        // Include Widget files
        require_once __DIR__.'/widget.php';

        // Register widget
        \Elementor\Plugin::instance()->widgets_manager->register(new \TheLion\LetsBox\Integrations\Elementor\Widget());
    }
}

\TheLion\LetsBox\Integrations\Elementor::instance();
