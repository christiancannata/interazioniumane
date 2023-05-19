<?php
/**
 * High-Performance Order Storage Class
 *
 * @package WPOWP
 * @since 2.3
 */

namespace WPOWP\Compatibility;

use WPOWP\Inc\Traits\Get_Instance;

/**
 * WooCommerce HPOS Compatibility Class
 */

if ( ! class_exists( 'WPOWP_HPOS_Compatibility' ) ) {
    
	class WPOWP_HPOS_Compatibility {

		use Get_Instance;

		public function __construct() {
			add_action( 'before_woocommerce_init', array( $this, 'declare_compatibility' ) );
		}

		public function declare_compatibility() {
			if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', WPOWP_FILE, true );
			}
		}

	}

    WPOWP_HPOS_Compatibility::get_instance();
}
