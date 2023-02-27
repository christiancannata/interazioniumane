<?php
/**
 * Search results partial template
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

global $product;
$current_date = date('Ymd');

$end_booking = get_field('end_booking');
$start_date = get_field('start_date');
$start_year = substr($start_date, 0, 4);
$start_month = substr($start_date, 4, -2);
$start_day = substr($start_date, 6, 8);

//Mesi
$first_month = '';
if($start_month === '01') {
	$first_month = 'Gennaio';
} else if($start_month === '02') {
	$first_month = 'Febbraio';
} else if($start_month === '03') {
	$first_month = 'Marzo';
} else if($start_month === '04') {
	$first_month = 'Aprile';
} else if($start_month === '05') {
	$first_month = 'Maggio';
} else if($start_month === '06') {
	$first_month = 'Giugno';
} else if($start_month === '07') {
	$first_month = 'Luglio';
} else if($start_month === '08') {
	$first_month = 'Agosto';
} else if($start_month === '09') {
	$first_month = 'Settembre';
} else if($start_month === '10') {
	$first_month = 'Ottobre';
} else if($start_month === '11') {
	$first_month = 'Novembre';
} else if($start_month === '12') {
	$first_month = 'Dicembre';
}

$course_price = '';
$sale_price = $product->get_sale_price();

if($sale_price) {
	$course_price = $product->get_regular_price();
} else {
	$course_price = $product->get_price();
}

$end_early_booking = ( $date = get_post_meta( $post->ID, '_sale_price_dates_to', true ) ) ? date_i18n( 'Ymd', $date ) : '';

$thumb_id = get_post_thumbnail_id();
$thumb_url_array = wp_get_attachment_image_src($thumb_id, 'full', true);
$thumb_url = $thumb_url_array[0];
$course_subtitle = get_field('course_subtitle');
?>


	<article class="course--box" id="post-<?php the_ID(); ?>">
		<a href="<?php the_permalink(); ?>" class="course--box__link" title="Scopri di più su <?php the_title(); ?>" style="background-image:url('<?php the_post_thumbnail_url();?>');">

			<?php if( get_field('sold_out_course') ): ?>
				<span class="ehi-label sold-out">SOLD OUT</span>
			<?php elseif( $current_date > $end_booking): ?>
				<span class="ehi-label closed-course">ISCRIZIONI CHIUSE</span>
			<?php endif; ?>


			<img src="<?php echo get_template_directory_uri(); ?>/assets/img/elements/default.png" class="course--box__img" alt="<?php the_title(); ?>" />
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
							$slug ="";
							if($term->parent) {
								$slug = $getslug->slug;
							}
							if( $slug == 'livello' ) { // Parent ID (Livello)
										$livello = $term->name;
										echo '<span>'. $livello. '</span>';
										if( get_field('online_course')) {
											echo '<span class="online-label"> Online</span>';
										}
							}

						}
					}
					?>
				<?php
						$taxonomy = 'product_cat'; // Taxonomy slug.
						$terms = get_the_terms( $post->ID, $taxonomy );
						$children = '';
						if($terms) {
						foreach ( $terms as $term ) {
							$getslug = get_term( $term->parent, $taxonomy );
							$slug ="";
							if($term->parent) {
								$slug = $getslug->slug;
							}
							if( $slug == 'argomento' ) { // Parent ID (Livello)
										$argomento = $term->name;
										echo '<span class="last">'. $argomento. '</span>';
							}

						}
					}
					?>
				</div>

				<h3 class="course--box__title"><a href="<?php the_permalink(); ?>" class="course--box__title--link" title="Scopri di più su <?php the_title(); ?>"><?php the_title(); ?></a></h3>
				<?php if($course_subtitle): ?>
					<h2 class="course__hero--subtitle"><?php echo $course_subtitle; ?></h2>
				<?php endif; ?>
				<?php if($start_date): ?>
				<p class="course--box__start"><span>Data di inizio:</span> <strong><?php echo $start_day . ' ' . $first_month . ' ' . $start_year; ?></strong></p>
				<?php endif; ?>
			</header><!-- .entry-header -->


		</div>

	</article><!-- #post-## -->
