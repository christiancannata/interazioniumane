<?php

namespace TheLion\LetsBox\Integrations;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class Integrations
{
    public static function init()
    {
        // Add Global Form Helpers
        require_once 'FormHelpers.php';
        new FormHelpers();

        // Load integrations
        self::load_automator();
        self::load_contactform7();
        self::load_elementor();
        self::load_divipagebuilder();
        self::load_gravityforms();
        self::load_formidableforms();
        self::load_fluentforms();
        self::load_gravitypdf();
        self::load_gutenberg();
        self::load_woocommcerce();
        self::load_wpforms();
        self::load_advancedcustomfields();
        self::load_beaverbuilder();
    }

    public static function load_automator()
    {
        if (!defined('AUTOMATOR_PLUGIN_VERSION')) {
            return false;
        }

        require_once 'automator/init.php';
    }

    public static function load_contactform7()
    {
        if (!defined('WPCF7_PLUGIN')) {
            return false;
        }

        require_once 'contactform7/init.php';

        new ContactForm();
    }

    public static function load_elementor()
    {
        if (!did_action('elementor/loaded')) {
            return false;
        }

        require_once 'elementor/init.php';
    }

    public static function load_divipagebuilder()
    {
        require_once 'divipagebuilder/init.php';
    }

    public static function load_gravityforms()
    {
        if (!class_exists('GFForms')) {
            return false;
        }

        if (class_exists('GFCommon')) {
            if (version_compare(\GFCommon::$version, '2', '<')) {
                return false;
            }

            if (version_compare(\GFCommon::$version, '2.5', '<')) {
                require_once 'gravityformslegacy/init.php';
            } else {
                require_once 'gravityforms/init.php';
            }
        }
    }

    public static function load_formidableforms()
    {
        if (!class_exists('FrmHooksController')) {
            return false;
        }

        require_once 'formidableforms/init.php';
    }

    public static function load_fluentforms()
    {
        if (!defined('FLUENTFORM')) {
            return false;
        }

        require_once 'fluentforms/init.php';
    }

    public static function load_gravitypdf()
    {
        if (!class_exists('GFForms')) {
            return false;
        }

        require_once 'gravitypdf/init.php';
    }

    public static function load_gutenberg()
    {
        require_once 'gutenberg/init.php';
    }

    public static function load_woocommcerce()
    {
        if (!class_exists('woocommerce')) {
            return false;
        }

        require_once 'woocommerce/init.php';
    }

    public static function load_wpforms()
    {
        if (!defined('WPFORMS_VERSION')) {
            return false;
        }

        require_once 'wpforms/init.php';
    }

    public static function load_advancedcustomfields()
    {
        if (!class_exists('ACF')) {
            return false;
        }

        require_once 'advancedcustomfields/init.php';
    }

    public static function load_beaverbuilder()
    {
        if (!class_exists('FLBuilder')) {
            return;
        }

        require_once 'beaverbuilder/init.php';
    }
}

Integrations::init();
