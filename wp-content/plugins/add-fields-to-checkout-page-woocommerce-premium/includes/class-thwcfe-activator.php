<?php
/**
 * Fired during plugin activation.
 *
 * @link       https://themelocation.com
 * @since      3.1.0
 *
 * @package    add-fields-to-checkout-page-woocommerce-premium
 * @subpackage add-fields-to-checkout-page-woocommerce-premium/includes
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFE_Activator')):

class THWCFE_Activator {

	/**
	 * Copy older version settings if any.
	 *
	 * Use pro version settings if available, if no pro version settings found 
	 * check for free version settings and use it.
	 *
	 * - Check for premium version settings, if found do nothing. 
	 * - If no premium version settings found, then check for free version settings and copy it.
	 *
	 * @since    2.9.0
	 */
	public static function activate() {
		self::check_for_premium_settings();
	}
	
	public static function check_for_premium_settings(){
		$premium_settings = get_option(THWCFE_Utils::OPTION_KEY_CUSTOM_SECTIONS);
		
		if($premium_settings && is_array($premium_settings)){			
			return;
		}else{		
			self::may_copy_free_version_settings();
		}
	}

	public static function may_copy_free_version_settings(){
		$admin_utils = new THWCFE_Admin_Utils();
		$checkout_sections = array('billing', 'shipping', 'additional');
		$copied = false;
		
		foreach($checkout_sections as $sname){
			$field_set_key = 'wc_fields_'.$sname;
			$field_set = get_option($field_set_key);

			if($field_set && is_array($field_set)){
				$section = self::prepare_section_and_fields($admin_utils, $sname, $field_set);
				$section = THWCFE_Utils_Section::sort_fields($section);

				$result = $admin_utils->update_section($section);
				if($result){
					$copied = true;
					delete_option($field_set_key);
				}
			}
		}

		if(!$copied){
			$admin_utils->prepare_sections_and_fields();
		}
	}


	public static function prepare_section_and_fields($admin_utils, $sname, $fields){
		$section = THWCFE_Utils::get_checkout_section($sname);
		if(empty($section)){
			$admin_utils->prepare_sections_and_fields();
			$section = THWCFE_Utils::get_checkout_section($sname);
		}
		
		if(THWCFE_Utils_Section::is_valid_section($section)){
			$section = THWCFE_Utils_Section::clear_fields($section);

			if(is_array($fields)){
				foreach($fields as $name => $field){
					$custom_field = isset($field['custom']) ? $field['custom'] : 0;
					$new_field = self::prepare_field($name, $field);
					$section = THWCFE_Utils_Section::add_field($section, $new_field, $custom_field);
				}
			}
		}
		return $section;
	}

	public static function prepare_field($name, $field){
		$new_field = false;
		
		if($field){
			$type = isset($field['type']) ? $field['type'] : 'text';
			
			$new_field = THWCFE_Utils_Field::create_field($type);
			$new_field = THWCFE_Utils_Field::prepare_field($new_field, $name, $field);
			
			if($type === 'select'){
				$options = $new_field->get_property('options');
				if($options && is_array($options)){
					$options_json = json_encode($options);
					$new_field->set_property('options_json',$options_json);
				}
			}
			$new_field = THWCFE_Utils_Field::prepare_properties($new_field);
		}
		return $new_field;
	}
}

endif;