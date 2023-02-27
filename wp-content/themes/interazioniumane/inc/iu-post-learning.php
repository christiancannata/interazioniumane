<?php

//***** Custom post: CORSI *****//

add_action( 'init', 'courses_posts_init' );
function courses_posts_init() {
    $theme = get_bloginfo("template_directory");
    $args = array(
        'labels' => array(
            'name' => 'Corsi',
            'singular_name' => 'Corso',
            'add_new' => 'Aggiungi nuovo',
            'add_new_item' => 'Aggiungi corso',
            'edit_item' => 'Modifica corso',
            'new_item' => 'Nuovo corso',
            'all_items' => 'Tutti i corsi',
            'view_item' => 'Visualizza corso',
            'search_items' => 'Cerca corsi',
            'not_found' =>  'Nessun corso trovato',
            'not_found_in_trash' => 'Nessun corso trovato nel cestino',
            'parent_item_colon' => '',
            'menu_name' => 'Corsi'
        ),
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        //'rewrite' => array( 'slug' => 'formazione' ),
        "rewrite" => array( "slug" => "formazione/%categorie_corsi%" ),
        'capability_type' => 'post',
        //'has_archive' => true,
        'has_archive' => 'formazione',
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array( 'title', 'thumbnail' ),
        'menu_position'=>3,
        'menu_icon'           => $theme . '/assets/img/admin/menu-icon-learning.svg',
        'taxonomies'=>array('categorie_corsi')
    );

    register_post_type( 'corsi', $args );
}

//**** Categorie: CORSI *****//
add_action( 'init', 'create_subjects_hierarchical_taxonomy', 0 );

function create_subjects_hierarchical_taxonomy() {

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
  register_taxonomy('categorie_corsi',array('corsi'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'show_in_rest' => true,
    'show_admin_column' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'formazione' ),
  ));

}


//Permalink
add_filter('post_type_link', 'cj_update_permalink_structure', 10, 2);
function cj_update_permalink_structure( $post_link, $post )
{
    if ( false !== strpos( $post_link, '%categorie_corsi%' ) ) {

      $taxonomy = 'categorie_corsi'; // Taxonomy slug.
      $taxonomy_terms = get_the_terms( $post->ID, $taxonomy );

        foreach ( $taxonomy_terms as $term ) {

            if ( $term->parent > 0 ) {
                $post_link = str_replace( '%categorie_corsi%', $term->slug, $post_link );
            }
        }
    }
    return $post_link;
}
