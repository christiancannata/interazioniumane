<?php
/**
 * Adds WooCommerce catalog sorting options using postmeta, such as custom fields
 * Tutorial: http://www.skyverge.com/blog/sort-woocommerce-products-custom-fields/
**/
function skyverge_add_postmeta_ordering_args( $sort_args ) {

	$orderby_value = isset( $_GET['orderby'] ) ? wc_clean( $_GET['orderby'] ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );
	switch( $orderby_value ) {

		// Name your sortby key whatever you'd like; must correspond to the $sortby in the next function
		case 'end_booking':
			$sort_args['orderby']  = 'meta_value';
			// Sort by meta_value because we're using alphabetic sorting
			$sort_args['order']    = 'desc';
			$sort_args['meta_key'] = 'end_booking';
			// use the meta key you've set for your custom field, i.e., something like "location" or "_wholesale_price"
			break;

		// case 'points_awarded':
		// 	$sort_args['orderby'] = 'meta_value_num';
		// 	// We use meta_value_num here because points are a number and we want to sort in numerical order
		// 	$sort_args['order'] = 'asc';
		// 	$sort_args['meta_key'] = 'points';
		// 	break;

	}

	return $sort_args;
}
add_filter( 'woocommerce_get_catalog_ordering_args', 'skyverge_add_postmeta_ordering_args' );


// Add these new sorting arguments to the sortby options on the frontend
function skyverge_add_new_postmeta_orderby( $sortby ) {

	// Adjust the text as desired
	$sortby['end_booking'] = __( 'Ordina per data di fine iscrizione', 'woocommerce' );
	//$sortby['points_awarded'] = __( 'Sort by points for purchase', 'woocommerce' );

	return $sortby;
}
add_filter( 'woocommerce_default_catalog_orderby_options', 'skyverge_add_new_postmeta_orderby' );
add_filter( 'woocommerce_catalog_orderby', 'skyverge_add_new_postmeta_orderby' );
