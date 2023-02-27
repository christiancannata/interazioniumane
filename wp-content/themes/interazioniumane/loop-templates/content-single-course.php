<?php
/**
 * Single post partial template
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$current_user = wp_get_current_user();
global $current_user;

//Questo utente ha il permesso di vedere i materiali?
$person_repeater = get_field('user_permission');
$search_array = '';
if($person_repeater) {
	$search_array = $person_repeater;
}


//Course informations
$course_number_sumbissions = get_field('course_number_sumbissions');
$course_subtitle = get_field('course_subtitle');
$course_overview = get_field('course_overview');
$course_goals = get_field('course_goals');
$course_location = get_field('course_location');
$course_address = get_field('course_address');
$course_hours = get_field('course_hours');
//$course_number_students = count($person_repeater);

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

// Ricorda i dati per l'iscrizione
$page_id = get_the_ID();

$_SESSION['from-course']= 'yes';
$_SESSION['title']= get_the_title();
$_SESSION['thumb']= get_the_post_thumbnail_url();
$_SESSION['price'] = '';
if($sale_price) {
	$_SESSION['price'] = number_format($sale_price, 0, ',', '.');
} else {
	$_SESSION['price'] = number_format($course_price, 0, ',', '.');
}

$_SESSION['online'] = '';
if( get_field('online_course') ){
		$_SESSION['online'] = 'Online';
}

$_SESSION['category'] = '';
$taxonomy = 'product_cat'; // Taxonomy slug.
$terms = get_the_terms( $post->ID, $taxonomy );
$children = '';
if($terms) {
foreach ( $terms as $term ) {
	$getslug = get_term( $term->parent, $taxonomy );
	$slug = $getslug->slug;
		if( $slug == 'livello' ) {
				$children = $term->name;
				$_SESSION['category'] = $children;
		}
	}
}

$_SESSION['type'] = '';
if($terms) {
foreach ( $terms as $term ) {
	$getslug = get_term( $term->parent, $taxonomy );
	$slug = $getslug->slug;
		if( $slug == 'argomento' ) {
				$children = $term->name;
				$_SESSION['type'] = $children;
		}
	}
}

$_SESSION['date'] = '';
if($start_date) {
	$_SESSION['date'] = $start_day . ' ' . $first_month . ' ' . $start_year;
}

$thumb_id = get_post_thumbnail_id();
$thumb_url_array = wp_get_attachment_image_src($thumb_id, 'full', true);
$thumb_url = $thumb_url_array[0];

?>
<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">

<div class="course-fixed">
	<div class="course-fixed--flex">
		<div class="course-fixed--sx">
			<h4 class="course-fixed--title"><?php echo the_title(); ?></h4>
		</div>
		<div class="course-fixed--dx">
		<div class="course-fixed--price">
			<?php //Course price
			if( $course_price ) {
				if( get_field('early_booking') ) {

						if( $current_date <= $end_early_booking) {
							if( $sale_price ) {

								echo '<span class="course--prices">';
								echo '<span class="course--price sale"><span class="course--price--symbol">€</span><span class="course--price--value">'. number_format($sale_price, 0, ',', '.'). '</span></span>';
								echo '<span class="course--price old"><span class="course--price--symbol">€</span><span class="course--price--value">'. number_format($course_price, 0, ',', '.'). '</span></span>';
								echo '</span>';
								echo '<span class="course--early"><strong>Early Booking</strong> fino al ' . $end_early_booking_day . ' ' . $early_month . ' ' . $end_early_booking_year .'</span>';
							}
						} else {
							echo '<span class="course--price"><span class="course--price--symbol">€</span><span class="course--price--value">'. number_format($course_price, 0, ',', '.'). '</span></span>';
							if($current_date > $end_booking) {

								$date1 = new DateTime($current_date);
								$date2 = new DateTime($end_booking);
								$diff = $date2->diff($date1)->format("%a");
								$days = intval($diff);
								if($days < 10) {
										echo '<span class="course--early">Hai <strong>' . $days . ' giorni</strong> per iscriverti!</span>';
								}

						}


						}
				} else {
						echo '<span class="course--price"><span class="course--price--symbol">€</span><span class="course--price--value">'. number_format($course_price, 0, ',', '.'). '</span></span>';
						if($current_date < $end_booking) {
							$date1 = new DateTime($current_date);
							$date2 = new DateTime($end_booking);
							$diff = $date2->diff($date1)->format("%a");
							$days = intval($diff);
							if($days < 11) {
								echo '<span class="course--early">Hai <strong>' . $days  .' giorni</strong> per iscriverti!</span>';
							}
						}
				}
			}
			?>
		</div>
		<div class="course-fixed--button">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>accedi" class="dstr-button dstr-button-primary dstr-button-small" title="Iscriviti">Iscriviti</a>
		</div>
		</div>
	</div>
</div>



	<header class="course__hero">
		<div class="small-container">
			<div class="course__hero--flex">
				<?php if ( has_post_thumbnail() ): ?>
					<div class="course__hero--image">
						<div class="image-cover">
							<div class="image-background" style="background-image:url('<?php echo $thumb_url; ?>');">
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
									$slug = $getslug->slug;
				            if( $slug == 'livello' ) {// Parent ID (Livello)
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
					<h1 class="course__hero--title"><?php echo the_title(); ?></h1>
					<?php if($course_subtitle): ?>
						<h2 class="course__hero--subtitle"><?php echo $course_subtitle; ?></h2>
					<?php endif; ?>
					<div class="course__hero--bottom">
						<div class="course__hero--price">
							<?php //Course price
							if( $course_price ) {
								if( get_field('early_booking') ) {

										if( $current_date <= $end_early_booking) {
											if( $sale_price ) {

												echo '<span class="course--prices">';
												echo '<span class="course--price sale"><span class="course--price--symbol">€</span><span class="course--price--value">'. number_format($sale_price, 0, ',', '.'). '</span></span>';
												echo '<span class="course--price old"><span class="course--price--symbol">€</span><span class="course--price--value">'. number_format($course_price, 0, ',', '.'). '</span></span>';
												echo '</span>';
												echo '<span class="course--early"><strong>Early Booking</strong> fino al ' . $end_early_booking_day . ' ' . $early_month . ' ' . $end_early_booking_year .'</span>';
											}
										} else {
											echo '<span class="course--price"><span class="course--price--symbol">€</span><span class="course--price--value">'. number_format($course_price, 0, ',', '.'). '</span></span>';
											if($current_date > $end_booking) {

												$date1 = new DateTime($current_date);
											  $date2 = new DateTime($end_booking);
											  $diff = $date2->diff($date1)->format("%a");
											  $days = intval($diff);
												if($days < 10) {
												  echo '<span class="course--early">Hai <strong>' . $days . ' giorni</strong> per iscriverti!</span>';
												}

										}


										}
								} else {
										echo '<span class="course--price"><span class="course--price--symbol">€</span><span class="course--price--value">'. number_format($course_price, 0, ',', '.'). '</span></span>';
										if($current_date < $end_booking) {
											$date1 = new DateTime($current_date);
										  $date2 = new DateTime($end_booking);
										  $diff = $date2->diff($date1)->format("%a");
										  $days = intval($diff);
											if($days < 11) {
												echo '<span class="course--early">Hai <strong>' . $days  .' giorni</strong> per iscriverti!</span>';
											}
										}
								}
							}
							?>
						</div>
						<div class="course__hero--button">
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>accedi" class="dstr-button dstr-button-primary" title="Iscriviti">Iscriviti</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</header>

	<div class="course__toolbar">
			<div class="small-container">
				<div class="course__toolbar--flex">

					<?php if($start_date): ?>
						<?php
						$date1 = new DateTime($start_date);
					  $date2 = new DateTime($end_date);
					 // $days = intval($diff);
						$interval = $date1->diff($date2);

						$days = $interval->format('%d');
						$months = $interval->format('%m');
						$full_month = $date2->modify( '+1 month' );
						$full_month_diff = $date1->diff($full_month);
						$full_month_output = $full_month_diff->format('%m');
						$years = $interval->format('%y');


						echo '<div class="course__toolbar--item">';
						echo '<span class="toolbar--title">' . $start_day . ' ' . $first_month . ' ' . $start_year . '</span>';
						echo '<span class="toolbar--label">Data di inizio</span>';
						echo '</div>';

						echo '<div class="course__toolbar--item">';
						if($years == 1) {
							echo '<span class="toolbar--title">'.$years. ' anno</span>';
						} else if($years > 0) {
							echo '<span class="toolbar--title">'.$years. ' anni</span>';
						}else if($months > 0 && $days < 15) {
							echo '<span class="toolbar--title">'.$months.' mesi</span>';
						} else if($months > 0 && $days > 14) {
							echo '<span class="toolbar--title">'.$full_month_output.' mesi</span>';
						}	else if($years == 0 && $months == 0) {
							echo '<span class="toolbar--title">'.$days.' giorni</span>';
						}
						echo '<span class="toolbar--label">Durata</span>';
						echo '</div>';
						?>
					<?php endif; ?>

					<?php if($course_number_sumbissions && $person_repeater): ?>
					<div class="course__toolbar--item">
						<span class="toolbar--title">
							<span class="course--submissions--total"><?php echo $course_number_sumbissions; ?></span>
						</span>
						<span class="toolbar--label">Posti disponibili</span>
					</div>
					<?php endif; ?>

					<?php
				        $taxonomy = 'product_cat'; // Taxonomy slug.
				        $terms = get_the_terms( $post->ID, $taxonomy );
				        $children = '';
								echo '<div class="course__toolbar--item">';
								echo '<span class="toolbar--title">';
								if($terms) {
				        foreach ( $terms as $term ) {
									$getslug = get_term( $term->parent, $taxonomy );
									$slug = $getslug->slug;
										if( $slug == 'argomento' ) { // Parent ID (Livello)
				                $children = $term->name; ?>

					                <span><?php echo $children; ?></span>

				            <?php }
				        }
							}
								echo '</span>';
								echo '<span class="toolbar--label">Categoria</span>';
								echo '</div>';
				    ?>


				</div>
			</div>
	</div>
		<?php

		$teachers = get_field('course_teachers');

		?>
		<?php if( $teachers ): ?>
			<section class="course-teachers">
				<h3 class="course-teachers--title">Docenti</h3>
			<div class="course-teachers--flex">
			<?php foreach( $teachers as $teacher ):
				$thumb_id = get_post_thumbnail_id($teacher->ID);
				$thumb_url_array = wp_get_attachment_image_src($thumb_id, 'thumb', true);
				$thumb_url = $thumb_url_array[0];
				?>
				<div class="course-teachers--item">
				 	<div class="course-teachers--image">
						<a href="<?php echo get_permalink( $teacher->ID ); ?>" title="Guarda il Curriculum Vitae di <?php echo get_the_title( $teacher->ID ); ?>">
							<img src="<?php echo $thumb_url; ?>" alt="<?php echo get_the_title( $teacher->ID ); ?>" />
						</a>
					</div>
					<div class="course-teachers--description">
						<h4 class="course-teachers--name"><?php echo get_the_title( $teacher->ID ); ?></h4>
						<p class="course-teachers--bio"><?php echo get_the_excerpt( $teacher->ID ); ?></p>
						<a href="<?php echo get_permalink( $teacher->ID ); ?>" class="simple-link"title="Guarda il Curriculum Vitae di <?php echo get_the_title( $teacher->ID ); ?>">Curriculum Vitae</a>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
			</section>
		<?php endif; ?>










	<div class="course__container">
		<div class="typography-content">
			<div class="entry-content">

				<?php if($course_overview): ?>
					<div class="course__section">
						<h4 class="course__section--title">Panoramica</h4>
						<div class="ffix"></div>
						<?php echo $course_overview; ?>
					</div>
				<?php endif; ?>

				<?php	if( have_rows('course_goals') ):
						echo '<div class="course__section">';
						echo '<h4 class="course__section--title">Obiettivi</h4>';
						echo '<div class="ffix"></div>';
						echo '<div class="course__section__list">';
					    while( have_rows('course_goals') ) : the_row();
					        $course_goals_item = get_sub_field('course_goals_item');
									echo '<div class="course__section__list--item">';
					        echo $course_goals_item;
									echo '</div>';

					    endwhile;
							echo '</div>';
							echo '</div>';
						endif;?>

						<?php	if( have_rows('course_paragraph') ):

							    while( have_rows('course_paragraph') ) : the_row();

										$course_paragraph_title = get_sub_field('course_paragraph_title');
										$course_paragraph_text = get_sub_field('course_paragraph_text');
										echo '<div class="course__section">';
										echo '<h4 class="course__section--title">'. $course_paragraph_title .'</h4>';
										echo '<div class="ffix"></div>';
							        echo $course_paragraph_text;
										echo '</div>';
							    endwhile;
								endif;?>

			</div>
		</div>
	</div>







</article><!-- #post-## -->
