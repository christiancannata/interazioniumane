<?php
/**
 * Hero setup
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>


<?php
$date = date('Ymd');
	$sticky = new WP_Query([
		'post_type'      => 'product',
	  'posts_per_page' => -1,
	  'post_status'    => 'publish',
	  'meta_query'     => array(
	      array(
	          'key'       => 'sticky_course',
	          'meta_value'     => '1',
	      ),
				array(
					'key'       => 'end_booking',
					'value'     => $date,
					'compare'   => '>',
				),
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
 if($sticky->have_posts()):

	  ?>
	<div class="sticky-courses">
		<?php
		while($sticky->have_posts()) : $sticky->the_post();
		get_template_part( 'loop-templates/content-sticky', get_post_format() );
		endwhile;
		?>
	</div>
<?php wp_reset_postdata(); ?>
<?php endif; ?>
