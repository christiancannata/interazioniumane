<?php
/**
 * Plugin Name: User Avatar For Woocommerce
 * Plugin URI: https://4wp.it/user-avatar-for-woocommerce
 * Description: Aggiunge un semplice avatar al tuo woocommerce riguardo i clienti e al tuo wordpress riguardo l'admin.
 * Version: 1.0.1
 * Author: 4wpbari
 * Author URI: https://4wp.it/roberto-bottalico/
 * Developer: Roberto Bottalico
 * Text Domain: 4wp
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 **/

// instantiate it
$User_Avatar_For_Woocommerce = new User_Avatar_For_Woocommerce();

class User_Avatar_For_Woocommerce
{
	/**
	 * Name of the plugin
	 * @var string
	 */
	public $plugin_name;

	/**
	 * Slugified version of plugin name
	 * @var string
	 */
	public $plugin_slug;

	/**
	 * Array of tabs for the settings page
	 * @var array
	 */
	public $tabs;

	/**
	 * Array of option names (one per tab) for storing the settings on each tab.
	 * Each corresponds to an "option_name" key in the "wp_options" table.
	 * @var array
	 */
	public $options;


	public function __construct()
	{
		// setup
		$this->init();

		// add actions and filters
		$this->add_hooks();
	}

	/**
	 * Setup procedures
	 *
	 * Put stuff here that needs to happen before any hooks fire, or that can
	 * be done without a hook (e.g. define properties, add shortcodes, etc.)
	 */
	public function init()
	{
		// define slugs
		$this->plugin_name = 'Avatar Woocommerce';
		$this->plugin_slug = str_replace(' ', '_', strtolower($this->plugin_name));

		$this->define_tabs();
		$this->add_options();
	}

	/**
	 * Defines tabs for the settings page
	 */
	public function define_tabs()
	{
		$this->tabs = array(
			array(
				'id' => 'impostazioni',
				'title' => 'Impostazioni',
				'has_fields' => true,
			),
		);
	}

	/**
	 * Adds options to the database to store the settings
	 */
	public function add_options()
	{
		foreach ($this->tabs as $tab) {
			// skip if no fields to save
			if (empty($tab['has_fields'])) {
				continue;
			}
			// define option name
			$this->options[$tab['id']] = sprintf('%s_%s', $this->plugin_slug, $tab['id']);
			// add it to the database
			add_option($this->options[$tab['id']]);
		}
	}

	/**
	 * Adds action and filter hooks
	 *
	 * This is the control center. Use hooks to call other methods. Everything
	 * below this point can be called from a hook.
	 */
	public function add_hooks()
	{
		add_action('admin_menu', array($this, 'add_settings_page'));
		add_action('admin_init', array($this, 'register_settings'));
	}

	/**
	 * Adds a top-level menu page for our plugin settings
	 */
	public function add_settings_page()
	{
		add_menu_page(
			$this->plugin_name // page title
			, $this->plugin_name // menu title
			, 'activate_plugins'// capability
			, $this->plugin_slug // menu slug
			, array($this, 'render_settings_page') // function
			, null // icon url
			, null // position
		);
	}

	/**
	 * Registers settings (one for each tab)
	 */
	public function register_settings()
	{
		foreach ($this->tabs as $tab) {
			// skip if no fields to save
			if (empty($tab['has_fields'])) {
				continue;
			}

			// register setting
			register_setting(
				$this->options[$tab['id']] // option group
				, $this->options[$tab['id']] // option name
			);

			// add sections
			$this->add_sections($tab['id']);
		}
	}

	/**
	 * Adds sections to each tab
	 *
	 * @param string $tab - tab id
	 */
	public function add_sections($tab)
	{
		$sections = array();

		switch ($tab) {

			case 'impostazioni':
				$sections = array(
					array(
						'id' => 'sezione_impostazioni',
						'title' => 'Sezione Impostazioni',
					),

				);
				break;
		}

		if (!empty($sections)) {
			foreach ($sections as &$section) {
				// add page to section (we'll need it when adding fields)
				$section['page'] = $this->options[$tab];

				// add section
				extract($section);
			    add_settings_section(
			    	$id // id
			    	, $title // title
			    	, array($this, 'section_text') // callback
			    	, $page // page
		    	);

				// add fields to that section
				$this->add_fields($section);
			}
		}
	}



