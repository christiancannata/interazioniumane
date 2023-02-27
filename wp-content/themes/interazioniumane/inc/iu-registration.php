<?php

//***** Registrazione in pagina *****//
add_action( 'bp_core_screen_signup', 'buddydev_redirect_on_signup' );

/**
* If the signup form is being processed, Redirect to the page where the signup form is
*
*/
function buddydev_redirect_on_signup() {

// Bail if not a POST action
    if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
        return;

    $bp = buddypress();

    //only if bp signup object is set
    if( ! empty( $bp->signup ) ) {
        //save the signup object and submitted post data
        $_SESSION['buddydev_signup'] = $bp->signup;
        $_SESSION['buddydev_signup_fields'] = $_POST;

    }
    bp_core_redirect( wp_get_referer());
}

add_action( 'bp_init', 'buddydev_process_signup_errors' );

function buddydev_process_signup_errors() {

    //we don't need to process if the user is logged in
    if( is_user_logged_in() )
    return;

    //if session was not started by another code, let us begin the session
    if( ! session_id() )
        session_start ();

    //check if the current request
    if( ! empty( $_SESSION['buddydev_signup'] ) ) {

        $bp = buddypress();
        //restore the old signup object
        $bp->signup = $_SESSION['buddydev_signup'];

        //we are sure that it is our redirect from the buddydev_redirect_on_signup function, so we can safely replace the $_POST array
        if( isset( $bp->signup->errors ) && !empty( $bp->signup->errors ) )
            $_POST = $_SESSION['buddydev_signup_fields'];//we need to restore so that the signup form can show the old data

        $errors = array();

        if( isset( $bp->signup->errors ) )
            $errors = $bp->signup->errors;

        foreach ( (array) $errors as $fieldname => $error_message ) {

            add_action( 'bp_' . $fieldname . '_errors', create_function( '', 'echo apply_filters(\'bp_members_signup_error_message\', "<div class=\"error\">" . stripslashes( \'' . addslashes( $error_message ) . '\' ) . "</div>" );' ) );

        }
        //remove from session
        $_SESSION['buddydev_signup'] = null;
        $_SESSION['buddydev_signup_fields'] = null;
    }
}



/**
 * Pre-populate Woocommerce checkout fields
 * Note that this filter populates shipping_ and billing_ fields with a different meta field eg 'first_name'
 */
add_filter('woocommerce_checkout_get_value', function($input, $key ) {

	global $current_user;

	switch ($key) :
		case 'billing_first_name':
		case 'shipping_first_name':
			return $current_user->first_name;
		break;

		case 'billing_last_name':
		case 'shipping_last_name':
			return $current_user->last_name;
		break;

		case 'billing_email':
			return $current_user->user_email;
		break;

		case 'billing_phone':
			return $current_user->phone;
		break;

	endswitch;

}, 10, 2);



/**
 * Dynamically pre-populate Woocommerce checkout fields with exact named meta field
 * Eg. field 'shipping_first_name' will check for that exact field and will not fallback to any other field eg 'first_name'
 *
 */
add_filter('woocommerce_checkout_get_value', function($input, $key) {

	global $current_user;

	// Return the user property if it exists, false otherwise
	return ($current_user->$key
		? $current_user->$key
		: false
	      	);
}, 10, 2);
