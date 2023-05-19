<?php

// ob_clean();
// ob_start();


//Aggiungi javascript
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script('iu-main', get_template_directory_uri() . '/js/main.js', ['jquery'], null, true);
    wp_enqueue_script('slick', get_template_directory_uri() . '/js/slick.min.js', ['jquery'], null, true);
    wp_enqueue_script('aos', get_template_directory_uri() . '/js/aos.js', ['jquery'], null, true);
    wp_localize_script('ajax_term', 'wpAjax', array('ajaxUrl' => admin_url('admin-ajax.php')));

}, 100);

//Menu custom
function footer_menu()
{
    register_nav_menus(
        array(
            'footer_menu_one' => __('Footer Menu 1'),
            'footer_menu_two' => __('Footer Menu 2'),
            'footer_menu_three' => __('Footer Menu 3'),
            'footer_menu_user' => __('Menu utente mobile')
        )
    );
}

add_action('init', 'footer_menu');

//Template categoria 'distretti'
// function wpd_subcategory_template( $template ) {
//     $cat = get_queried_object();
//     if( 0 < $cat->category_parent )
//         $template = locate_template( 'subcategory-distretti.php' );
//     return $template;
// }
// add_filter( 'category_template', 'wpd_subcategory_template' );


//***** Custom archive titles *****//
add_filter('get_the_archive_title', function ($title) {
    if (is_category()) {
        $title = single_cat_title('', false);
    } elseif (is_tag()) {
        $title = single_tag_title('', false);
    } elseif (is_author()) {
        $title = '<span class="vcard">' . get_the_author() . '</span>';
    } elseif (is_tax()) { //for custom post types
        $title = sprintf(__('%1$s'), single_term_title('', false));
    }
    return $title;
});

//***** Filtro Ajax ******//
add_action('wp_ajax_myfilter', 'misha_filter_function'); // wp_ajax_{ACTION HERE}
add_action('wp_ajax_nopriv_myfilter', 'misha_filter_function');

function misha_filter_function()
{
    $args = array(
        'orderby' => 'date', // we will sort posts by date
        'order' => $_POST['date'] // ASC or DESC
    );

    // for taxonomies / categories
    if (isset($_POST['categoryfilter']))
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'corsi',
                'field' => 'id',
                'terms' => $_POST['categoryfilter']
            )
        );

    $query = new WP_Query($args);

    if ($query->have_posts()) :
        while ($query->have_posts()): $query->the_post();
            echo get_template_part('loop-templates/content-card', get_post_format());
        endwhile;
        wp_reset_postdata();
    else :
        echo 'No posts found';
    endif;

    die();
}

//Remove breadcrumbs
remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0);

//Remove excerpt prodcuts
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);

function woocommerce_template_single_excerpt()
{
    return;
}

//Options page ACF
if (function_exists('acf_add_options_page')) {
    acf_add_options_page();
}


//Permalink categorie prodotto
add_filter('request', function ($vars) {
    global $wpdb;
    if (!empty($vars['pagename']) || !empty($vars['category_name']) || !empty($vars['name']) || !empty($vars['attachment'])) {
        $slug = !empty($vars['pagename']) ? $vars['pagename'] : (!empty($vars['name']) ? $vars['name'] : (!empty($vars['category_name']) ? $vars['category_name'] : $vars['attachment']));
        $exists = $wpdb->get_var($wpdb->prepare("SELECT t.term_id FROM $wpdb->terms t LEFT JOIN $wpdb->term_taxonomy tt ON tt.term_id = t.term_id WHERE tt.taxonomy = 'product_cat' AND t.slug = %s", array($slug)));
        if ($exists) {
            $old_vars = $vars;
            $vars = array('product_cat' => $slug);
            if (!empty($old_vars['paged']) || !empty($old_vars['page']))
                $vars['paged'] = !empty($old_vars['paged']) ? $old_vars['paged'] : $old_vars['page'];
            if (!empty($old_vars['orderby']))
                $vars['orderby'] = $old_vars['orderby'];
            if (!empty($old_vars['order']))
                $vars['order'] = $old_vars['order'];
        }
    }
    return $vars;
});

//Rimuovo editor prodotto
function remove_product_editor()
{
    remove_post_type_support('product', 'editor');
}

add_action('init', 'remove_product_editor');

/**
 * Clears WC Cart on Page Load
 * (Only when not on cart/checkout page)
 */

add_action('wp_head', 'bryce_clear_cart');
function bryce_clear_cart()
{
    if (wc_get_page_id('cart') == get_the_ID() || wc_get_page_id('checkout') == get_the_ID()) {
        return;
    }
    WC()->cart->empty_cart(true);
}


//PAGINA FORMAZIONE: Nascondi corsi con iscrizione chiusi

add_action('woocommerce_product_query', 'hide_finishd_events');

function hide_finishd_events($q)
{
    $meta_query = $q->get('meta_query');

    $today = date('Ymd');
    $start_date = get_field('start_date');

    $meta_query[] = array(
        'key' => 'end_booking',
        'value' => $today,
        'compare' => '>',
    );

    $tax_query[] = array(
        'taxonomy' => 'product_cat',
        'field' => 'slug',
        'terms' => array('privato'),
        'operator' => 'NOT IN',
    );


    $q->set('meta_key', 'start_date');
    $q->set('orderby', 'meta_value');
    $q->set('order', 'ASC');

    $q->set('meta_query', $meta_query);
    $q->set('tax_query', $tax_query);
}


