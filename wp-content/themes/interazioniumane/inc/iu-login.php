<?php

//Rimuovo toolbar per tutti gli utenti
add_action('after_setup_theme', 'remove_admin_bar');
function remove_admin_bar() {
if (!current_user_can('administrator') && !is_admin()) {
  show_admin_bar(false);
}
}

//***** Custom login page *****//

//Modify logo
function modify_logo() {
    $logo_style = '<style type="text/css">';
    $logo_style .= 'h1 a {background-image: url(' . get_template_directory_uri() . '/login/iu-logo.png) !important;}';
    $logo_style .= '</style>';
    echo $logo_style;
}
add_action('login_head', 'modify_logo');

//Logo url
function custom_login_url() {
    //return 'http://www.interazioniumane.it/';
    return 'http://localhost:8888/interazioniumane/';
}
add_filter('login_headerurl', 'custom_login_url');

//Custom link below registration form
function custom_link() {

}
add_action('login_footer','custom_link');


//***** Custom roles *****//
add_role(
  'student',
  __( 'Student' ),
  array(
  'read'         => true,
  'edit_posts'   => false,
  'delete_posts' => false
  )
);


//** Login after registration
function wpf_dev_autologin( $user_id, $fields, $form_data, $userdata ) {

    if ( empty( $userdata['user_login'] ) || empty( $userdata['user_pass'] ) ) {
        return;
    }

    wp_signon(
        array(
            'user_login'    => $userdata['user_login'],
            'user_password' => $userdata['user_pass'],
            'remember'      => false,
        )
    );
    wp_redirect( site_url( '/esplora' ) );
}
add_action( 'wpforms_user_registered', 'wpf_dev_autologin', 10, 4 );


//Rimuovo voci dal menu account utente
add_filter ( 'woocommerce_account_menu_items', 'misha_remove_my_account_links' );
function misha_remove_my_account_links( $menu_links ){

	//unset( $menu_links['edit-address'] ); // Addresses
	//unset( $menu_links['dashboard'] ); // Remove Dashboard
	//unset( $menu_links['payment-methods'] ); // Remove Payment Methods
	//unset( $menu_links['orders'] ); // Remove Orders
	unset( $menu_links['downloads'] ); // Disable Downloads
	//unset( $menu_links['edit-account'] ); // Remove Account details tab
	//unset( $menu_links['customer-logout'] ); // Remove Logout link

  $menu_links['orders'] = __('Candidature', 'textdomain'); // Changing label for orders
  $menu_links['edit-address'] = __('Fatturazione', 'textdomain'); // Changing label for address
  $menu_links['edit-account'] = __('Account', 'textdomain'); // Changing label for account
  $menu_links['customer-logout'] = __('Esci', 'textdomain'); // Changing label for logout

	return $menu_links;
}

/**
 * Change page titles
 */
add_filter( 'the_title', 'custom_account_endpoint_titles' );
function custom_account_endpoint_titles( $title ) {
    global $wp_query;

    if ( isset( $wp_query->query_vars['orders'] ) && in_the_loop() ) {
        return 'Le tue candidature';
    }

    if ( isset( $wp_query->query_vars['edit-address'] ) && in_the_loop() ) {
        return 'Indirizzo di fatturazione';
    }

    if ( isset( $wp_query->query_vars['edit-account'] ) && in_the_loop() ) {
        return 'Il tuo profilo';
    }

    return $title;
}

// Modify Default Gravatar Size
