<?php

/**
 * REST Class
 *
 * @package WPOWP
 * @since 2.3
 */

namespace WPOWP\Inc;

use WPOWP\Inc\Traits\Get_Instance;

if ( ! class_exists( 'WPOWP_Rest_API' ) ) {
	class WPOWP_Rest_API extends \WP_REST_Controller {

		use Get_Instance;

		protected $namespace = 'wpowp-api';
		protected $rest_base = 'action';
		protected $response  = array(
			'success' => false,
			'message' => WPOWP_API_ERROR_TEXT,
		);

		public function __construct() {
			// Init API Routes
			add_action( 'rest_api_init', array( $this, 'init_admin' ) );
		}

		/**
		 * Init REST API Admin
		 *
		 * @return void
		 */

		public function init_admin() {

			$this->rest_base . '/save-settings';

			register_rest_route(
				$this->namespace,
				$this->rest_base . '/save-settings',
				array(
					array(
						'methods'             => \WP_REST_Server::EDITABLE,
						'callback'            => array( $this, 'save_settings' ),
						'permission_callback' => array( $this, 'get_write_api_permission_check' ),
						'args'                => array(
							'settings' => array(
								'description'       => __( 'Plugin settings', 'funnel-builder' ),
								'type'              => 'json',
								'validate_callback' => 'rest_validate_request_arg',
								'sanitize_callback' => array( $this, 'sanitize_request' ),
							),
						),
					),
				)
			);

		}

		public function save_settings( \WP_REST_Request $request ) {

			$default_settings = \WPOWP\Inc\WPOWP_Admin::get_instance()->get_settings( '', true );
			$posted_data      = $this->sanitize_clean( $request->get_param( 'settings' ) );
			$settings         = wp_parse_args( $posted_data, $default_settings );

			WPOWP_Admin::get_instance()->set_option( 'wpowp_settings', ( $settings ) );

			$this->response['success'] = true;
			$this->response['message'] = __( 'Settings Saved', WPOWP_TEXT_DOMAIN );

			return rest_ensure_response( $this->response );

		}

		public function sanitize_request( $data, $skip_clean = 0 ) {
			return ( 0 === $skip_clean ) ? json_decode( $data, true ) : wc_clean( json_decode( $data, true ) );
		}

		private function sanitize_clean( $posted_data ) {
			$data = array();
			if ( is_array( $posted_data ) ) {
				foreach ( $posted_data as $post_array ) {
					$data[ str_replace( WPOWP_FORM_PREFIX, '', $post_array['name'] ) ] = sanitize_text_field( $post_array['value'] );
				}
				return $data;
			} else {
				return sanitize_text_field( $data );
			}
		}


		/**
		 * Check user has write permission
		 *
		 * @return void
		 */

		public function get_write_api_permission_check() {
			return current_user_can( 'manage_options' ) ? true : false;
		}

	}

	WPOWP_Rest_API::get_instance();

}
