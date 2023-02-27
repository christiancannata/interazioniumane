<?php

//Lunghezza riassunto
function wpdocs_custom_excerpt_length( $length ) {
    return 20;
}
add_filter( 'excerpt_length', 'wpdocs_custom_excerpt_length', 999 );

function wpshout_change_and_link_excerpt( $more ) {
	if ( is_admin() ) {
		return $more;
	}

	return '&hellip;';
 }
 add_filter( 'excerpt_more', 'wpshout_change_and_link_excerpt', 999 );



//Infinite scroll
function misha_my_load_more_scripts() {

	global $wp_query;

  wp_register_script( 'my_loadmore', get_stylesheet_directory_uri() . '/js/myloadmore.js', array('jquery') );

	// you can define variables directly in your HTML but I decided that the most proper way is wp_localize_script()
	wp_localize_script( 'my_loadmore', 'misha_loadmore_params', array(
		'ajaxurl' => site_url() . '/wp-admin/admin-ajax.php', // WordPress AJAX
		'posts' => json_encode( $wp_query->query_vars ), // everything about your loop is here
		'current_page' => get_query_var( 'paged' ) ? get_query_var('paged') : 1,
		'max_page' => $wp_query->max_num_pages
	) );

 	wp_enqueue_script( 'my_loadmore' );
}

add_action( 'wp_enqueue_scripts', 'misha_my_load_more_scripts' );


function misha_loadmore_ajax_handler(){

	// prepare our arguments for the query
	$args = json_decode( stripslashes( $_POST['query'] ), true );
	$args['paged'] = $_POST['page'] + 1; // we need next page to be loaded
	$args['post_status'] = 'publish';

	// it is always better to use WP_Query but not here
	query_posts( $args );

	if( have_posts() ) :

		// run the loop
		while( have_posts() ): the_post();

      get_template_part( 'loop-templates/content-archive', get_post_format() );
			// for the test purposes comment the line above and uncomment the below one
			//the_title();

		endwhile;

	endif;
	die; // here we exit the script and even no wp_reset_query() required!
}

add_action('wp_ajax_loadmore', 'misha_loadmore_ajax_handler'); // wp_ajax_{action}
add_action('wp_ajax_nopriv_loadmore', 'misha_loadmore_ajax_handler'); // wp_ajax_nopriv_{action}
