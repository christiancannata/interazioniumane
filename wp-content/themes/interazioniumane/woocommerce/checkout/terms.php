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


    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        $product = $cart_item['data'];

        if (0 == $product->get_price()) {
            echo '<span class="free-course-item" style="margin-top: 16px;"></span>';
        } else if (has_term('selezione', 'product_cat', $product->get_id()) && 0 != $product->get_price()) {
            /*
            if (str_contains($product->get_title(), 'II livello')) {
                echo "<div class='info-payment'>";
                echo "<h3 class='my-account--minititle'>Pagamento</h3>";
                echo "<p>L'iscrizione non è vincolante, ovvero non comporta alcun costo, ma consente di partecipare alle selezioni.</p>";
                echo '</div>';
            } else {
                echo "<div class='info-payment'>";
                echo "<h3 class='my-account--minititle'>Pagamento</h3>";
                echo "<p>Il collegio docenti valuterà la vostra candidatura e vi invierà un riscontro. In caso di accettazione dovrete procedere al pagamento della quota di iscrizione.</p>";
                echo '</div>';
            }

               */
        } else {
            /*echo $product->get_price();
            echo "<div class='info-payment'>";
            echo "<h3 class='my-account--minititle'>Pagamento</h3>";
            echo "<p>La tua richiesta di iscrizione sarà confermata al pagamento della fattura che riceverai a mezzo email all'indirizzo che hai indicato entro 2 gg lavorativi. In caso di mancato pagamento entro 7gg (o in ogni caso entro 24h prima dell'avvio dell'evento) dall'emissione del documento fiscale, la tua iscrizione sarà automaticamente cancellata. Una volta effettuo il pagamento, potrai annullare la tua iscrizione ottenendo il rimborso totale della quota, dando comunicazione di disdetta entro 14 gg prima dell’evento e il 50% della quota entro 7 gg prima dell’evento. In caso di mancato avviso entro la data stabilita non sarà possibile ottenere alcun rimborso. In caso di mancato pagamento IESCUM si riserva adempiere tutte le azioni necessarie al recupero della quota dovuta.</p>";
            echo '</div>';*/
        }
    } ?>

    <?php
    if (!empty($testoPagamento)):
        ?>
        <div class='info-payment' style="width:100%">
            <h3 class='my-account--minititle'>Pagamento</h3>
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
