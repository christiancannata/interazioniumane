<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;
$product = wc_get_product( $post->ID );

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
$course_location_hq = get_field('course_location_hq');
$course_address = get_field('course_address');
$course_hours = get_field('course_hours');
$course_credits = get_field('course_credits');
$course_credits_number = get_field('course_credits_number');
$calendar_file = get_field('calendar_file');
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

$course_price = '';
$sale_price = $product->get_sale_price();

if($sale_price) {
	$course_price = $product->get_regular_price();
} else {
	$course_price = $product->get_price();
}

$end_early_booking = ( $date = get_post_meta( $post->ID, '_sale_price_dates_to', true ) ) ? date_i18n( 'Ymd', $date ) : '';

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

$thumb_id = get_post_thumbnail_id();
$thumb_url_array = wp_get_attachment_image_src($thumb_id, 'full', true);
$thumb_url = $thumb_url_array[0];

// Ricorda i dati per l'iscrizione
$page_id = get_the_ID();

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_all_notices - 10
 */
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // WPCS: XSS ok.
	return;
}
?>



<div id="product-<?php the_ID(); ?>">
<div class="course-fixed">
	<div class="course-fixed--flex">
		<div class="course-fixed--sx">
			<h4 class="course-fixed--title"><?php echo the_title(); ?></h4>
		</div>
		<div class="course-fixed--dx">

		<?php if( $current_date < $end_booking): ?>
			<?php if(!get_field('sold_out_course')): ?>
				<div class="course-fixed--button">
					<?php if( str_contains($product->get_title(), 'II livello') ): ?>
						<a href="<?php echo $product->add_to_cart_url(); ?>" class="dstr-button dstr-button-primary dstr-button-small" title="Iscriviti">Iscriviti</a>
					<?php elseif(get_field('link_esterno')): ?>
							<a href="<?php echo the_field('link_esterno_url'); ?>" target="_blank" class="dstr-button dstr-button-primary dstr-button-small" title="Iscriviti">Iscriviti</a>
					<?php else: ?>
						<a href="<?php echo $product->add_to_cart_url(); ?>" class="dstr-button dstr-button-primary dstr-button-small" title="Iscriviti">Iscriviti</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		</div>
	</div>
</div>