	/**
	 * Adds fields to each section
	 *
	 * @param array $section - array of section data defined in add_sections()
	 */
	public function add_fields($section)
	{
		$fields = array();
		$option_name = $section['page'];

		switch ($section['id']) {

			case 'sezione_impostazioni':
				$fields = array(
					array(
		    			'id' => 'avatar_woocommerce_checkbox',
		    			'title' => 'Checkbox',
			    		'type' => 'checkbox',
		    			'label_text' => 'Attiva l\'avatar su woocommerce nel "il mio account". ',
					),
					array(
		    			'id' => 'mobile_menu_woocommerce_checkbox',
		    			'title' => 'Checkbox',
			    		'type' => 'checkbox',
		    			'label_text' => 'Attiva "il mio account" versione mobile , inserendo prima il contenuto e poi il menù.',
					),

				);
				break;

		}

		if (!empty($fields)) {
			foreach ($fields as &$field) {
				// add option name to field (we'll need it when rendering the field)
				$field['option_name'] = $option_name;

				// add each field the the section
				extract($field);
				add_settings_field(
					$id // id
					, $title // title
					, array($this, 'render_settings_field') // callback
					, $section['page'] // page
					, $section['id'] // section
					, $field // args
				);
			}
		}
	}

	/**
	 * Outputs the HTML for each form field
	 *
	 * @param array $field - array of field data defined in add_fields()
	 */
	public function render_settings_field($field)
	{
		// get option from database
		extract($field);
		$option = get_option($option_name);

		// get field name and value
		$field_name = sprintf('%s[%s]', $option_name, $id);
		$field_value = (!empty($option[$id])) ? $option[$id] : '';

		// render based on type
		switch ($type) {

			case 'checkbox':
				printf('<label for="%s">', $field_name);
					printf('<input type="hidden" name="%s" value="0" />', $field_name); // save even if unchecked
					printf('<input type="checkbox" name="%s" id="%s" value="1" %s/>', $field_name, $field_name, checked($field_value, 1, false));
				printf('<span>%s</span></label>', $label_text);
				break;

		}
	}

	/**
	 * Outputs the HTML for the settings page
	 */
	public function render_settings_page()
	{
		// default to first tab
		$active_tab = (!empty($_GET['tab'])) ? $_GET['tab'] : $this->tabs[0]['id'];

		// page contents
		echo '<div class="wrap">';
			// heading
			echo '<div class="col-plugin-icon icon32"></div>';
			printf('<h2>%s</h2>', $this->plugin_name);
			// tabs
			echo '<h2 class="nav-tab-wrapper">';
				foreach ($this->tabs as $tab) {
					$tab_class = ($tab['id'] == $active_tab) ? ' nav-tab-active' : '';
					printf('<a href="?page=%s&tab=%s" class="nav-tab%s">%s</a>', $this->plugin_slug, $tab['id'], $tab_class, $tab['title']);
				}
			echo '</h2>';
			// tab contents
			switch ($active_tab) {

				// output the settings form
				default:
					settings_errors();
					echo '<form action="options.php" method="post">';
						settings_fields($this->options[$active_tab]);
				        do_settings_sections($this->options[$active_tab]);
						submit_button();
					echo '</form>';
					break;
			}
		echo '</div>';
	}

}



//funzionalità dell avatar
class general_user_avatars {

	/**
	 * User ID
	 *
	 * @since 1.0.0
	 * @var int
	 */
	private $user_id_being_edited;

