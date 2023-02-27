<?php

//***** Custom post: ESAMI *****//

add_action( 'init', 'exams_posts_init' );
function exams_posts_init() {
    $theme = get_bloginfo("template_directory");
    $args = array(
        'labels' => array(
            'name' => 'Esami',
            'singular_name' => 'Esame',
            'add_new' => 'Aggiungi nuovo',
            'add_new_item' => 'Aggiungi esame',
            'edit_item' => 'Modifica esame',
            'new_item' => 'Nuovo esame',
            'all_items' => 'Tutti i esami',
            'view_item' => 'Visualizza esame',
            'search_items' => 'Cerca esami',
            'not_found' =>  'Nessun esame trovato',
            'not_found_in_trash' => 'Nessun esame trovato nel cestino',
            'parent_item_colon' => '',
            'menu_name' => 'Esami'
        ),
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array( 'slug' => 'esami' ),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array( 'title' ),
        'menu_position'=>4,
        'menu_icon'           => $theme . '/assets/img/admin/menu-icon-learning.svg',
        'taxonomies'=>array('exam_category')
    );

    register_post_type( 'esami', $args );
}


//**** Categorie: ESAMI *****//
add_action( 'init', 'create_subjects_hierarchical_taxonomy_exam', 0 );

function create_subjects_hierarchical_taxonomy_exam() {

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
  register_taxonomy('exam_category',array('esami'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'show_in_rest' => true,
    'show_admin_column' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'appello' ),
  ));

}


//Aggiungi select data esame
add_filter('wpcf7_form_tag_data_option', function($n, $options, $args) {
  if (in_array('exam_date', $options)){

    if( have_rows('exams_info') ):
      while( have_rows('exams_info') ): the_row();

        $result=[];
          if( have_rows('exam_detail') ):

            while( have_rows('exam_detail') ): the_row();
            $exam_date = get_sub_field('exam_date');
            $newDate = date("j F Y", strtotime($exam_date));
            $exam_array = array($newDate);

            $result = array_merge($result, $exam_array);

      endwhile; endif;
      endwhile; endif;

      return $result;

  }
  return $n;
}, 10, 3);

//Aggiungi select tipo di master
add_filter('wpcf7_form_tag_data_option', function($n, $options, $args) {
  if (in_array('master_type', $options)){

    $exam_type = get_field('master_type');

      return $exam_type;

  }
  return $n;
}, 10, 3);
