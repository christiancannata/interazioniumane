<?php
/**
 * The template for displaying all single posts
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();
$container = get_theme_mod( 'understrap_container_type' );



//Corsi attivi
//$featured_posts = get_field('course_teachers');
$teacher_role = get_field('teacher_role');


?>



<?php while ( have_posts() ) : the_post(); ?>


	<div class="wrapper" id="author-wrapper">

		<div class="small-container" id="content" tabindex="-1">


				<main class="site-main" id="main">

						<header class="page-header teacher-profile--header" data-aos="fade-down" data-aos-duration="1000" data-aos-delay="0">

								<?php
								$thumb_id = get_post_thumbnail_id();
								$thumb_url_array = wp_get_attachment_image_src($thumb_id, 'medium', true);
								$thumb_url = $thumb_url_array[0];
								 if($thumb_url): ?>

								 <div class="teacher-profile--avatar" style="background-image: url('<?php echo $thumb_url; ?>')"></div>

								<?php endif; ?>

							<div class="teacher-profile--info">
								<h1 class="page-title"><?php echo the_title(); ?></h1>
								<div class="taxonomy-description"><?php echo $teacher_role; ?></div>
							</div>
						</header><!-- .page-header -->


					<section class="teacher-profile">




						<div class="teacher-profile__curriculum">

								<div class="teacher-profile__curriculum--item">
									<?php	if( have_rows('teacher_curriculum') ):

										    while( have_rows('teacher_curriculum') ) : the_row();

													$teacher_curriculum_title = get_sub_field('teacher_curriculum_title');
													$teacher_curriculum_text = get_sub_field('teacher_curriculum_text');
													echo '<div class="course__section">';
													echo '<h4 class="course__section--title">'. $teacher_curriculum_title .'</h4>';
													echo '<div class="ffix"></div>';
										        echo $teacher_curriculum_text;
													echo '</div>';
										    endwhile;
											endif;?>
								</div>

						</div>

						<?php

						$teacher_courses = get_posts(array(
							'post_type' => 'product',
							'meta_query' => array(
								array(
									'key' => 'course_teachers', // name of custom field
									'value' => '"' . get_the_ID() . '"', // matches exactly "123", not just 123. This prevents a match for "1234"
									'compare' => 'LIKE'
								)
							)
						));
						?>
						<?php if( $teacher_courses ): ?>
							<section class="teacher-courses">
								<h3 class="teacher-courses--title">I suoi corsi</h3>
								<div class="teacher-courses--flex">
							<?php foreach( $teacher_courses as $course ):
								//setup_postdata(  $course->ID );
                                    
								$thumb_id = get_post_thumbnail_id($course->ID);
								$thumb_url_array = wp_get_attachment_image_src($thumb_id, 'medium', true);
								$thumb_url = $thumb_url_array[0];
								$current_date = date('Ymd');
                                
                                $start_date = get_field('start_date', $course->ID);
								$end_booking = get_field('end_booking', $course->ID);

								$course_subtitle = get_field('course_subtitle', $course->ID);
                                $sold_out = get_field('sold_out_course', $course->ID);
                                
                                $end_early_booking = ( $date = get_post_meta( $post->ID, '_sale_price_dates_to', true ) ) ? date_i18n( 'Ymd', $date ) : '';
                                $start_month = substr($start_date, 4, -2);
                                $start_day = substr($start_date, 6, 8);
                                $start_year = substr($start_date, 0, 4);
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
                                    
                                    
								?> 

								<article class="course--box teachers-single" id="post-<?php the_ID(); ?>">

									<a href="<?php the_permalink($course->ID); ?>" class="course--box__link" title="Scopri di più su <?php echo get_the_title($course->ID); ?>" style="background-image:url('<?php echo $thumb_url;?>');">
										<?php if( $sold_out ): ?>
                                            <span class="ehi-label sold-out">SOLD OUT</span>
                                        <?php elseif( $current_date >= $end_booking): ?>
                                            <span class="ehi-label closed-course">ISCRIZIONI CHIUSE</span>

                                        <?php elseif($end_early_booking):?>
                                            <?php if( $current_date <= $end_early_booking) {
                                                echo '<span class="ehi-label early-booking">EARLY BOOKING</span>';
                                            }?> 

                                        <?php elseif($current_date < $end_booking):?>

                                                <?php $date1 = new DateTime($current_date);
                                                $date2 = new DateTime($end_booking);
                                                $diff = $date2->diff($date1)->format("%a");
                                                $days = intval($diff);
                                                if($days <= 10) {
                                                    echo '<span class="ehi-label last-minute">HAI ANCORA '.$days.' GIORNI</span>';
                                                }
                                         ?>
                                        <?php else: ?>
                                        <div class="ehi-label hero--data-info">
                                            Dal <?php echo $start_day . ' ' . $first_month . ' ' . $start_year;  ?>
                                        </div>
                                        <?php endif; ?>
										<img src="<?php echo get_template_directory_uri(); ?>/assets/img/elements/default.png" class="course--box__img" alt="<?php the_title(); ?>" />
									</a>
									<div class="course--box__body">
										<header class="course--box__header">
											<div class="course--box__category">


												<?php
														$taxonomy = 'product_cat'; // Taxonomy slug.
														$terms = get_the_terms( $course->ID, $taxonomy );
														$children = '';
														if($terms) {
														foreach ( $terms as $term ) {
															$getslug = get_term( $term->parent, $taxonomy );
															$slug = $getslug->slug;
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
														$terms = get_the_terms( $course->ID, $taxonomy );
														$children = '';
														if($terms) {
														foreach ( $terms as $term ) {
															$getslug = get_term( $term->parent, $taxonomy );
															$slug = $getslug->slug;
															if( $slug == 'argomento' ) { // Parent ID (Livello)
																		$argomento = $term->name;
																		echo '<span class="last">'. $argomento. '</span>';
															}

														}
													}
													?>


												<?php if( get_field('online_course', $course->ID) ): ?>
													<span class="online-label">Online</span>
												<?php endif; ?>
												</div>
											<h3 class="course--box__title"><a href="<?php echo get_the_permalink($course->ID); ?>" class="course--box__title--link" title="Scopri di più su <?php echo get_the_title($course->ID); ?>"><?php echo get_the_title($course->ID); ?></a></h3>
											<?php if($course_subtitle): ?>
												<h2 class="course__hero--subtitle"><?php echo $course_subtitle; ?></h2>
											<?php endif; ?>
										</header>

									</div>
								</article>
							<?php endforeach; ?>
						</div>
					</section>
						<?php endif; ?>
					</section>









				</main><!-- #main -->



		</div><!-- #content -->

	</div><!-- #author-wrapper -->

<?php endwhile; // end of the loop. ?>


<?php get_footer();
