<?php

/**
 * FRONT Class
 *
 * @package WPOWP
 * @since 2.3
 */

namespace WPOWP\Inc;

use WPOWP\Inc\Traits\Get_Instance;

if ( ! class_exists( 'WPOWP_Front' ) ) {
	class WPOWP_Front {

		use Get_Instance;

		private $settings = '';

		/**
		 * Constructor
		 *
		 * return void
		 */
		public function __construct() {

			$this->settings = WPOWP_Admin::get_instance()->get_settings();
			$this->handle_front( $this->settings );
			// Enqueue Scripts
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_js' ) );
			// Update Order status
			add_action( 'woocommerce_thankyou', array( $this, 'update_order_status' ), 10, 1 );
		}

		/**
		 * Handle Front
		 *
		 * @param  array $settings
		 * @return void
		 */
		public function handle_front( $settings ) {

			if ( ! empty( $settings ) && is_array( $settings ) ) {

				$skip_cart = $settings['skip_cart'];

				if ( true === wc_string_to_bool( $skip_cart ) ) {
					add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'skip_cart' ) );

					add_filter( 'woocommerce_product_single_add_to_cart_text', array( $this, 'cart_btntext' ) );
					add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'cart_btntext' ) );
					add_filter( 'wc_add_to_cart_message_html', '__return_empty_string' );

					add_filter( 'option_woocommerce_enable_ajax_add_to_cart', '__return_false' );
					add_filter( 'woocommerce_get_price_html', array( $this, 'free_product' ), 9999, 2 );
				}

				if ( true === wc_string_to_bool( $settings['free_product'] ) ) {
					add_filter( 'woocommerce_get_price_html', array( $this, 'free_product' ), 9999, 2 );
				}
			}

		}

		public function enqueue_js() {
			$quote_only = get_option( 'wpowp_quote_only', 'no' );

			if ( 'yes' === $quote_only && is_checkout() ) {
				wp_enqueue_script(
					'wpowp-admin',
					WPOWP_URL . 'admin/assets/js/wpowp-front.js',
					array( 'jquery' ),
					null,
					true
				);
			}
		}

		/**
		 * Skip Cart
		 *
		 * @return URL
		 */
		public function skip_cart() {
			return wc_get_checkout_url();
		}

		/**
		 * Cart BtnText
		 *
		 * @return string
		 */
		public function cart_btntext() {
			return ( true === wc_string_to_bool( $this->settings['skip_cart'] ) ) ? $this->settings['add_cart_text'] : '';
		}


		/**
		 * Free Product
		 *
		 * @param  float $price
		 * @param  object $product
		 * @return $price
		 */
		public function free_product( $price, $product ) {

			$free_price_txt = $this->settings['free_product_text'];

			if ( $product->is_type( 'variable' ) ) {

				$prices    = $product->get_variation_prices( true );
				$min_price = current( $prices['price'] );
				if ( 0 === $min_price ) {
					$max_price     = end( $prices['price'] );
					$min_reg_price = current( $prices['regular_price'] );
					$max_reg_price = end( $prices['regular_price'] );
					if ( $min_price !== $max_price ) {
						$price  = wc_format_price_range( $free_price_txt, $max_price );
						$price .= $product->get_price_suffix();
					} elseif ( $product->is_on_sale() && $min_reg_price === $max_reg_price ) {
						$price  = wc_format_sale_price( wc_price( $max_reg_price ), $free_price_txt );
						$price .= $product->get_price_suffix();
					} else {
						$price = $free_price_txt;
					}
				}
			} elseif ( 0 === absint( $product->get_price() ) ) {
				$price = '<span class="woocommerce-Price-amount amount">' . $free_price_txt . '</span>';
			}

			return $price;
		}

		/**
		 * Update Order Status
		 *
		 * @param  int $order_id
		 * @return void
		 */
		public function update_order_status( $order_id ) {

			$order        = new \WC_Order( $order_id );
			$order_status = $order->get_status();

			if ( 'pending' !== $order_status && 'completed' !== $order_status ) {

				$option_order_status = \WPOWP\Inc\WPOWP_Admin::get_instance()->get_settings( 'order_status' );
				$status              = apply_filters( 'wpowp_filter_order_status', wp_kses_post( $option_order_status ) );
				// Update Order status
				$order->update_status( $status );
			}

		}

	}

	WPOWP_Front::get_instance();

}
