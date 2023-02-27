<?php
/**
 * Post rendering content according to caller of get_template_part
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$current_date = date('Ymd');
$course_price = get_field('course_price');
$sale_price = get_field('sale_price');
$end_early_booking = get_field('end_early_booking');

?>

<article class="course--box" id="post-<?php the_ID(); ?>">
	<a href="<?php the_permalink(); ?>" class="course--box__link" title="Scopri di più su <?php the_title(); ?>">
		<?php //Course price
		if( $course_price ) {
			if( get_field('early_booking') ) {
					if( $current_date <= $end_early_booking) {
						if( $sale_price ) {
							$percent = (($course_price  - $sale_price)*100) /$course_price ;
							$percent_r = round($percent);
							echo '<span class="sales-label">-' . $percent_r . '<span class="course--percentage--symbol">%</span></span>';
						}
					}
			}
		}
		?>

		<img src="<?php the_post_thumbnail_url('medium'); ?>" class="course--box__img" alt="<?php the_title(); ?>" />
	</a>
	<div class="course--box__body">
		<header class="course--box__header">

		<div class="course--box__category">
		<?php
	        $taxonomy = 'product_cat'; // Taxonomy slug.
	        $terms = get_the_terms( $post->ID, $taxonomy );
	        $children = '';
					if($terms) {
	        foreach ( $terms as $term ) {
						$getslug = get_term( $term->parent, $taxonomy );
						$slug = $getslug->slug;
	            if( $slug == 'livello' ) {  // Parent ID (Livello)
	                $children = $term->name; ?>

		                <span><?php echo $children; ?></span>

	            <?php }
	        }
				}
	    ?>
			<?php if( get_field('online_course') ): ?>
				<span class="online-label">Online</span>
			<?php endif; ?>
			</div>

			<h2 class="course--box__title"><a href="<?php the_permalink(); ?>" class="course--box__title--link" title="Scopri di più su <?php the_title(); ?>"><?php the_title(); ?></a></h2>
		</header><!-- .entry-header -->


	</div>

</article><!-- #post-## -->
