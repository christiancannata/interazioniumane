<?php
/**
 * Define the internationalization functionality.
 *
 * @link       https://themelocation.com
 * @since      3.1.0
 *
 * @package    add-fields-to-checkout-page-woocommerce-premium
 * @subpackage add-fields-to-checkout-page-woocommerce-premium/public
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFE_i18n')):

class THWCFE_i18n {
	const TEXT_DOMAIN = 'woocommerce-checkout-field-editor-pro';
	const ICL_CONTEXT = 'woocommerce-checkout-field-editor-pro';
	const ICL_NAME_PREFIX = "WCFE";
	
	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    2.3.0
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters('plugin_locale', get_locale(), self::TEXT_DOMAIN);
		
		load_textdomain(self::TEXT_DOMAIN, WP_LANG_DIR.'/woocommerce-checkout-field-editor-pro/'.self::TEXT_DOMAIN.'-'.$locale.'.mo');
		load_plugin_textdomain(self::TEXT_DOMAIN, false, dirname(THWCFE_BASE_NAME) . '/languages/');
	}
	
	public static function get_locale_code(){
		$locale_code = '';
		$locale = get_locale();
		if(!empty($locale)){
			$locale_arr = explode("_", $locale);
			if(!empty($locale_arr) && is_array($locale_arr)){
				$locale_code = $locale_arr[0];
			}
		}		
		return empty($locale_code) ? 'en' : $locale_code;
	}
	
	public static function t($text){
		if(!empty($text)){	
			$otext = $text;						
			$text = __($text, self::TEXT_DOMAIN);	
			if($text === $otext){
				$text = self::icl_t($text);
				if($text === $otext){	
					$text = __($text, 'woocommerce');
				}
			}
		}
		return $text;
	}
	
	public static function et($text){
		if(!empty($text)){	
			$otext = $text;						
			$text = __($text, self::TEXT_DOMAIN);	
			if($text === $otext){
				$text = self::icl_t($text);
				if($text === $otext){		
					$text = __($text, 'woocommerce');
				}
			}
		}
		echo $text;
	}
	
	public static function esc_attr__t($text){
		if(!empty($text)){	
			$otext = $text;						
			$text = esc_attr__($text, self::TEXT_DOMAIN);	
			if($text === $otext){
				$text = self::icl_t($text);	
				if($text === $otext){	
					$text = esc_attr__($text, 'woocommerce');
				}
			}
		}
		return $text;
	}
	
	public static function esc_html__t($text){
		if(!empty($text)){	
			$otext = $text;						
			$text = esc_html__($text, self::TEXT_DOMAIN);	
			if($text === $otext){
				$text = self::icl_t($text);	
				if($text === $otext){	
					$text = esc_html__($text, 'woocommerce');
				}
			}
		}
		return $text;
	}
	
	/* WPML SUPPORT */
	public static function wpml_register_string($name, $value ){
		$name = self::ICL_NAME_PREFIX." - ".$value;
		
		if(function_exists('icl_register_string')){
			icl_register_string(self::ICL_CONTEXT, $name, $value);
		}
	}
	
	public static function wpml_unregister_string($name){
		if(function_exists('icl_unregister_string')){
			icl_unregister_string(self::ICL_CONTEXT, $name);
		}
	}
	
	public static function icl_t($value){
        $name = self::ICL_NAME_PREFIX." - ".$value;
		
		if(function_exists('icl_t')){
			$value = icl_t(self::ICL_CONTEXT, $name, $value);
		}
		return $value;
	}
}

endif;