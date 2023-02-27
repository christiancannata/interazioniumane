<?php

//***** Custom post: CORSI *****//

add_action( 'init', 'teachers_posts_init' );
function teachers_posts_init() {
    $theme = get_bloginfo("template_directory");
    $args = array(
        'labels' => array(
            'name' => 'Docenti',
            'singular_name' => 'Docente',
            'add_new' => 'Aggiungi nuovo',
            'add_new_item' => 'Aggiungi docente',
            'edit_item' => 'Modifica docente',
            'new_item' => 'Nuovo docente',
            'all_items' => 'Tutti i docenti',
            'view_item' => 'Visualizza docente',
            'search_items' => 'Cerca docenti',
            'not_found' =>  'Nessun docente trovato',
            'not_found_in_trash' => 'Nessun docente trovato nel cestino',
            'parent_item_colon' => '',
            'menu_name' => 'Docenti'
        ),
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array( 'slug' => 'docenti' ),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array( 'title', 'thumbnail' ),
        'menu_position'=>5,
        'menu_icon'           => $theme . '/assets/img/admin/menu-icon-teachers.svg',
        //'taxonomies'=>array('category')
    );

    register_post_type( 'teacher', $args );
}

// mostra tutti in archivio
function alpha_order_classes( $query ) {
    if ( $query->is_post_type_archive('teacher') && $query->is_main_query() ) {
        $query->set('posts_per_page', -1);
    }
}

add_action( 'pre_get_posts', 'alpha_order_classes' );

// Ordina per alfabeto
// function alpha_order_classes( $query ) {
//     if ( $query->is_post_type_archive('teacher') && $query->is_main_query() ) {
//         $query->set('meta_key', 'teacher_lastname');
//         $query->set('orderby', 'meta_value');
//         $query->set( 'order', 'ASC' );
//     }
// }
//
// add_action( 'pre_get_posts', 'alpha_order_classes' );
