<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

if (!defined('ABSPATH')) {
    exit;
}

do_action('woocommerce_before_checkout_form', $checkout);

// If checkout registration is disabled and not logged in, the user cannot checkout.
if (!$checkout->is_registration_enabled() && $checkout->is_registration_required() && !is_user_logged_in()) {
    echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'woocommerce')));
    return;
}

?>

<form name="checkout" method="post" class="checkout woocommerce-checkout"
      action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">

    <?php if ($checkout->get_checkout_fields()) : ?>

        <?php do_action('woocommerce_checkout_before_customer_details'); ?>

        <div class="col2-set" id="customer_details">
            <div class="col-1">
                <?php do_action('woocommerce_checkout_billing'); ?>
            </div>

            <div class="col-2">
                <?php do_action('woocommerce_checkout_shipping'); ?>
            </div>
        </div>

        <?php do_action('woocommerce_checkout_after_customer_details'); ?>

    <?php endif; ?>


 <?php 

    global $woocommerce;

    $canViewRiduzioni = false;

    $items = $woocommerce->cart->get_cart();

    foreach ($items as $item => $product) {
        $earlyMode = get_field('in_early', $product['product_id']);
        if(is_null($earlyMode ) || $earlyMode === false){
            $canViewRiduzioni = true;
        }
    }

    
    if($canViewRiduzioni):
   
    $moduloIscrizione = null;
    $riduzioniProduct = [];
    foreach ($items as $item => $product) {
        $riduzioni = get_field('riduzioni', $product['product_id']);
        if (is_array($riduzioni) && !empty($riduzioni)) {
            $riduzioniProduct = $riduzioni;
        }
    }

    $riduzioniProduct = array_map(function($tmp){
        $post = get_post($tmp);
        if(!$post){
            return null;
        }
        $post->riduzione = floatval(get_field('sconto_in_percentuale',$post->ID));
        return $post;
    },$riduzioniProduct );

    ?>
    <?php if(count($riduzioniProduct)> 0): ?>
<div class="woocommerce-additional-fields">
<h3 class="my-account--minititle" style="">Hai diritto a qualche riduzione?</h3>
    
    <div class="container-checkbox-riduzione">
        <div>
        <input type="radio" name="riduzione" value="0" checked>
        </div>
        <div>
            <span>Non ho nessuna riduzione</span>
        </div>
    </div>
    <?php foreach($riduzioniProduct as $riduzione): ?>
    <div class="container-checkbox-riduzione">
        <div>
        <input type="radio" name="riduzione" value="<?php echo $riduzione->ID ?>">
        </div>
        <div>
            <span><?php echo $riduzione->post_title; ?></span>
        </div>
    </div>

<?php endforeach; ?>



</div>

<?php endif; ?>

<?php endif; ?>


    <?php do_action('woocommerce_checkout_before_order_review_heading'); ?>


    <h3 id="order_review_heading"><?php esc_html_e('Your order', 'woocommerce'); ?></h3>

    <?php do_action('woocommerce_checkout_before_order_review'); ?>

    <div id="order_review" class="woocommerce-checkout-review-order">
        <?php do_action('woocommerce_checkout_order_review'); ?>
    </div>

    <?php do_action('woocommerce_checkout_after_order_review'); ?>

</form>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
