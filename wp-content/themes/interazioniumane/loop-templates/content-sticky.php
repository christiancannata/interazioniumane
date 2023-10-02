<?php
/**
 * Post rendering content according to caller of get_template_part
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


$current_date = date('Ymd');

$start_date = get_field('start_date');
$end_date = get_field('end_date');
$end_booking = get_field('end_booking');


$start_year = substr($start_date, 0, 4);
$start_month = substr($start_date, 4, -2);
$start_day = substr($start_date, 6, 8);

$end_year = substr($end_date, 0, 4);
$end_month = substr($end_date, 4, -2);
$end_day = substr($end_date, 6, 8);

$course_price = get_field('course_price');
$sale_price = get_field('sale_price');
$end_early_booking = get_field('end_early_booking');

$end_early_booking_year = substr($end_early_booking, 0, 4);
$end_early_booking_month = substr($end_early_booking, 4, -2);
$end_early_booking_day = substr($end_early_booking, 6, 8);

$course_subtitle = get_field('course_subtitle');

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

$last_month = '';
if($end_month === '01') {
	$last_month = 'Gennaio';
} else if($end_month === '02') {
	$last_month = 'Febbraio';
} else if($end_month === '03') {
	$last_month = 'Marzo';
} else if($end_month === '04') {
	$last_month = 'Aprile';
} else if($end_month === '05') {
	$last_month = 'Maggio';
} else if($end_month === '06') {
	$last_month = 'Giugno';
} else if($end_month === '07') {
	$last_month = 'Luglio';
} else if($end_month === '08') {
	$last_month = 'Agosto';
} else if($end_month === '09') {
	$last_month = 'Settembre';
} else if($end_month === '10') {
	$last_month = 'Ottobre';
} else if($end_month === '11') {
	$last_month = 'Novembre';
} else if($end_month === '12') {
	$last_month = 'Dicembre';
}

$early_month = '';
if($end_early_booking_month === '01') {
	$early_month = 'Gennaio';
} else if($end_early_booking_month === '02') {
	$early_month = 'Febbraio';
} else if($end_early_booking_month === '03') {
	$early_month = 'Marzo';
} else if($end_early_booking_month === '04') {
	$early_month = 'Aprile';
} else if($end_early_booking_month === '05') {
	$early_month = 'Maggio';
} else if($end_early_booking_month === '06') {
	$early_month = 'Giugno';
} else if($end_early_booking_month === '07') {
	$early_month = 'Luglio';
} else if($end_early_booking_month === '08') {
	$early_month = 'Agosto';
} else if($end_early_booking_month === '09') {
	$early_month = 'Settembre';
} else if($end_early_booking_month === '10') {
	$early_month = 'Ottobre';
} else if($end_early_booking_month === '11') {
	$early_month = 'Novembre';
} else if($end_early_booking_month === '12') {
	$early_month = 'Dicembre';
}

$thumb_id = get_post_thumbnail_id();
$thumb_url_array = wp_get_attachment_image_src($thumb_id, 'medium', true);
$thumb_url = $thumb_url_array[0];
?>

<article class="sticky-post--item" id="post-<?php the_ID(); ?>">

<header class="course__hero">
	<div class="small-container">
		<div class="course__hero--flex">
			<?php if ( has_post_thumbnail() ): ?>
				<div class="course__hero--image">
					<div class="image-cover">
						<?php if($start_date):?>
						<div class="ehi-label hero--data-info">
							Dal <?php echo $start_day . ' ' . $first_month . ' ' . $start_year;  ?>
						</div>
					<?php endif;?>
						<div class="image-background" style="background-image:url('<?php the_post_thumbnail_url();?>');">
							<img src="<?php echo get_template_directory_uri(); ?>/assets/img/elements/default.png" title="<?php the_title(); ?>" />
						</div>
					</div>
				</div>
			<?php endif; ?>
			<div class="course__hero--info">
				<div class="course__hero--categories">
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
											echo '<span class="online-label">Online</span>';
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

				<h3 class="course__hero--title"><?php echo the_title(); ?></h3>
				<?php if($course_subtitle): ?>
					<p class="course__hero--subtitle"><?php echo $course_subtitle; ?></p>
				<?php endif; ?>
				<div class="course__hero--bottom">

					<div class="course__hero--button">
						<a href="<?php the_permalink(); ?>" class="dstr-button dstr-button-primary" title="Scopri di più">Scopri di più</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</header>

</article>
