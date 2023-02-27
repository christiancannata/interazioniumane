<?php

/*
    Plugin Name: Disable cart page for WooCommerce
    Plugin URI: https://code4life.it/shop/plugins/disable-cart-page-for-woocommerce/
    Description: Disable cart page and redirect to checkout for each purchase.
    Author: Code4Life
    Author URI: https://code4life.it/
    Version: 1.2.5
    Text Domain: disable-cart-page-for-woocommerce
 	Domain Path: /i18n/
	License: GPLv3
	License URI: http://www.gnu.org/licenses/gpl-3.0.html

    WC requires at least: 2.0
    WC tested up to: 6.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Function to execute on plugin activation
register_activation_hook( __FILE__, function() {
	if ( ! current_user_can( 'activate_plugins' ) ) { return; }

    $plugin = isset( $_REQUEST[ 'plugin' ] ) ? $_REQUEST[ 'plugin' ] : null;
    check_admin_referer( 'activate-plugin_' . $plugin );

    /* Code here */
} );

// Function to execute on plugin deactivation
register_deactivation_hook( __FILE__, function() {
	if ( ! current_user_can( 'activate_plugins' ) ) { return; }

    $plugin = isset( $_REQUEST[ 'plugin' ] ) ? $_REQUEST[ 'plugin' ] : null;
    check_admin_referer( 'deactivate-plugin_' . $plugin );

    /* Code here */
} );

// Add language support to internationalize plugin
add_action( 'init', function() {
	load_plugin_textdomain( 'disable-cart-page-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/' );
} );

// Add link to configuration page into plugin
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function( $links ) {
	return array_merge( array(
		'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=wcdcp' ) . '">' . __( 'Settings', 'disable-cart-page-for-woocommerce' ) . '</a>'
	), $links );
} );

function wcdcp_fields() {
    $fields = array(
        'section_title-enable' => array(
            'name'     => __( 'Disable cart page', 'disable-cart-page-for-woocommerce' ),
            'type'     => 'title',
            'desc'     => __( 'Enabling this option will delete the cart page and for each purchase customers will be redirected directly to the checkout. They will also be able to buy one type of product at a time.', 'disable-cart-page-for-woocommerce' )
        ),
        'wcdcp_enable' => array(
            'name' => __( 'Enable', 'disable-cart-page-for-woocommerce' ),
            'type' => 'checkbox',
            'desc' => __( 'Disable cart page and redirect directly to checkout', 'disable-cart-page-for-woocommerce' ),
            'id'   => 'wcdcp_enable'
        ),
        'section_end-enable' => array(
             'type' => 'sectionend'
        )
    );

    return apply_filters( 'wcdcp_fields', $fields );
}

// Add settings tab to WooCommerce options
add_filter( 'woocommerce_settings_tabs_array', function( $tabs ) {
    $tabs['wcdcp'] = __( 'Disable cart page', 'disable-cart-page-for-woocommerce' );
    
    return $tabs;
}, 50 );

// Add settings to the new tab
add_action( 'woocommerce_settings_tabs_wcdcp', function() {
    woocommerce_admin_fields( wcdcp_fields() );
} );

// Save settings
add_action( 'woocommerce_update_options_wcdcp', function() {
    woocommerce_update_options( wcdcp_fields() );
} );



/*** IF PLUGIN IS ENABLED ***/
if ( get_option( 'wcdcp_enable' ) == 'yes' ) {

    // Remove cart button from mini-cart
    remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_button_view_cart', 10 );

    // Add checks and notices
    add_action( 'admin_notices', function() {
        if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            ?><div class="notice notice-error"><p><?php _e( 'Warning! To use Disable cart page for WooCommerce it need WooCommerce is installed and active.', 'disable-cart-page-for-woocommerce' ); ?></p></div><?php
        }
    } );

    // Force WooCommerce to redirect after product added to cart
    add_filter( 'pre_option_woocommerce_cart_redirect_after_add', function( $pre_option ) {
        return 'yes';
    } );

    add_filter( 'woocommerce_product_settings', function( $fields ) {
        foreach ( $fields as $key => $field ) {
            if ( $field['id'] === 'woocommerce_cart_redirect_after_add' ) {
                $fields[$key]['custom_attributes'] = array(
                    'disabled' => true
                );
            }
        }
        return $fields;
    }, 10, 1 );

    // Empty cart when product is added to cart, so we can't have multiple products in cart
    add_action( 'woocommerce_add_cart_item_data', function( $cart_item_data ) {
        wc_empty_cart();
        return $cart_item_data;
    } );

    // When add a product to cart, redirect to checkout
    add_action( 'woocommerce_init', function() {
        if ( version_compare( WC_VERSION, '3.0.0', '<' ) ) {
            add_filter( 'add_to_cart_redirect', function() {
                return wc_get_checkout_url();
            } );
        } else {
            add_filter( 'woocommerce_add_to_cart_redirect', function() {
                return wc_get_checkout_url();
            } );
        }
    } );

    // Remove added to cart message
    add_filter( 'wc_add_to_cart_message_html', '__return_null' );

    // If someone reaches the cart page, redirect to checkout permanently
    add_action( 'template_redirect', function() {
        if ( ! is_cart() ) { return; }
        if ( WC()->cart->get_cart_contents_count() == 0 ) {
            wp_redirect( apply_filters( 'wcdcp_redirect', wc_get_page_permalink( 'shop' ) ) );
            exit;
        }

        // Redirect to checkout page
        wp_redirect( wc_get_checkout_url(), '301' );
        exit;
    } );

    // Change add to cart button text ( in loop )
    add_filter( 'add_to_cart_text', function() {
        return __( 'Buy now', 'disable-cart-page-for-woocommerce' );
    } );

    // Change add to cart button text ( in product page )
    add_filter( 'woocommerce_product_single_add_to_cart_text', function() {
        return __( 'Buy now', 'disable-cart-page-for-woocommerce' );
    } );

    // Clear cart if there are errors
    add_action( 'woocommerce_cart_has_errors', function() {
        wc_empty_cart();
    } );

}