<header class="course__hero">
	<div class="small-container">
		<div class="course__hero--flex">
			<?php if ( has_post_thumbnail() ): ?>
				<div class="course__hero--image">
					<div class="image-cover">

						<?php if( get_field('sold_out_course') ): ?>
							<span class="ehi-label sold-out">SOLD OUT</span>
						<?php elseif( $current_date > $end_booking): ?>
							<span class="ehi-label closed-course">ISCRIZIONI CHIUSE</span>
						<?php endif; ?>
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
							if( $end_early_booking ) {

									if( $current_date <= $end_early_booking) {
										if( $sale_price ) {

											echo '<span class="course--prices">';
											echo '<span class="course--price sale"><span class="course--price--symbol">€</span><span class="course--price--value">'. number_format($sale_price, 0, ',', '.'). '</span></span>';
											echo '<span class="course--price old"><span class="course--price--symbol">€</span><span class="course--price--value">'. number_format($course_price, 0, ',', '.'). '</span></span>';
											/*if( get_field('course_vat') ){
												echo '<span class="course--price__vat">+ IVA</span>';
											}*/
											echo '</span>';
											echo '<span class="course--early"><strong>Early Booking</strong> fino al ' . $end_early_booking_day . ' ' . $early_month . ' ' . $end_early_booking_year .'</span>';
										}
									} else {

										echo '<span class="course--price"><span class="course--price--symbol">€</span><span class="course--price--value">'. number_format($course_price, 0, ',', '.'). '</span>';
										/*if( get_field('course_vat') ){
											echo '<span class="course--price__vat">+ IVA</span>';
										}*/
										echo'</span>';

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
									echo '<span class="course--price"><span class="course--price--symbol">€</span><span class="course--price--value">'. number_format($course_price, 0, ',', '.'). '</span>';
									/*if( get_field('course_vat') ){
										echo '<span class="course--price__vat">+ IVA</span>';
									}*/
									echo'</span>';
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
						<?php if( $current_date < $end_booking): ?>
							<?php if(!get_field('sold_out_course')): ?>
								<div class="course__hero--button">
									<?php if( str_contains($product->get_title(), 'II livello') ): ?>
										<a href="<?php echo $product->add_to_cart_url(); ?>" class="dstr-button dstr-button-primary" title="Iscriviti">Iscriviti</a>
									<?php elseif(get_field('link_esterno')): ?>
									<a href="<?php echo the_field('link_esterno_url'); ?>" target="_blank" class="dstr-button dstr-button-primary" title="Iscriviti">Iscriviti</a>
									<?php else: ?>
										<a href="<?php echo $product->add_to_cart_url(); ?>" class="dstr-button dstr-button-primary" title="Iscriviti">Iscriviti</a>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						<?php endif; ?>

				</div>
				<?php if($current_date >= $end_booking): ?>
					<div class="notify-me">
						<?php echo do_shortcode('[contact-form-7 title="Sold out"]'); ?>
					</div>
				<?php elseif(get_field('sold_out_course')): ?>
					<div class="notify-me">
						<?php echo do_shortcode('[contact-form-7 title="Sold out"]'); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</header>


<div class="course__toolbar">
		<div class="small-container">
			<div class="course__toolbar--line">
			<div class="course__toolbar--flex">

				<?php if($start_date): ?>
					<?php
					$date1 = new DateTime($start_date);
					$date2 = new DateTime($end_date);
				 // $days = intval($diff);
					$interval = $date1->diff($date2);

					$days = $interval->format('%d');
					$days = $days + 1;
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

				<?php if($course_number_sumbissions): ?>
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
								$slug ="";
								if($term->parent) {
									$slug = $getslug->slug;
								}
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
					<a href="<?php echo get_permalink( $teacher->ID ); ?>" class="teacher-profile-picture" title="Guarda il Curriculum Vitae di <?php echo get_the_title( $teacher->ID ); ?>" style="background-image: url(<?php echo $thumb_url; ?>);">

					</a>
				</div>
				<div class="course-teachers--description">
					<h4 class="course-teachers--name"><?php echo get_the_title( $teacher->ID ); ?></h4>
					<p class="course-teachers--bio"><?php echo the_field('teacher_role', $teacher->ID); ?></p>
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

			<?php	if( have_rows('video_list') ):
					echo '<div class="video_course_section">';
						while( have_rows('video_list') ) : the_row();
								$codice_youtube = get_sub_field('codice_youtube');
								echo '<div class="video_course_section__box">';
								echo '<div class="videoWrapper">';
								echo $codice_youtube;
								echo '</div>';
								echo '</div>';
						endwhile;
						echo '</div>';
					endif;?>

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


					<?php if($course_credits): ?>
						<div class="course__section">
							<h4 class="course__section--title">Crediti ECM</h4>
							<div class="ffix"></div>
							<p>Al termine del corso sarà rilasciato un attestato di frequenza. Sono previsti <?php if($course_credits_number) { echo ' '. $course_credits_number . ' '; } ?>crediti ECM.</p>
						</div>
					<?php endif; ?>


						<div class="course__section">
							<h4 class="course__section--title">Orari e luogo</h4>
							<div class="ffix"></div>
							<p class="course_descr">
							<?php if( get_field('online_course') ): ?>
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
																<span>Questo <span style="text-transform:lowercase;"><?php echo $children; ?></span> si terrà online.</span><br/>
													<?php }
											}
										}
									?>
							<?php else: ?>
								<?php if($course_location_hq): ?>
									<span><strong><?php echo $course_location_hq; ?></strong></span><br/>
								<?php endif; ?>
								<?php if($course_location): ?>
									<span><?php echo $course_address; ?> — <?php echo $course_location; ?></span><br/>
								<?php endif; ?>
							<?php endif; ?>
							<?php if($course_hours): ?>
								<br/><span><?php echo $course_hours; ?></span><br/>
							<?php endif; ?>
							</p>


							<?php	if( have_rows('course_calendar') ):

								echo '<div class="course-calendar">';

										while( have_rows('course_calendar') ) : the_row();
												$course_calendar_date = get_sub_field('course_calendar_date');
												$course_calendar_date_day = substr($course_calendar_date,6, 8);
												$course_calendar_date_month = substr($course_calendar_date, 4, -2);

												$course_calendar_month_full = '';
												if($course_calendar_date_month === '01') {
													$course_calendar_month_full = 'Gennaio';
												} else if($course_calendar_date_month === '02') {
													$course_calendar_month_full = 'Febbraio';
												} else if($course_calendar_date_month === '03') {
													$course_calendar_month_full = 'Marzo';
												} else if($course_calendar_date_month === '04') {
													$course_calendar_month_full = 'Aprile';
												} else if($course_calendar_date_month=== '05') {
													$course_calendar_month_full = 'Maggio';
												} else if($course_calendar_date_month === '06') {
													$course_calendar_month_full = 'Giugno';
												} else if($course_calendar_date_month === '07') {
													$course_calendar_month_full = 'Luglio';
												} else if($course_calendar_date_month === '08') {
													$course_calendar_month_full = 'Agosto';
												} else if($course_calendar_date_month === '09') {
													$course_calendar_month_full = 'Settembre';
												} else if($course_calendar_date_month === '10') {
													$course_calendar_month_full = 'Ottobre';
												} else if($course_calendar_date_month === '11') {
													$course_calendar_month_full = 'Novembre';
												} else if($course_calendar_date_month === '12') {
													$course_calendar_month_full = 'Dicembre';
												}

												echo '<div class="course-calendar__item">';
												echo '<span class="big">' .$course_calendar_date_day.'</span>';
												echo '<span class="small">' .$course_calendar_month_full.'</span>';
												echo '</div>';

										endwhile;
										echo '</div>';
									endif;?>

									<?php if($calendar_file):?>
										<div class="calendar-download" style="margin-top:8px;">
											<a class="arrow-link" href="<?php echo $calendar_file; ?>" title="Scarica il calendario" target="_blank" >Scarica il calendario</a>
										</div>
									<?php endif;?>

						</div>



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

</div><!-- end product -->



<?php do_action( 'woocommerce_after_single_product' ); ?>
