<?php

//***** Custom post: CORSI *****//

add_action( 'init', 'subscriptions_posts_init' );
function subscriptions_posts_init() {
    $theme = get_bloginfo("template_directory");
    $args = array(
        'labels' => array(
            'name' => 'Iscrizioni',
            'singular_name' => 'Iscrizione',
            'add_new' => 'Aggiungi nuova',
            'add_new_item' => 'Aggiungi iscrizione',
            'edit_item' => 'Modifica iscrizione',
            'new_item' => 'Nuova iscrizione',
            'all_items' => 'Tutte le iscrizioni',
            'view_item' => 'Visualizza iscrizione',
            'search_items' => 'Cerca iscrizioni',
            'not_found' =>  'Nessuna iscrizione trovata',
            'not_found_in_trash' => 'Nessuna iscrizione trovata nel cestino',
            'parent_item_colon' => '',
            'menu_name' => 'Iscrizioni'
        ),
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array( 'slug' => 'Iscrizioni' ),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array( 'title', 'thumbnail', 'author'  ),
        'menu_position'=>5,
        'menu_icon'           => $theme . '/assets/img/admin/menu-icon-students.svg',
        //'taxonomies'=>array('category')
    );

    register_post_type( 'Iscrizion', $args );
}
