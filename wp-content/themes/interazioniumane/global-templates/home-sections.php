<section  class="home-sections">

  <?php
			if( have_rows('home_sections') ):
					while( have_rows('home_sections') ) : the_row();
					$home_type = get_sub_field('home_type');
					$home_title = get_sub_field('home_title');
					?>

<?php if ( $home_type == 'early_booking'): ?>

  <?php
    $date = date('Ymd');
    $today = strtotime($date);

  	$query = new WP_Query([
  		'post_type'      => 'product',
  	  'posts_per_page' => -1,
  	  'post_status'    => 'publish',
  	  'meta_query'     => array(
  	      array(
            'key'       => 'end_booking',
            'value'     => $date,
            'compare'   => '>',
  	      ),
          array(
            'key'       => '_sale_price_dates_to',
            'value'     => $today,
            'compare'   => '>',
  	      )
  	  ),
      'tax_query' => array(
         array(
         'taxonomy' => 'product_cat',
         'field' => 'slug',
         'terms' => array( 'privato' ),
         'operator' => 'NOT IN',
         )
       ),
  	  'orderby'  => 'meta_value',
  	  'meta_key' => 'start_date',
  	  'order'    => 'DESC'
  	]);
   if($query->have_posts()):


  	  ?>
      <div class="books-section">
        <h2 class="books--category"><?php echo $home_title; ?></h2>
      	<div class="books-slider">
      		<?php
      		while($query->have_posts()) : $query->the_post();
          echo '<div class="book--item">';
      		get_template_part( 'loop-templates/content-course', get_post_format() );
          echo '</div>';
      		endwhile;
      		?>
      	</div>
  	</div>
  <?php wp_reset_postdata(); ?>
  <?php endif; ?>

<?php elseif ( $home_type == 'end_booking'): ?>

  <?php
    $date = date('Ymd');
    $today = strtotime($date);
    $last_minute = date('Ymd', strtotime('+9 days'));

  	$query = new WP_Query([
  		'post_type'      => 'product',
  	  'posts_per_page' => -1,
  	  'post_status'    => 'publish',

      'meta_query' => array(
        array(
          'key'       => 'end_booking',
          'value'     => $date,
          'compare'   => '>',
        ),
         array(
            'key'       => 'end_booking',
            'value'     => $last_minute,
            'compare'   => '<=',
         )
       ),
      'tax_query' => array(
         array(
         'taxonomy' => 'product_cat',
         'field' => 'slug',
         'terms' => array( 'privato' ),
         'operator' => 'NOT IN',
         )
       ),

  	  'orderby'  => 'meta_value',
  	  'meta_key' => 'start_date',
  	  'order'    => 'ASC'
  	]);
   if($query->have_posts()):


  	  ?>
      <div class="books-section">
        <h2 class="books--category"><?php echo $home_title; ?></h2>
      	<div class="books-slider">
      		<?php
      		while($query->have_posts()) : $query->the_post();
          echo '<div class="book--item">';
      		get_template_part( 'loop-templates/content-course', get_post_format() );
          echo '</div>';
      		endwhile;
      		?>
      	</div>
  	</div>
  <?php wp_reset_postdata(); ?>
  <?php endif; ?>

<?php elseif ( $home_type == 'this_month'): ?>

  <?php
    $date = date('Ymd');
    $today = strtotime($date);
    $one_month = date('Ymd', strtotime('+30 days'));

  	$query = new WP_Query([
  		'post_type'      => 'product',
  	  'posts_per_page' => -1,
  	  'post_status'    => 'publish',
  	  'meta_query'     => array(
        array(
          'key'       => 'end_booking',
          'value'     => $date,
          'compare'   => '>',
        ),
  	      array(
            'key'       => 'start_date',
            'value'     => $date,
            'compare'   => '>=',
  	      ),
          array(
            'key'       => 'start_date',
            'value'     => $one_month,
            'compare' => '<=',
  	      )
  	  ),
      'tax_query' => array(
         array(
         'taxonomy' => 'product_cat',
         'field' => 'slug',
         'terms' => array( 'privato' ),
         'operator' => 'NOT IN',
         )
       ),
  	  'orderby'  => 'meta_value',
  	  'meta_key' => 'start_date',
  	  'order'    => 'ASC'
  	]);
   if($query->have_posts()):


  	  ?>
      <div class="books-section">
        <h2 class="books--category"><?php echo $home_title; ?></h2>
      	<div class="books-slider">
      		<?php
      		while($query->have_posts()) : $query->the_post();
          echo '<div class="book--item">';
      		get_template_part( 'loop-templates/content-course', get_post_format() );
          echo '</div>';
      		endwhile;
      		?>
      	</div>
  	</div>
  <?php wp_reset_postdata(); ?>
  <?php endif; ?>




  <?php endif;?>

<?php endwhile; endif; ?>

</section