	/**
	 * Initialize all the things
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Text domain
		$this->load_textdomain();

		// Actions
		add_action( 'admin_init',                array( $this, 'admin_init'               )        );
		add_action( 'show_user_profile',         array( $this, 'edit_user_profile'        )        );
		add_action( 'edit_user_profile',         array( $this, 'edit_user_profile'        )        );
		add_action( 'personal_options_update',   array( $this, 'edit_user_profile_update' )        );
		add_action( 'edit_user_profile_update',  array( $this, 'edit_user_profile_update' )        );

		// Shortcode
		add_shortcode( 'general-user-avatars',     array( $this, 'shortcode'                )        );

		// Filters
		add_filter( 'get_avatar',                array( $this, 'get_avatar'               ), 10, 5 );
		add_filter( 'avatar_defaults',           array( $this, 'avatar_defaults'          )        );
	}

	/**
	 * Loads the plugin language files.
	 *
	 * @since 1.0.1
	 */
	public function load_textdomain() {
		$domain = 'general-user-avatars';
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Start the admin engine.
	 *
	 * @since 1.0.0
	 */
	public function admin_init() {

		// Register/add the Discussion setting to restrict avatar upload capabilites
		register_setting( 'discussion', 'general_user_avatars_caps', array( $this, 'sanitize_options' ) );
		add_settings_field( 'general-user-avatars-caps', esc_html__( 'Local Avatar Permissions', 'general-user-avatars' ), array( $this, 'avatar_settings_field' ), 'discussion', 'avatars' );
	}

	/**
	 * Discussion settings option
	 *
	 * @since 1.0.0
	 * @param array $args [description]
	 */
	public function avatar_settings_field( $args ) {
		$options = get_option( 'general_user_avatars_caps' );
		?>
		<label for="general_user_avatars_caps">
			<input type="checkbox" name="general_user_avatars_caps" id="general_user_avatars_caps" value="1" <?php checked( $options['general_user_avatars_caps'], 1 ); ?>/>
			<?php esc_html_e( 'Only allow users with file upload capabilities to upload local avatars (Authors and above)', 'general-user-avatars' ); ?>
		</label>
		<?php
	}

	/**
	 * Sanitize the Discussion settings option
	 *
	 * @since 1.0.0
	 * @param array $input
	 * @return array
	 */
	public function sanitize_options( $input ) {
		$new_input['general_user_avatars_caps'] = empty( $input['general_user_avatars_caps'] ) ? 0 : 1;
		return $new_input;
	}

	/**
	 * Filter the avatar WordPress returns
	 *
	 * @since 1.0.0
	 * @param string $avatar
	 * @param int/string/object $id_or_email
	 * @param int $size
	 * @param string $default
	 * @param boolean $alt
	 * @return string
	 */
	public function get_avatar( $avatar = '', $id_or_email, $size = 300, $default = '', $alt = false ) {

		// Determine if we recive an ID or string
		if ( is_numeric( $id_or_email ) )
			$user_id = (int) $id_or_email;
		elseif ( is_string( $id_or_email ) && ( $user = get_user_by( 'email', $id_or_email ) ) )
			$user_id = $user->ID;
		elseif ( is_object( $id_or_email ) && ! empty( $id_or_email->user_id ) )
			$user_id = (int) $id_or_email->user_id;

		if ( empty( $user_id ) )
			return $avatar;

		$local_avatars = get_user_meta( $user_id, 'general_user_avatar', true );

		if ( empty( $local_avatars ) || empty( $local_avatars['full'] ) )
			return $avatar;

		$size = (int) $size;

		if ( empty( $alt ) )
			$alt = get_the_author_meta( 'display_name', $user_id );

		// Generate a new size
		if ( empty( $local_avatars[$size] ) ) {

			$upload_path      = wp_upload_dir();
			$avatar_full_path = str_replace( $upload_path['baseurl'], $upload_path['basedir'], $local_avatars['full'] );
			$image            = wp_get_image_editor( $avatar_full_path );
			$image_sized      = null;

			if ( ! is_wp_error( $image ) ) {
				$image->resize( $size, $size, true );
				$image_sized = $image->save();
			}

			// Deal with original being >= to original image (or lack of sizing ability).
			if ( empty( $image_sized ) || is_wp_error( $image_sized ) ) {
				$local_avatars[ $size ] = $local_avatars['full'];
			} else {
				$local_avatars[ $size ] = str_replace( $upload_path['basedir'], $upload_path['baseurl'], $image_sized['path'] );
			}

			// Save updated avatar sizes
			update_user_meta( $user_id, 'general_user_avatar', $local_avatars );

		} elseif ( substr( $local_avatars[$size], 0, 4 ) != 'http' ) {
			$local_avatars[$size] = home_url( $local_avatars[$size] );
		}

		if ( is_ssl() ) {
			$local_avatars[ $size ] = str_replace( 'http:', 'https:', $local_avatars[ $size ] );
		}

		$author_class = is_author( $user_id ) ? ' current-author' : '' ;

		$avatar       = "<img alt='" . esc_attr( $alt ) . "' src='" . $local_avatars[$size] . "' class='avatar avatar-{$size}{$author_class} photo' height='{$size}' width='{$size}' />";

			return apply_filters( 'general_user_avatar', $avatar, $user_id );
		}

	/**
	 * Form to display on the user profile edit screen
	 *
	 * @since 1.0.0
	 * @param object $profileuser
	 * @return
	 */
	public function edit_user_profile( $profileuser ) {

		// bbPress will try to auto-add this to user profiles - don't let it.
		// Instead we hook our own proper function that displays cleaner.
		if ( function_exists( 'is_bbpress') && is_bbpress() )
			return;
		?>

		<h2><?php _e( 'Avatar', 'general-user-avatars' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><label for="general-user-avatar"><?php esc_html_e( 'Upload Avatar', 'general-user-avatars' ); ?></label></th>
				<td style="width: 50px;" valign="top">
					<?php echo get_avatar( $profileuser->ID ); ?>
				</td>
				<td>
				<?php
				$options = get_option( 'general_user_avatars_caps' );
				if ( empty( $options['general_user_avatars_caps'] ) || current_user_can( 'upload_files' ) ) {
					// Nonce security ftw
					wp_nonce_field( 'general_user_avatar_nonce', '_general_user_avatar_nonce', false );

					// File upload input
					echo '<input type="file" name="general-user-avatar" id="general-local-avatar" /><br />';

					if ( empty( $profileuser->general_user_avatar ) ) {
						echo '<span class="description empty">' . esc_html__( 'Carica una tua foto', 'general-user-avatars' ) . '</span>';
					} else {
						echo '<input type="checkbox" name="general-user-avatar-erase" id="general-user-avatar-erase" value="1" /><label for="general-user-avatar-erase">' . esc_html__( 'Delete local avatar', 'general-user-avatars' ) . '</label><br />';
						echo '<span class="description">' . esc_html__( 'Carica una tua foto', 'general-user-avatars' ) . '</span>';
					}

				} else {
					if ( empty( $profileuser->general_user_avatar ) ) {
						echo '<span class="description">' . esc_html__( 'No local avatar is set. Set up your avatar at Gravatar.com.', 'general-user-avatars' ) . '</span>';
					} else {
						echo '<span class="description">' . esc_html__( 'You do not have media management permissions. To change your local avatar, contact the site administrator.', 'general-user-avatars' ) . '</span>';
					}
				}
				?>
				</td>
			</tr>
		</table>
		<script type="text/javascript">var form = document.getElementById('your-profile');form.encoding = 'multipart/form-data';form.setAttribute('enctype', 'multipart/form-data');</script>
		<?php
	}

	/**
	 * Update the user's avatar setting
	 *
	 * @since 1.0.0
	 * @param int $user_id
	 */
	public function edit_user_profile_update( $user_id ) {

		// Check for nonce otherwise bail
		if ( ! isset( $_POST['_general_user_avatar_nonce'] ) || ! wp_verify_nonce( $_POST['_general_user_avatar_nonce'], 'general_user_avatar_nonce' ) )
			return;

		if ( ! empty( $_FILES['general-user-avatar']['name'] ) ) {

			// Allowed file extensions/types
			$mimes = array(
				'jpg|jpeg|jpe' => 'image/jpeg',
				'gif'          => 'image/gif',
				'png'          => 'image/png',
			);

			// Front end support - shortcode, bbPress, etc
			if ( ! function_exists( 'wp_handle_upload' ) )
				require_once ABSPATH . 'wp-admin/includes/file.php';

			// Delete old images if successful
			$this->avatar_delete( $user_id );

			// Need to be more secure since low privelege users can upload
			if ( strstr( $_FILES['general-user-avatar']['name'], '.php' ) )
				wp_die( 'For security reasons, the extension ".php" cannot be in your file name.' );

			// Make user_id known to unique_filename_callback function
			$this->user_id_being_edited = $user_id;
			$avatar = wp_handle_upload( $_FILES['general-user-avatar'], array( 'mimes' => $mimes, 'test_form' => false, 'unique_filename_callback' => array( $this, 'unique_filename_callback' ) ) );

			// Handle failures
			if ( empty( $avatar['file'] ) ) {
				switch ( $avatar['error'] ) {
				case 'File type does not meet security guidelines. Try another.' :
					add_action( 'user_profile_update_errors', function( $error = 'avatar_error' ){
						esc_html__("Please upload a valid image file for the avatar.","general-user-avatars");
					} );
					break;
				default :
					add_action( 'user_profile_update_errors', function( $error = 'avatar_error' ){
						"<strong>".esc_html__("There was an error uploading the avatar:","general-user-avatars")."</strong> ". esc_attr( $avatar['error'] );
					} );
				}
				return;
			}

			// Save user information (overwriting previous)
			update_user_meta( $user_id, 'general_user_avatar', array( 'full' => $avatar['url'] ) );

		} elseif ( ! empty( $_POST['general-user-avatar-erase'] ) ) {
			// Nuke the current avatar
			$this->avatar_delete( $user_id );
		}
	}

	/**
	 * Enable avatar management on the frontend via this shortocde.
	 *
	 * @since 1.0.0
	 */
	function shortcode() {

		// Don't bother if the user isn't logged in
		if ( ! is_user_logged_in() )
			return;

		$user_id     = get_current_user_id();
		$profileuser = get_userdata( $user_id );

		if ( isset( $_POST['manage_avatar_submit'] ) ){
			$this->edit_user_profile_update( $user_id );
		}

		ob_start();
		?>
		<form id="general-user-avatar-form" action="<?php the_permalink(); ?>" method="post" enctype="multipart/form-data">
			<?php

			$str = get_avatar( $profileuser->ID);
			preg_match('/(src=["\'](.*?)["\'])/', $str, $match);  //find src="X" or src='X'
			$split = preg_split('/["\']/', $match[0]); // split by quotes

			$src = $split[1]; // X between quotes
			$word = 'gravatar.com';
			$user_avatar = '';
			if(strpos($src, $word) !== false){
					$user_avatar = false;
			} else{
					$user_avatar = true;
			}
			$user_first_name = $profileuser->first_name;
			$firstCharacter = '';
			if($user_first_name) {
				$firstCharacter = $user_first_name[0];
			}

			if( $user_avatar === true) {
					echo '<div class="current-avatar">';
					// File upload input
					echo '<input type="file" name="general-user-avatar" id="general-local-avatar" />';
					echo '<span class="avatar-label">Carica una tua foto</span>';
					echo get_avatar( $profileuser->ID, 300);
					echo '</div>';
			} else {
				if($user_first_name) {
					echo '<div class="current-avatar">';
					// File upload input
					echo '<input type="file" name="general-user-avatar" id="general-local-avatar" />';
					echo '<span class="avatar-label">Carica una tua foto</span>';
					echo '<span class="user-avatar">' .$firstCharacter . '</span>';
					echo '</div>';
				}
			}

			$options = get_option( 'general_user_avatars_caps' );
			if ( empty( $options['general_user_avatars_caps'] ) || current_user_can( 'upload_files' ) ) {
				// Nonce security ftw
				wp_nonce_field( 'general_user_avatar_nonce', '_general_user_avatar_nonce', false );

				if ( empty( $profileuser->general_user_avatar ) ) {
					echo '<div class="current-avatar-buttons">';
					echo '<input type="submit" class="update-avatar" name="manage_avatar_submit" value="'. apply_filters( 'gu_avatars_update_button_text', esc_attr__( 'Aggiorna immagine', 'general-user-avatars' ) ) .'" />';
					echo '</div>';
					//echo '<p class="description empty">' . apply_filters( 'bu_avatars_no_avatar_set_text',esc_html__( 'Carica una tua foto', 'general-user-avatars' ), $profileuser ) . '</p>';
				} else {
					echo '<div class="current-avatar-buttons">';
					echo '<input type="submit" class="update-avatar" name="manage_avatar_submit" value="'. apply_filters( 'gu_avatars_update_button_text', esc_attr__( 'Aggiorna immagine', 'general-user-avatars' ) ) .'" />';
					echo '</div>';
					//echo '<p class="description">' . apply_filters( 'bu_avatars_replace_avatar_text', esc_html__( 'Carica una tua foto', 'general-user-avatars' ), $profileuser ) . '</p>';
				}

			} else {
				if ( empty( $profileuser->general_user_avatar ) ) {
					//echo '<p class="description">' . apply_filters( 'gu_avatars_no_avatar_set_text', esc_html__( 'No local avatar is set. Set up your avatar at Gravatar.com.', 'general-user-avatars' ), $profileuser ) . '</p>';
				} else {
					//echo '<p class="description">' . apply_filters( 'gu_avatars_permissions_text', esc_html__( 'You do not have media management permissions. To change your local avatar, contact the site administrator.', 'general-user-avatars' ), $profileuser ) . '</p>';
				}
			}
			?>

		</form>
		<?php
		return ob_get_clean();
	}

	/**
	 * Remove the custom get_avatar hook for the default avatar list output on
	 * the Discussion Settings page.
	 *
	 * @since 1.0.0
	 * @param array $avatar_defaults
	 * @return array
	 */
	public function avatar_defaults( $avatar_defaults ) {
		remove_action( 'get_avatar', array( $this, 'get_avatar' ) );
		return $avatar_defaults;
	}

	/**
	 * Delete avatars based on user_id
	 *
	 * @since 1.0.0
	 * @param int $user_id
	 */
	public function avatar_delete( $user_id ) {
		$old_avatars = get_user_meta( $user_id, 'general_user_avatar', true );
		$upload_path = wp_upload_dir();

		if ( is_array( $old_avatars ) ) {
			foreach ( $old_avatars as $old_avatar ) {
				$old_avatar_path = str_replace( $upload_path['baseurl'], $upload_path['basedir'], $old_avatar );
				@unlink( $old_avatar_path );
			}
		}

		delete_user_meta( $user_id, 'general_user_avatar' );
	}

	/**
	 * File names are magic
	 *
	 * @since 1.0.0
	 * @param string $dir
	 * @param string $name
	 * @param string $ext
	 * @return string
	 */
	public function unique_filename_callback( $dir, $name, $ext ) {
		$user = get_user_by( 'id', (int) $this->user_id_being_edited );
		$name = $base_name = sanitize_file_name( $user->display_name . '_avatar' );
		$number = 1;

		while ( file_exists( $dir . "/$name$ext" ) ) {
			$name = $base_name . '_' . $number;
			$number++;
		}

		return $name . $ext;
	}
}
$general_user_avatars = new general_user_avatars;

function gu_check_for_avatar_update() {

	$user_id = get_current_user_id();
	if ( is_account_page() && isset( $_POST['manage_avatar_submit'] ) && class_exists( 'general_user_avatars' ) ) {
		$general_user_avatars = new general_user_avatars;
		$general_user_avatars->edit_user_profile_update( $user_id );
	}

} add_action('template_redirect', 'gu_check_for_avatar_update');




// show avatar to left of my account woocommerce customers

function uafw_show_avatar_woo() {
	$options = get_option( 'avatar_woocommerce_impostazioni' );
     if( $options ['avatar_woocommerce_checkbox'] == '1' ) {
	$current_user = wp_get_current_user();

     echo '<div class="myaccount_avatar">' . get_avatar( $current_user->user_email, 180 ) . '</div>';

	$display = '
        <style>
            .myaccount_avatar {
    border-right: 1px solid rgba(0, 0, 0, 0.1);
    padding-right: 10px;
		margin-right: 10px;
    width: 180px;
margin-bottom:10px;
}
            .woocommerce-MyAccount-content {
    margin-top: -180px;
}
@media only screen and (max-width: 600px) {
  .woocommerce-MyAccount-content {
    margin-top: 0px;

}
}
.avatar.avatar-180 {
    border-radius: 50%;
}
.woocommerce-MyAccount-content-mobile
{
    display:none;
}

        </style>

    ';

    echo $display;
 }
	}
add_action('woocommerce_before_account_navigation','uafw_show_avatar_woo');

// show avatar function in account edit to woocommerce customer
function uafw_show_avatar_wooeditaccount() {
echo do_shortcode('[general-user-avatars]');
	};
add_action('woocommerce_after_edit_account_form','uafw_show_avatar_wooeditaccount');

// function transform account to mobile
function uafw_myaccount_mobile_woo ()  {
$options = get_option( 'avatar_woocommerce_impostazioni' );
if( $options ['mobile_menu_woocommerce_checkbox'] == '1' ) {
echo '<div class="woocommerce-MyAccount-content-mobile">';
echo do_action( 'woocommerce_account_content' );
echo '</div>';
$display = '
       <style>
@media only screen and (max-width: 600px) {
  .woocommerce-MyAccount-content {
    display:none;

}
}
@media only screen and (max-width: 600px) {
  .woocommerce-MyAccount-content-mobile
{
    display:inline!important;
}
}
@media only screen and (max-width: 600px) {
  .myaccount_avatar {
    margin-top: 40px;
}
}
        </style>

    ';

    echo $display;
}
	}

add_action('woocommerce_account_navigation','uafw_myaccount_mobile_woo');
