<?php
/**
 * Single post partial template.
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$seniority = get_field('work-ente');
$location = get_field('work-location');

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<header class="page-header career-page" data-aos="fade-down" data-aos-duration="1000" data-aos-delay="0">
		<h1 class="page-title"><?php the_title(); ?></h1>
	</header><!-- .page-header -->



		<div class="the-career__content" data-aos="fade-up" data-aos-duration="600" data-aos-delay="100">
			<div class="lp-container">
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
			</div>
		</div>

</article>
