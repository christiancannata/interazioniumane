<?php
/**
 * Checkout terms and conditions area.
 *
 * @package WooCommerce\Templates
 * @version 3.4.0
 */

defined('ABSPATH') || exit;

if (apply_filters('woocommerce_checkout_show_terms', true) && function_exists('wc_terms_and_conditions_checkbox_enabled')) {
    do_action('woocommerce_checkout_before_terms_and_conditions');

    ?>

    <?php

    $moduloIscrizione = getModuloIscrizioneFromCart();
    $testoPagamento = null;
    $showCheckboxIscrizione = true;
    if ($moduloIscrizione) {
        $testoPagamento = get_field('testo_pagamento', $moduloIscrizione);
        $showCheckboxIscrizione = get_field('show_checkbox_iscrizione', $moduloIscrizione);
    }
    ?>

    <?php
    if (!empty($testoPagamento)):
        ?>
        <div class='info-payment' style="width:100%">

            <p><?php echo $testoPagamento; ?></p>
        </div>
    <?php endif; ?>

    <div class="woocommerce-terms-and-conditions-wrapper">

        <?php if ($showCheckboxIscrizione): ?>
            <div class="checkbox-form" style="margin-bottom:8px !important;">
                <input type="checkbox" name="ts-system" id="ts-system"/>
                <label for="ts-system">
                    Il paziente si oppone alla trasmissione al Sistema TS ai sensi dell’art. 3 del DM 31-7-2015
                </label>
            </div>
            <div class="clear"></div>
        <?php endif; ?>

        <div class="checkbox-form" style="margin-bottom:24px !important;">
            <input type="checkbox"
                   name="terms" <?php checked(apply_filters('woocommerce_terms_is_checked_default', isset($_POST['terms'])), true); // WPCS: input var ok, csrf ok. ?>
                   id="terms"/>
            <label for="terms">
                Ho letto e accetto la <a href="<?php echo esc_url(home_url('/')); ?>privacy-policy" target="_blank">Privacy
                    Policy</a>
            </label>
        </div>
        <div class="clear"></div>

        <?php
        /**
         * Terms and conditions hook used to inject content.
         *
         * @since 3.4.0.
         * @hooked wc_checkout_privacy_policy_text() Shows custom privacy policy text. Priority 20.
         * @hooked wc_terms_and_conditions_page_content() Shows t&c page content. Priority 30.
         */
        //do_action( 'woocommerce_checkout_terms_and_conditions' );
        ?>

    </div>

    <?php

    //do_action( 'woocommerce_checkout_after_terms_and_conditions' );
}
