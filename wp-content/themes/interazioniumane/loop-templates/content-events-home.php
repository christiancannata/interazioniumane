<?php
/**
 * Post rendering content according to caller of get_template_part.
 *
 * @package understrap
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<article class="course-box">

	<?php setup_postdata( $post );

		$thumb_id = get_post_thumbnail_id();
		$thumb_url_array = wp_get_attachment_image_src($thumb_id, 'full', true);
		$thumb_url = $thumb_url_array[0];

		$time_format = get_option( 'time_format', Tribe__Date_Utils::TIMEFORMAT );
		$start_ts = tribe_get_start_date( null, false, Tribe__Date_Utils::DBDATEFORMAT );
		$originalDate = str_replace('-', '', $start_ts);

		$end_datetime = tribe_get_end_date();
		$end_date = tribe_get_display_end_date( null, false );
		$end_time = tribe_get_end_date( null, false, $time_format );
		$end_ts = tribe_get_end_date( null, false, Tribe__Date_Utils::DBDATEFORMAT );

		$originalEnd = str_replace('-', '', $end_ts);

		$startTime = tribe_get_start_date( null, false, $time_format );

		if($startTime ==='00:00') {
			$startTime = '(tutto il giorno)';
		} else {
			$startTime = 'alle ' . $startTime;
		}

		$year = substr($originalDate, 0, 4);
		$month = substr($originalDate, 4, -2);
		$day = substr($originalDate, 6, 8);

		$yearEnd = substr($originalEnd, 0, 4);
		$monthEnd = substr($originalEnd, 4, -2);
		$dayEnd = substr($originalEnd, 6, 8);



		$current_time = date('Ymd');

		$DateField = strtotime($originalDate);
		$Date = date('Y-m-d',$DateField);
		$Tomorrow = date("Y-m-d", strtotime('tomorrow'));

		$FirstDay = date("Y-m-d", strtotime('monday this week'));
		$LastDay = date("Y-m-d", strtotime('monday next week'));

		$giorni = array('Domenica','Lunedì','Martedì','Mercoledì','Giovedì','Venerdì','Sabato');
		$weekday = $year.'-'.$month.'-'.$day;
		$weekdayEnd = $yearEnd.'-'.$monthEnd.'-'.$dayEnd;

		if($month === '01') {
			$month = 'Gennaio';
		} else if($month === '02') {
			$month = 'Febbraio';
		} else if($month === '03') {
			$month = 'Marzo';
		} else if($month === '04') {
			$month = 'Aprile';
		} else if($month === '05') {
			$month = 'Maggio';
		} else if($month === '06') {
			$month = 'Giugno';
		} else if($month === '07') {
			$month = 'Luglio';
		} else if($month === '08') {
			$month = 'Agosto';
		} else if($month === '09') {
			$month = 'Settembre';
		} else if($month === '10') {
			$month = 'Ottobre';
		} else if($month === '11') {
			$month = 'Novembre';
		} else if($month === '12') {
			$month = 'Dicembre';
		}

		if($monthEnd === '01') {
			$monthEnd = 'Gennaio';
		} else if($monthEnd === '02') {
			$monthEnd = 'Febbraio';
		} else if($monthEnd === '03') {
			$monthEnd = 'Marzo';
		} else if($monthEnd === '04') {
			$monthEnd = 'Aprile';
		} else if($monthEnd === '05') {
			$monthEnd = 'Maggio';
		} else if($monthEnd === '06') {
			$monthEnd = 'Giugno';
		} else if($monthEnd === '07') {
			$monthEnd = 'Luglio';
		} else if($monthEnd === '08') {
			$monthEnd = 'Agosto';
		} else if($monthEnd === '09') {
			$monthEnd = 'Settembre';
		} else if($monthEnd === '10') {
			$monthEnd = 'Ottobre';
		} else if($monthEnd === '11') {
			$monthEnd = 'Novembre';
		} else if($monthEnd === '12') {
			$monthEnd = 'Dicembre';
		}

		$dataCat = '';
		$LabelName = '';
		$eventDateAll = '';

		if(($current_time == $originalDate) && ($originalDate === $originalEnd)) {
			//solo oggi
			$dataCat = 'oggi';
			$LabelName = 'Oggi';
			$eventDateAll = '<span class="today">Oggi</span> <span class="time">' . $startTime .'</span>';
		} else if(($Date == $Tomorrow) && ($originalDate === $originalEnd)) {
			//solo domani
			$dataCat = 'domani';
			$LabelName = 'Domani';
			$eventDateAll = '<span class="today">Domani</span> <span class="time">' . $startTime .'</span>';
		} else if(($Date != $Tomorrow) && ($current_time != $originalDate) && ($current_time < $originalDate ) && ($originalDate === $originalEnd)) {
			//solo un giorno - futuro
			$dataCat = 'prossimamente';
			$LabelName = 'Fra poco';
			$eventDateAll = '<span class="weekday">' . $giorni[date('w',strtotime($weekday))] . '</span> <span class="day">'. $day .'</span> <span class="month">'. $month .'</span> <span class="time">' . $startTime .'</span>';
		} else if(($current_time > $originalDate) && ($originalDate === $originalEnd)) {
			//solo un giorno - passato
			$dataCat = 'passati';
			$LabelName = 'Te lo sei perso';
			$eventDateAll = '<span class="weekday">' . $giorni[date('w',strtotime($weekday))] . '</span> <span class="day">'. $day .'</span> <span class="month">'. $month .'</span> <span class="time">' . $startTime .'</span>';
		}
		else if(($current_time == $originalDate) && ($originalDate !== $originalEnd)) {
			//da oggi fino a
			$dataCat = 'oggi';
			$LabelName = 'Oggi';
			$eventDateAll = '<span class="today">Oggi</span> <span class="time">' . $startTime .'</span>';
		} else if(($Date == $Tomorrow) && ($originalDate !== $originalEnd)) {
			//da domani fino a
			$dataCat = 'domani';
			$LabelName = 'Domani';
			$eventDateAll = '<span class="today">Domani</span> <span class="time">' . $startTime .'</span>';
		} else if(($Date != $Tomorrow) && ($current_time != $originalDate) && ($current_time < $originalDate ) && ($originalDate !== $originalEnd)){
			//multiday - futuro
			$dataCat = 'prossimamente';
			$LabelName = 'Fra poco';
			$eventDateAll = 'Da <span class="weekday lowcase">' . $giorni[date('w',strtotime($weekday))] . '</span> <span class="day">'. $day .'</span> <span class="month">'. $month .'</span> <span class="time">' . $startTime .'</span>';
		} else if(($current_time > $originalDate) && ($originalDate !== $originalEnd)) {
			//multiday - passato
			$dataCat = 'passati';
			$LabelName = 'Te lo sei perso';
			$eventDateAll = 'Fino a <span class="weekday lowcase">' . $giorni[date('w',strtotime($weekdayEnd))] . '</span> <span class="day">' . $dayEnd .'</span> <span class="month">'. $month .'</span>';
		}


		//Nuovi
		$cost = tribe_get_formatted_cost();
		$venue = tribe_get_venue();  ?>



		<div class="course-box__event <?php echo $dataCat; ?>">

			<div class="course-box__image">
				<?php // Event Cost
				if ( ! empty( $cost ) ) : ?>
					<span class="course-box--price"><?php esc_html_e( $cost ); ?></span>
				<?php endif ?>
				<a href="<?php the_permalink(); ?>" class="course-box__image--link" title="<?php the_title(); ?>">
					<img src="<?php echo $thumb_url; ?>" title="<?php the_title(); ?>" class="course-box__image--thumb" />
				</a>
			</div>
			<header class="course-box__header">
				<h4 class="course-box--meta">
					<span class="course-box--meta--date"><?php echo $eventDateAll; ?></span>
				</h4>

				<h3 class="course-box--title">
					<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
						<?php the_title(); ?>
					</a>
				</h3>

				<div class="course-box--excerpt"><?php the_excerpt(); ?></div>
				<footer class="course-box__footer">
					<?php if( get_field('event_location') ): ?>
					<?php if ( ! empty( $venue ) ) : ?>
						<span class="the-post--footer__location">@ <?php echo $venue ?></span>
						<div class="ffix"></div>
					<?php endif ?>
					<?php endif; ?>
					<?php $tags = get_the_tags(); ?>
					<?php if($tags): ?>
		        <?php foreach ( $tags as $tag ) :
		            $tag_link = get_tag_link( $tag->term_id ); ?>
		            <a href='<?php echo $tag_link; ?>' title='<?php echo $tag->name; ?>' class='the-post--footer__tag <?php echo $tag->slug ?>'>#<?php echo $tag->name ?></a>
		        <?php endforeach; ?>
					<?php endif; ?>
				</footer>
			</header>
		</div>


</article>
