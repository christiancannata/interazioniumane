<?php

//***** Custom post: LAVORO *****//

add_action( 'init', 'works_posts_init' );
function works_posts_init() {
    $theme = get_bloginfo("template_directory");
    $args = array(
        'labels' => array(
            'name' => 'Annunci di lavoro',
            'singular_name' => 'Lavoro',
            'add_new' => 'Aggiungi nuovo',
            'add_new_item' => 'Aggiungi lavoro',
            'edit_item' => 'Modifica lavoro',
            'new_item' => 'Nuovo lavoro',
            'all_items' => 'Tutti i lavori',
            'view_item' => 'Visualizza lavoro',
            'search_items' => 'Cerca lavori',
            'not_found' =>  'Nessun lavoro trovato',
            'not_found_in_trash' => 'Nessun lavoro trovato nel cestino',
            'parent_item_colon' => '',
            'menu_name' => 'Lavoro'
        ),
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array( 'slug' => 'lavoro' ),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array( 'title', 'editor' ),
        'menu_position'=>4,
        'menu_icon'           => $theme . '/assets/img/admin/menu-icon-work.svg',
        'taxonomies'=>array('work_category')
    );

    register_post_type( 'lavoro', $args );
}


//**** Categorie: LAVORO *****//
add_action( 'init', 'create_subjects_hierarchical_taxonomy_work', 0 );

function create_subjects_hierarchical_taxonomy_work() {

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
  register_taxonomy('work_category',array('lavoro'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'show_in_rest' => true,
    'show_admin_column' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'professione' ),
  ));

}
