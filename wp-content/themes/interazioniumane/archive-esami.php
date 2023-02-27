<?php
/**
 * The template for displaying archive pages.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();

$container = get_theme_mod( 'understrap_container_type' );


?>

<div class="wrapper" id="archive-wrapper">

	<div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">

		<div class="row">


			<main class="site-main" id="main">

				<?php if ( have_posts() ) : ?>


					<header class="page-header" data-aos="fade-down" data-aos-duration="1000" data-aos-delay="0">
						<h1 class="page-title">Esami</h1>
					</header><!-- .page-header -->

					<section class="career" data-aos="fade-up" data-aos-duration="600" data-aos-delay="100">
						<div class="lp-container">

						<?php /* Start the Loop */ ?>
						<?php $i = 1; while ( have_posts() ) : the_post(); ?>

							<?php $seniority = get_field('work-ente');
										$location = get_field('work-location'); ?>

							<article id="post-<?php the_ID(); ?>" class="career__item <?php echo 'career-'.$i; ?>">

								<header class="career__content">
									<div class="career__flex">
										<h2 class="career__title"><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>" class="career__link"><?php the_title(); ?></a></h2>
										<a href="<?php the_permalink(); ?>" title="Iscriviti" class="arrow-link">Iscriviti <span class="icon-arrow-right"></span></a>
									</div>
									<div class="exam__list">

										<?php	if( have_rows('exams_info') ):

											echo '<div class="course-calendar dark">';
													while( have_rows('exams_info') ) : the_row();


													if( have_rows('exam_detail') ):
														while( have_rows('exam_detail') ): the_row();

															$exam_type = get_sub_field('exam_type');
															$exam_online = get_sub_field('exam_online');
															$exam_hour = get_sub_field('exam_hour');
															$exam_calendar_date = get_sub_field('exam_date');
															$exam_calendar_date_day = substr($exam_calendar_date,6, 8);
															$exam_calendar_date_month = substr($exam_calendar_date, 4, -2);

															$exam_calendar_month_full = '';
															if($exam_calendar_date_month === '01') {
																$exam_calendar_month_full = 'Gennaio';
															} else if($exam_calendar_date_month === '02') {
																$exam_calendar_month_full = 'Febbraio';
															} else if($exam_calendar_date_month === '03') {
																$exam_calendar_month_full = 'Marzo';
															} else if($exam_calendar_date_month === '04') {
																$exam_calendar_month_full = 'Aprile';
															} else if($exam_calendar_date_month=== '05') {
																$exam_calendar_month_full = 'Maggio';
															} else if($exam_calendar_date_month === '06') {
																$exam_calendar_month_full = 'Giugno';
															} else if($exam_calendar_date_month === '07') {
																$exam_calendar_month_full = 'Luglio';
															} else if($exam_calendar_date_month === '08') {
																$exam_calendar_month_full = 'Agosto';
															} else if($exam_calendar_date_month === '09') {
																$exam_calendar_month_full = 'Settembre';
															} else if($exam_calendar_date_month === '10') {
																$exam_calendar_month_full = 'Ottobre';
															} else if($exam_calendar_date_month === '11') {
																$exam_calendar_month_full = 'Novembre';
															} else if($exam_calendar_date_month === '12') {
																$exam_calendar_month_full = 'Dicembre';
															}

															echo '<div class="course-calendar__item">';
															if( $exam_online ){
																echo '<span class="course-calendar__online">Online</span>';
															}
															echo '<span class="big">' .$exam_calendar_date_day.'</span>';
															echo '<span class="small">' .$exam_calendar_month_full.'</span>';
															echo '<span class="small">' .$exam_hour.'</span>';
															echo '<span class="small type">' .$exam_type.'</span>';
															echo '</div>';

														endwhile;
													endif;

													endwhile;
													echo '</div>';
												endif;?>
									</div>
								</header>

							</article>

						<?php $i++; endwhile; ?>

					</div>
				</section>

				<?php else : ?>

					<?php get_template_part( 'loop-templates/content', 'none' ); ?>

				<?php endif; ?>

			</main><!-- #main -->




		</div> <!-- .row -->

	</div><!-- #content -->

	</div><!-- #archive-wrapper -->

<?php get_footer(); ?>
