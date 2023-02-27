<?php

//***** Custom post: CORSI *****//

add_action( 'init', 'books_posts_init' );
function books_posts_init() {
    $theme = get_bloginfo("template_directory");
    $args = array(
        'labels' => array(
            'name' => 'Libreria',
            'singular_name' => 'Libro',
            'add_new' => 'Aggiungi nuovo',
            'add_new_item' => 'Aggiungi libro',
            'edit_item' => 'Modifica libro',
            'new_item' => 'Nuovo libro',
            'all_items' => 'Tutti i libri',
            'view_item' => 'Visualizza libro',
            'search_items' => 'Cerca libri',
            'not_found' =>  'Nessun libro trovato',
            'not_found_in_trash' => 'Nessun libro trovato nel cestino',
            'parent_item_colon' => '',
            'menu_name' => 'Libreria'
        ),
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array( 'slug' => 'libreria' ),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
        'menu_position'=>5,
        'menu_icon'           => $theme . '/assets/img/admin/menu-icon-book.svg',
        'taxonomies'=>array('books_category')
    );

    register_post_type( 'books', $args );
}


//**** Categorie: LIBRI *****//
add_action( 'init', 'create_subjects_hierarchical_taxonomy_books', 0 );

function create_subjects_hierarchical_taxonomy_books() {

// Add new taxonomy, make it hierarchical like categories
//first do the translations part for GUI

  $labels = array(
    'name' => _x( 'Categorie', 'taxonomy general name' ),
    'singular_name' => _x( 'Categoria', 'taxonomy singular name' ),
    'search_items' =>  __( 'Cerca categoria' ),
    'all_items' => __( 'Tutte le categorie' ),
    'parent_item' => __( 'Genitore' ),
    'parent_item_colon' => __( 'Genitore:' ),
    'edit_item' => __( 'Modifica categoria' ),
    'update_item' => __( 'Aggiorna categoria' ),
    'add_new_item' => __( 'Add New Subject' ),
    'new_item_name' => __( 'Nuova categoria' ),
    'menu_name' => __( 'Categorie' ),
  );

// Now register the taxonomy
  register_taxonomy('books_category',array('books'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'show_in_rest' => true,
    'show_admin_column' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'genere' ),
  ));

}