//Rimuovo prodotti privati dalla ricerca
function wcs_exclude_category_search($query)
{
    if (is_admin() || !$query->is_main_query())
        return;

    $tax_query[] = array(
        'taxonomy' => 'product_cat',
        'field' => 'slug',
        'terms' => array('privato'),
        'operator' => 'NOT IN',
    );

    if ($query->is_search) {
        $query->set('tax_query', $tax_query);
    }

}

add_action('pre_get_posts', 'wcs_exclude_category_search', 1);

//Only show products in the front-end search results
add_filter('pre_get_posts', 'lw_search_filter_pages');
function lw_search_filter_pages($query)
{
    // Frontend search only
    if (!is_admin() && $query->is_search()) {
        $query->set('post_type', 'product');
        $query->set('wc_query', 'product_query');
    }
    return $query;
}

/**
 * Add Remove button to WooCommerce checkout page - cart items
 */
function filter_woocommerce_checkout_cart_item_quantity($item_qty, $cart_item, $cart_item_key)
{
    $remove_link = apply_filters('woocommerce_cart_item_remove_link',
        sprintf(
            '<a href="#" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s" data-cart_item_key="%s">&times;</a>',
            __('Remove this item', 'woocommerce'),
            esc_attr($cart_item['product_id']),
            esc_attr($cart_item['data']->get_sku()),
            esc_attr($cart_item_key)
        ),
        $cart_item_key);

    // Return
    return $item_qty . $remove_link;
}

add_filter('woocommerce_checkout_cart_item_quantity', 'filter_woocommerce_checkout_cart_item_quantity', 10, 3);

// jQuery - Ajax script
function action_wp_footer()
{
    // Only checkout page
    if (!is_checkout())
        return;
    ?>
    <script type="text/javascript">
        jQuery(function ($) {
            $('form.checkout').on('click', '.cart_item a.remove', function (e) {
                e.preventDefault();

                var cart_item_key = $(this).attr("data-cart_item_key");

                $.ajax({
                    type: 'POST',
                    url: wc_checkout_params.ajax_url,
                    data: {
                        'action': 'woo_product_remove',
                        'cart_item_key': cart_item_key,
                    },
                    success: function (result) {
                        $('body').trigger('update_checkout');
                        //console.log( 'response: ' + result );
                    },
                    error: function (error) {
                        //console.log( error );
                    }
                });
            });
        });
    </script>
    <?php

}

add_action('wp_footer', 'action_wp_footer', 10, 0);

// Php Ajax
function woo_product_remove()
{
    if (isset($_POST['cart_item_key'])) {
        $cart_item_key = sanitize_key($_POST['cart_item_key']);

        // Remove cart item
        WC()->cart->remove_cart_item($cart_item_key);
    }

    // Alway at the end (to avoid server error 500)
    die();
}

add_action('wp_ajax_woo_product_remove', 'woo_product_remove');
add_action('wp_ajax_nopriv_woo_product_remove', 'woo_product_remove');

//Label note ordini
// Hook in
add_filter('woocommerce_checkout_fields', 'theme_override_checkout_notes_fields');

// Our hooked in function - $fields is passed via the filter!
function theme_override_checkout_notes_fields($fields)
{
    $fields['order']['order_comments']['placeholder'] = 'Ci sono richieste particolari che dobbiamo tenere in considerazione?';
    $fields['order']['order_comments']['label'] = 'Note personali';
    return $fields;
}


///Cambia bottone se il corso è su selezione
add_filter('woocommerce_order_button_text', 'subscriptions_custom_checkout_submit_button_text');
function subscriptions_custom_checkout_submit_button_text($order_button_text)
{

    $moduloIscrizione = getModuloIscrizioneFromCart();
    $ctaButton = get_post_meta($moduloIscrizione, 'cta_button', true);

    if (!$ctaButton) {
        $ctaButton = 'Concludi ordine';
    }

    return $ctaButton;
}


///Tolgo dati di fatturazione se il corso è gratis
add_filter('woocommerce_checkout_fields', 'custom_override_checkout_fields');

function custom_override_checkout_fields($fields)
{
    global $woocommerce;

    if (0 != $woocommerce->cart->total) {
        return $fields;
    }

    // unset($fields['billing']['billing_first_name']);
    // unset($fields['billing']['billing_last_name']);
    unset($fields['billing']['billing_company']);
    unset($fields['billing']['billing_address_1']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_city']);
    unset($fields['billing']['billing_postcode']);
    unset($fields['billing']['billing_country']);
    unset($fields['billing']['billing_state']);
    // unset($fields['billing']['billing_phone']);

    return $fields;


}

function getModuloIscrizioneFromCart()
{

//get modulo iscrizione
    global $woocommerce;
    $items = $woocommerce->cart->get_cart();
    $moduloIscrizione = null;
    foreach ($items as $item => $product) {
        $moduloIscrizione = get_field('modulo_iscrizione', $product['product_id']);
        if (is_array($moduloIscrizione) && !empty($moduloIscrizione)) {
            $moduloIscrizione = reset($moduloIscrizione);
            $moduloIscrizione = $moduloIscrizione->ID;
        }
    }

    return $moduloIscrizione;
}


