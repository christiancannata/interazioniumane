<?php

/**
 * Admin Class
 *
 * @package WPOWP
 * @since 2.3
 */

namespace WPOWP\Inc;

use WPOWP\Inc\Traits\Get_Instance;

if ( ! class_exists( 'WPOWP_Admin' ) ) {
	class WPOWP_Admin {

		use Get_Instance;

		/**
		* Default options
		* @var array
		*/

		private $settings = array();

		public function __construct() {
			$this->default_settings();
			// Add Admin menu
			add_action( 'admin_menu', array( $this, 'menu_admin' ), 9 );
			add_action( 'admin_init', array( $this, 'init_admin' ), 10 );
		}

		public function menu_admin() {
			add_menu_page( WPOWP_SHORT_NAME, WPOWP_SHORT_NAME, 'manage_options', WPOWP_PLUGIN_SLUG, array( $this, 'menu_settings' ), 'dashicons-store', 26 );
		}

		private function default_settings() {
			$this->settings = array(
				'skip_cart'                        => false,
				'order_status'                     => 'processing', // Default order status after place order
				'add_cart_text'                    => __( 'Buy Now', WPOWP_TEXT_DOMAIN ),
				'free_product'                     => false,
				'free_product_text'                => __( 'FREE', WPOWP_TEXT_DOMAIN ),
				'quote_only'                       => false,
				'quote_button_postion'             => 'after_submit',
				'quote_button_text'                => __( 'Qoute Only', WPOWP_TEXT_DOMAIN ),
				'remove_shipping'                  => false,
				'remove_privacy_policy_text'       => false,
				'remove_checkout_terms_conditions' => false,

			);
		}

		public function menu_settings() {

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$default_tab = WPOWP_PLUGIN_SLUG;

			$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $default_tab; // phpcs:ignore
			$tab = 'admin/' . str_replace( 'wpowp-', '', $tab );

			$this->load_admin( $tab );

		}

		private function order_status_list() {
			$order_statuses = wc_get_order_statuses();
			$statuses       = array();
			foreach ( $order_statuses as $key => $status ) {
				$statuses[ str_replace( 'wc-', '', $key ) ] = $status;
			}
			return $statuses;
		}

		/**
		 * Load Admin
		 *
		 * @param  string  $tab
		 * @param  boolean $append_php
		 * @return void
		 */
		public function load_admin( $tab, $append_php = true ) {

			$template_file = ( true === $append_php ) ? WPOWP_TEMPLATES . $tab . '.php' : WPOWP_TEMPLATES . $tab;

			if ( ! file_exists( $template_file ) ) {
				return;
			}

			require_once $template_file; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable

		}

		/**
		 * Get settings
		 *
		 * @return settings
		 */
		public function get_settings( $setting_name = '', $skip_merge = false ) {

			$option = get_option( 'wpowp_settings', true );

			if ( true === $skip_merge ) {
				$this->settings = $option;
			} else {
				$this->settings = wp_parse_args( $option, $this->settings );
			}

			if ( ! empty( $setting_name ) && isset( $this->settings[ $setting_name ] ) ) {
				return $this->settings[ $setting_name ];
			}

			return $this->settings;

		}

		/**
		 * Set Option
		 *
		 * @return array
		 */
		public function set_option( $option_name, $option_value ) {

			if ( ! empty( $option_name ) && ! empty( $option_value ) ) {
				return update_option( $option_name, $option_value );
			}

			return 0;

		}

		public function init_admin() {
			wp_enqueue_style( 'wpowp-toastr', WPOWP_URL . 'admin/assets/css/wpowp-admin.css', array(), array(), false );
			wp_enqueue_script(
				'wpowp-admin-rest',
				WPOWP_URL . 'admin/assets/js/wpowp-admin-rest.js',
				array( 'wp-api' ),
				null,
				true
			);
			wp_enqueue_script(
				'wpowp-toast',
				WPOWP_URL . 'admin/assets/js/wpowp-toastr.min.js',
				array( 'jquery' ),
				null,
				true
			);

		}

	}

	WPOWP_Admin::get_instance();

}
