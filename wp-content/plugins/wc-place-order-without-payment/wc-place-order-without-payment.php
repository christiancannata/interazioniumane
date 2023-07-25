<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://nitin247.com/
 * @since             1.0.0
 * @package           wc_place_order_without_payment
 *
 * @wordpress-plugin
 * Plugin Name:       Place Order Without Payment for WooCommerce
 * Plugin URI:        https://wordpress.org/plugins/wc-place-order-without-payment/
 * Description:       Place Order Without Payment for WooCommerce will allow users to place orders directly.This plugin will customize checkout page and offers to direct place order without payment.
 * Version:           2.5.2
 * Author:            Nitin Prakash
 * Author URI:        https://nitin247.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wpowp
 * Domain Path:       /languages/
 * Requires PHP:      5.6
 * Requires at least: 5.0
 * Tested up to: 6.2
 * WC requires at least: 5.0
 * WC tested up to: 7.8
 */
// If this file is called directly, abort.
namespace WPOWP;

defined( 'WPOWP_FILE' ) or define( 'WPOWP_FILE', __FILE__ );
defined( 'WPOWP_BASE' ) or define( 'WPOWP_BASE', plugin_basename( WPOWP_FILE ) );
defined( 'WPOWP_DIR' ) or define( 'WPOWP_DIR', plugin_dir_path( WPOWP_FILE ) );
defined( 'WPOWP_URL' ) or define( 'WPOWP_URL', plugins_url( '/', WPOWP_FILE ) );
defined( 'WPOWP_VERSION' ) or define( 'WPOWP_VERSION', '2.5.2' );
defined( 'WPOWP_TEXT_DOMAIN' ) or define( 'WPOWP_TEXT_DOMAIN', 'wpowp' );
defined( 'WPOWP_NAME' ) or define( 'WPOWP_NAME', __( 'Place Order Without Payment', WPOWP_TEXT_DOMAIN ) );
defined( 'WPOWP_SHORT_NAME' ) or define( 'WPOWP_SHORT_NAME', __( 'Place Order', WPOWP_TEXT_DOMAIN ) );
defined( 'WPOWP_PLUGIN_SLUG' ) or define( 'WPOWP_PLUGIN_SLUG', 'wpowp-settings' );
defined( 'WPOWP_PLUGIN_PREFIX' ) or define( 'WPOWP_PLUGIN_PREFIX', 'wpowp-' );
defined( 'WPOWP_FORM_PREFIX' ) or define( 'WPOWP_FORM_PREFIX', 'wpowp_' );
defined( 'WPOWP_TEMPLATES' ) or define( 'WPOWP_TEMPLATES', WPOWP_DIR . 'templates/' );
defined( 'WPOWP_API_ERROR_TEXT' ) or define( 'WPOWP_API_ERROR_TEXT', __( 'Error Processing data!', WPOWP_TEXT_DOMAIN ) );

if ( !function_exists( 'WPOWP\\wpowp_fs' ) ) {
    // Create a helper function for easy SDK access.
    function wpowp_fs()
    {
        global  $wpowp_fs ;
        
        if ( !isset( $wpowp_fs ) ) {
            // Activate multisite network integration.
            if ( !defined( 'WP_FS__PRODUCT_4030_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_4030_MULTISITE', true );
            }
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $wpowp_fs = fs_dynamic_init( array(
                'id'             => '4030',
                'slug'           => 'wc-place-order-without-payment',
                'type'           => 'plugin',
                'public_key'     => 'pk_11c5a507e23c860c7e456326363ba',
                'is_premium'     => false,
                'premium_suffix' => 'Pro',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'menu'           => array(
                'slug'       => 'wpowp-settings',
                'first-path' => 'admin.php?page=wpowp-settings&tab=settings',
            ),
                'is_live'        => true,
            ) );
        }
        
        return $wpowp_fs;
    }
    
    // Init Freemius.
    wpowp_fs();
    // Signal that SDK was initiated.
    do_action( 'wpowp_fs_loaded' );
}

if ( !defined( 'WPINC' ) ) {
    die;
}

