<?php

//***** Custom post: PARTNER *****//

add_action( 'init', 'partner_posts_init' );
function partner_posts_init() {
    $theme = get_bloginfo("template_directory");
    $args = array(
        'labels' => array(
            'name' => 'Partner',
            'singular_name' => 'Partner',
            'add_new' => 'Aggiungi nuovo',
            'add_new_item' => 'Aggiungi partner',
            'edit_item' => 'Modifica partner',
            'new_item' => 'Nuovo partner',
            'all_items' => 'Tutti i partner',
            'view_item' => 'Visualizza partner',
            'search_items' => 'Cerca partner',
            'not_found' =>  'Nessun partner trovato',
            'not_found_in_trash' => 'Nessun partner trovato nel cestino',
            'parent_item_colon' => '',
            'menu_name' => 'Partner'
        ),
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array( 'slug' => 'partner' ),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array( 'title' ),
        'menu_position'=>5,
        'menu_icon'           => $theme . '/assets/img/admin/menu-icon-partner.svg',
    );

    register_post_type( 'partner', $args );
}
