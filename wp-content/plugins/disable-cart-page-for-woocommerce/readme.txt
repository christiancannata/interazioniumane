=== Disable cart page for WooCommerce ===
Contributors: code4life
Tags: WooCommerce, disable cart, single product buy, redirect to checkout
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=code4lifeitalia@gmail.com&item_name=Donazione&item_number=Contributo+libero¤cy_code=EUR&lc=it_IT
Requires at least: 4.6
Tested up to: 5.9
Stable tag: 1.2.5
WC requires at least: 2.0
WC tested up to: 6.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Disable WooCommerce cart page and force customers to buy single products.

== Description ==
Disable cart page for WooCommerce is a plugin that allows you to bypass the WooCommrce cart page, redirecting your customers directly to checkout page and also disable the ability to add multiple products to their orders.
Disable cart page for WooCommerce is ideal when you need to force your customers to buy only one product at a time from your WooCommerce catalog, but additionally, by modifying the standard WooCommerce shopping behavior, it greatly simplifies WooCommerce process of finalizing orders, increasing your users conversions and increasing your profits.

= Features =
Disable cart page for WooCommerce plugin provide you a lot of extra features, such as:
* Simplified purchase
* Functions control
* Multilanguage
* Enable/Disable settings

For more information, see [Official page](https://code4life.it/shop/plugins/disable-cart-page-for-woocommerce/).

= Docs & Support =
You can find help in the [support forum](https://wordpress.org/support/plugin/disable-cart-page-for-woocommerce/) on WordPress.org. If you can't locate any topics that pertain to your particular issue, post a new topic for it.

= Hooks & Filters =
* Change redirect page when cart is empty: apply_filters( 'wcdcp_redirect', string $permalink )

= Translations =
You can translate Disable cart page for WooCommerce on [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/disable-cart-page-for-woocommerce/).

== Installation ==
1. Upload the entire `disable-cart-page-for-woocommerce` folder to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the `Plugins` menu in WordPress.
3. Go to settings tab, under WooCommerce settings page, and enable the plugin functionality.

== Frequently Asked Questions ==

= What happens if someone reaches the cart page?

Plugin redirect customers to checkout permanently

= What happens if a customer has added a product to the shopping cart before a new product adding?

Plugin take care to empty cart before another product is added

== Changelog ==
For more information, see [Official page](https://code4life.it/shop/plugins/disable-cart-page-for-woocommerce/).

= v1.2.5 =
* Added "pre_option_woocommerce_cart_redirect_after_add" hook to force WooCommerce to redirect after product added to cart
* Bug fix: added return $cart_item_data variable in "woocommerce_add_cart_item_data" hook

= v1.2.4 =
* Added function to empty cart if error occurs

= v1.2.3 =
* Fix: language domain as requested by WordPress internationalization standards

= v1.2.2 =
* Bug fix: some WooCommerce error messages not appeared properly
* Added filter to change redirect page when cart is empty

= v1.2.1 =
* Bug fix for compatibility both with WooCommerce version 3.x.x and with older versions
* Bug fix for infinite loop when users go to checkout and cart is empty

= v1.2.0 =
* Added settings tab to enable/disable redirect to checkout page

= v1.1.0 =
* Added support to WooCommerce 3.0+

= v1.0.0 =
* Initial release