if ( !class_exists( 'WPOWP\\WPOWP_Loader' ) ) {
    class WPOWP_Loader
    {
        private static  $instance ;
        /**
         * Get Instance
         *
         * @since 2.3
         * @return object initialized object of class.
         */
        public static function get_instance()
        {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        
        /**
         * Constructor
         *
         * @since 2.3
         */
        private function __construct()
        {
            // On plugin init
            add_action( 'init', array( $this, 'before_plugin_load' ) );
            // Add action links
            add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
            // Load dependency files
            add_action( 'plugins_loaded', array( $this, 'load_classes' ) );
        }
        
        /**
         * Before Plugin Load
         *
         * @return void
         */
        public function before_plugin_load()
        {
            
            if ( !class_exists( 'woocommerce' ) ) {
                add_action( 'admin_notices', array( $this, 'wc_not_active' ) );
                return;
            }
            
            // Run plugin
            $this->run_plugin();
        }
        
        /**
         * Run Plugin
         *
         * @return void
         */
        private function run_plugin()
        {
            // Skip Payment based on loic
            $this->skip_payment();
        }
        
        /**
         * Action Links
         *
         * @param  array $links
         * @return array
         */
        public function action_links( $links )
        {
            if ( wpowp_fs()->is_not_paying() ) {
                $links = array_merge( array( '<a target="blank" href="' . esc_url( 'https://nitin247.com/buy-me-a-coffe' ) . '">' . __( 'Donate', 'wpowp' ) . '</a>' ), $links );
            }
            $links = array_merge( array( '<a target="blank" href="' . esc_url( admin_url( 'admin.php?page=wpowp-settings&tab=settings' ) ) . '">' . __( 'Settings', 'wpowp' ) . '</a>', '<a target="blank" href="' . esc_url( 'https://nitin247.com/support/' ) . '">' . __( 'Plugin Support', 'wpowp' ) . '</a>' ), $links );
            return $links;
        }
        
        /**
         * Load Classses
         *
         * @return void
         */
        public function load_classes()
        {
            // Define autoload folders in plugin
            $folders = array( 'inc/traits', 'compatibility', 'inc' );
            if ( wpowp_fs()->is_paying() ) {
                $folders = wp_parse_args( array( 'inc/premium' ), $folders );
            }
            $this->load_recursively( $folders );
        }
        
        /**
         * Load Recursively
         *
         * @param  array $folders
         * @return void
         */
        private function load_recursively( $folders )
        {
            if ( is_array( $folders ) ) {
                foreach ( $folders as $folder ) {
                    foreach ( glob( WPOWP_DIR . "{$folder}/*.php" ) as $filename ) {
                        include_once $filename;
                        // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
                    }
                }
            }
        }
        
        /**
         * WC not active.
         *
         * @return void
         * @since 2.3
         */
        public function wc_not_active()
        {
            $install_url = wp_nonce_url( add_query_arg( array(
                'action' => 'install-plugin',
                'plugin' => 'woocommerce',
            ), admin_url( 'update.php' ) ), 'install-plugin_woocommerce' );
            echo  '<div class="notice notice-error is-dismissible"><p>' ;
            // translators: 1$-2$: opening and closing <strong> tags, 3$-4$: link tags, takes to woocommerce plugin on wp.org, 5$-6$: opening and closing link tags, leads to plugins.php in admin.
            echo  sprintf(
                esc_html__( '%1$sPlace Order without payment for WooCommerce is inactive.%2$s The %3$sWooCommerce plugin%4$s must be active for Place Order without payment for WooCommerce to work. Please %5$sinstall & activate WooCommerce &raquo;%6$s', 'checkout-plugins-stripe-woo' ),
                '<strong>',
                '</strong>',
                '<a href="http://wordpress.org/extend/plugins/woocommerce/">',
                '</a>',
                '<a href="' . esc_url( $install_url ) . '">',
                '</a>'
            ) ;
            echo  '</p></div>' ;
        }
        
        /**
         * Skip Payment.
         *
         * @return void
         * @since 2.3
         */
        public function skip_payment()
        {
            // Check skip payment login
            $quote_only = \WPOWP\Inc\WPOWP_Admin::get_instance()->get_settings( 'quote_only' );
            $quote_btn_pos = \WPOWP\Inc\WPOWP_Admin::get_instance()->get_settings( 'quote_button_postion' );
            
            if ( false === $quote_only ) {
                $this->disable_payment();
            } else {
                add_action( 'woocommerce_review_order_' . $quote_btn_pos, array( $this, 'quote_button' ) );
                add_action( 'wc_ajax_checkout', array( $this, 'disable_payment' ), 0 );
            }
        
        }
        
        /**
         * Disable Payment.
         *
         * @return void
         * @since 2.3
         */
        public function disable_payment()
        {
            $remove_shipping = $quote_btn_text = \WPOWP\Inc\WPOWP_Admin::get_instance()->get_settings( 'remove_shipping' );
            $remove_privacy_policy_text = $quote_btn_text = \WPOWP\Inc\WPOWP_Admin::get_instance()->get_settings( 'remove_privacy_policy_text' );
            $remove_checkout_terms_conditions = $quote_btn_text = \WPOWP\Inc\WPOWP_Admin::get_instance()->get_settings( 'remove_checkout_terms_conditions' );
            add_filter( 'woocommerce_cart_needs_payment', '__return_false' );
            add_filter( 'woocommerce_order_needs_payment', '__return_false' );
            add_filter( 'woocommerce_available_payment_gateways', '__return_empty_array' );
            // Conditionally remove shipping rates
            
            if ( true === filter_var( $remove_shipping, FILTER_VALIDATE_BOOLEAN ) ) {
                add_filter( 'woocommerce_package_rates', '__return_empty_array' );
                add_filter( 'woocommerce_cart_needs_shipping', '__return_false' );
            }
            
            // Remove checkout privacy text
            if ( true === filter_var( $remove_privacy_policy_text, FILTER_VALIDATE_BOOLEAN ) ) {
                remove_action( 'woocommerce_checkout_terms_and_conditions', 'wc_checkout_privacy_policy_text', 20 );
            }
            // Remove checkout terms and condition
            if ( true === filter_var( $remove_checkout_terms_conditions, FILTER_VALIDATE_BOOLEAN ) ) {
                remove_action( 'woocommerce_checkout_terms_and_conditions', 'wc_terms_and_conditions_page_content', 30 );
            }
        }
        
        /**
         * Quote Button
         *
         * @return void()
         */
        public function quote_button()
        {
            $quote_btn_text = \WPOWP\Inc\WPOWP_Admin::get_instance()->get_settings( 'quote_button_text' );
            echo  '<button type="submit" id="wpowp-quote-only" class="button wpowp-quote-only" href="javascript:void(0)">' . esc_html( $quote_btn_text ) . '</button>' ;
        }
        
        public function pre( $array, $stop )
        {
            echo  '<pre>' . print_r( $array ) . '</pre>' ;
            // phpcs:ignore
            if ( 1 == $stop && defined( 'WP_DEBUG' ) && false == WP_DEBUG ) {
                // phpcs:ignore
                wp_die();
            }
        }
    
    }
    // Initiate Loader
    WPOWP_Loader::get_instance();
    if ( !function_exists( 'wpowp_debug' ) ) {
        function wpowp_debug( $array, $stop = 0 )
        {
            WPOWP_Loader::get_instance()->pre( $array, $stop );
        }
    
    }
}
