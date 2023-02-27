<?php

class Disable_User_Gravatar_Admin {
	
	public static function init(){
		add_filter('avatar_defaults', 'Disable_User_Gravatar_Admin::avatar_defaults', 1000000); //last one
		add_filter('default_avatar_select', 'Disable_User_Gravatar_Admin::default_avatar_select', 1000000); //last one
		add_action('admin_init', 'Disable_User_Gravatar_Admin::admin_init');
	}
	
	public static function admin_init(){
		register_setting('discussion', 'gravatar_substitute', array(
				'description' => __('If your default avatar is "Disabled", the following image URL will be used for all avatars by default.', 'disable-user-avatar'),
				'sanitize_callback' => 'esc_url',
			)
		);
		add_settings_field('gravatar_substitute', __('Default Avatar Image', 'disable-user-gravatar'), 'Disable_User_Gravatar_Admin::substitute_image_field', 'discussion', 'avatars');
	}
	
	public static function default_avatar_select( $default_avatar_select ){
		$disable_gravatar_warning = __("Gravatars are disabled by the 'Disable User Gravatar' plugin. All user emails will be anonymized when sent to gravatar.com and therefore will always produce generated avatars.", 'disable-user-avatar');
		$default_avatar_select = '<p class="description" style="color:#cc0000;">' . $disable_gravatar_warning . '</p>' . $default_avatar_select;
		return $default_avatar_select;
	}
	
	public static function avatar_defaults( $avatar_defaults ){
		$avatar_defaults['disable_gravatar_substitute'] = esc_html__('Disable Gravatar (Use Default Avatar Image)', 'disable-user-gravatar');
		if( function_exists('buddypress') ){
			$avatar_defaults['disable_gravatar_buddypress'] = esc_html__('Disable Gravatar via BuddyPress', 'disable-user-gravatar');
		}
		return $avatar_defaults;
	}
	
	public static function substitute_image_field(){
		//add custom field
		$gravatar_substitute = get_option('gravatar_substitute', false);
		if( empty($gravatar_substitute) ) $gravatar_substitute = plugins_url('default-gravatar.png', __FILE__);
		echo '<input type="text" name="gravatar_substitute" value="' . esc_attr($gravatar_substitute) . '" class="regular-text" placeholder="https://domain.com/path/to/image.jpg">';
		echo '<br><p class="description">'. esc_html__('If your default avatar is "Disable Gravatar", the following image URL will be used for all avatars by default.', 'disable-user-avatar'). '</p>';
	}
	
}
Disable_User_Gravatar_Admin::init();