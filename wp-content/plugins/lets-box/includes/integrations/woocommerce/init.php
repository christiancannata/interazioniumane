<?php

namespace TheLion\LetsBox\Integrations;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

function load_woocommerce_addon($integrations)
{
    global $woocommerce;

    if (is_object($woocommerce) && version_compare($woocommerce->version, '3.0', '>=')) {
        $integrations[] = __NAMESPACE__.'\WooCommerce';
    }

    return $integrations;
}

add_filter('woocommerce_integrations', '\TheLion\LetsBox\Integrations\load_woocommerce_addon', 10);

class WooCommerce extends \WC_Integration
{
    public function __construct()
    {
        $this->id = 'letsbox-woocommerce';
        $this->method_title = 'WooCommerce Box';
        $this->method_description = esc_html__('Easily add downloadable products right from the cloud.', 'wpcloudplugins').' '
                .sprintf(esc_html__('To be able to use this integration, you only need to link your %s Account to the plugin on the %s.', 'wpcloudplugins'), 'Box', '<a href="'.admin_url('admin.php?page=LetsBox_settings').'">Lets-Box settings page</a>');

        // Add Filter to remove the default 'Guest - ' part from the Private Folder name
        add_filter('letsbox_private_folder_name_guests', [$this, 'rename_private_folder_for_guests']);

        // Update shortcodes with Product ID/Order ID when available
        add_filter('letsbox_shortcode_add_options', [$this, 'update_shortcode'], 10, 3);

        if (defined('DOING_AJAX')) {
            if (!isset($_REQUEST['action']) || false === strpos($_REQUEST['action'], 'letsbox')) {
                return false;
            }
        }

        include_once __DIR__.'/wpcp-class-wc-uploads.php';

        include_once __DIR__.'/wpcp-class-wc-downloads.php';

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();
    }

    public function rename_private_folder_for_guests($private_folder_name)
    {
        return str_replace(esc_html__('Guests', 'wpcloudplugins').' - ', '', $private_folder_name);
    }

    public function update_shortcode($options, $processor, $raw_shortcode)
    {
        if (isset($raw_shortcode['wc_order_id'])) {
            $options['wc_order_id'] = $raw_shortcode['wc_order_id'];
        }

        if (isset($raw_shortcode['wc_product_id'])) {
            $options['wc_product_id'] = $raw_shortcode['wc_product_id'];
        }

        if (isset($raw_shortcode['wc_item_id'])) {
            $options['wc_item_id'] = $raw_shortcode['wc_item_id'];
        }
        
        return $options;
    }
}
