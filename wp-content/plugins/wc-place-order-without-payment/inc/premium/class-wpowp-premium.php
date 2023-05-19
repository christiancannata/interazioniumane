<?php

/**
 * Premium Class
 *
 * @package WPOWP
 * @since 2.3
 */

 namespace WPOWP\Inc\Premium;

 use WPOWP\Inc\Traits\Get_Instance;

if ( ! class_exists( 'WPOWP_Premium' ) ) {
	class WPOWP_Premium {

		use Get_Instance;

		public function __construct() {
			// Nothing to do here
		}

		public function on_activate() {
			deactivate_plugins( '/wc-place-order-without-payment/wc-place-order-without-payment.php', true );
		}

	}

	WPOWP_Premium::get_instance();

}

register_activation_hook( __FILE__, array( 'WPOWP_Premium', 'on_activate' ) );
