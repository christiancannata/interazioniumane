<?php
/*
Plugin Name: Disable User Gravatar
Plugin URI: https://wordpress.org/plugins/disable-user-gravatar/
Description: Stops wordpress from automatically grabbing the users' gravatar with their registered email.
Tags: gravatar, avatar, wordpress mu, wpmu, buddypress
Version: 3.1
Author: Marcus Sykes
Author URI: http://msyk.es/?utm_source=disable-user-gravatar&utm_medium=plugin-header&utm_campaign=plugins

Copyright (C) 2019 Marcus Sykes

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class Disable_User_Gravatar {
	
	static $email_template = "member.%USER%@somerandomdomain.com";
	
	public static function init(){
		//create your own gravatar email template if you'd like by defining this in wp-config.php
		if( defined('DISABLE_GRAVATAR_TEMPLATE') ){
			self::$email_template = DISABLE_GRAVATAR_TEMPLATE;
		}
		//general gravatar stuff
		add_filter('get_avatar', 'Disable_User_Gravatar::wp_avatar', 1, 5);
		add_filter('user_profile_picture_description', '__return_empty_string');
		if( is_admin() ){
			include('disable-user-gravatar-admin.php');
		}
		//buddypress
		if( get_option('avatar_default') == 'disable_gravatar_buddypress' ){
			add_filter('bp_core_fetch_avatar_no_grav', '__return_true');
		}else{
			add_filter('bp_core_fetch_avatar', 'Disable_User_Gravatar::bp_avatar', 1, 2);
			add_filter('bp_core_fetch_avatar_url', 'Disable_User_Gravatar::bp_avatar', 1, 2);
		}
	}
	
	public static function wp_avatar( $content, $id_or_email, $size = '', $default = ''){
		//check default gravatar replacement
		if( $default == 'disable_gravatar_substitute' ){
			$gravatar_substitute = get_option('gravatar_substitute', false);
			if( empty($gravatar_substitute) ) $gravatar_substitute = plugins_url('default-gravatar.png', __FILE__);
			return preg_replace("/'(https?:)?\/\/.+?'/", $gravatar_substitute, $content);
		}
		//replace gravatar itself
		if( preg_match( "/gravatar.com\/avatar/", $content ) ){
			//get user login
			if ( is_numeric($id_or_email) ) {
				$id = (int) $id_or_email;
				$user = get_userdata($id);
			} elseif ( is_object($id_or_email) ) {
				if ( !empty($id_or_email->user_id) ) {
					$id = (int) $id_or_email->user_id;
					$user = get_userdata($id);
				}elseif( !empty( $id_or_email->post_author) ){
					$user = get_user_by( 'id', (int) $id_or_email->post_author );
				}elseif ( !empty($id_or_email->comment_author_email) ) {
					return $content; //Commenters not logged in don't need filtering
				}
			} else {
				$user = get_user_by('email', $id_or_email);
			}
			if(!$user) return $content;
			$username = $user->user_login;
			//replace the email template with username and md5 it for gravatar
			$email = md5( str_replace('%USER%', $username, self::$email_template) );
			//replace the image url
			$avatar = preg_replace("/gravatar.com\/avatar\/[a-zA-Z0-9]+/", "gravatar.com/avatar/{$email}", $content);
			return $avatar;
		}
		return $content;
	}
	
	public static function bp_avatar( $content, $params ){
		if( is_array($params) && $params['object'] == 'user' ){
			$default = !empty($params['default']) ? $params['default'] : '';
			return self::wp_avatar($content, $params['item_id'], '', $default);
		}
		return $content;
	}
	
}
Disable_User_Gravatar::init();