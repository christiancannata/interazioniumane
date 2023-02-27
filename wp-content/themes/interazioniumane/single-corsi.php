<?php
/**
 * The template for displaying all single posts
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


get_header('corsi');
$container = get_theme_mod( 'understrap_container_type' );

//***** Course informations ******//

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


?>


<?php while ( have_posts() ) : the_post(); ?>
			<div class="course__wrapper">
				<?php get_template_part( 'loop-templates/content', 'single-course' ); ?>
			</div>

			<?php $course_faqs = get_field('course_faqs');
				if( $course_faqs ): ?>
				<section class="faq course-faq">
					<h3 class="course-faq--title">Domande?</h3>
					<?php $i = 1; foreach( $course_faqs as $faq ): ?>


							<article id="post-<?php the_ID(); ?>" class="faq__item <?php echo 'faq-'.$i; ?>">

								<header class="faq__content">
									<h2 class="faq__title"><a href="<?php echo get_permalink( $faq->ID ); ?>" title="<?php echo get_the_title( $faq->ID ); ?>" class="faq__link"><span class="faq__icon icon-close"></span><?php echo get_the_title( $faq->ID ); ?></a></h2>
									<div class="faq__description"><?php the_content(); ?></div>
								</header>

							</article>

						<?php $i++; endforeach; ?>
						<?php wp_reset_postdata(); ?>
				</section>

			<?php endif; ?>


			<?php
			//Related books
			$taxonomy = 'product_cat'; // Taxonomy slug.
			$terms = get_the_terms( $post->ID, $taxonomy );
			$term_ids = wp_list_pluck($terms,'term_id');

			$second_query = new WP_Query( array(
				'post_type' => 'books',
				'tax_query' => array(
					array(
							'taxonomy' => 'product_cat',
							'field' => 'id',
							'terms' => $term_ids,
							'operator'=> 'IN'
					 )),
				'posts_per_page' => 3,
				'ignore_sticky_posts' => 1,
				'orderby' => 'rand',
				'post__not_in'=>array($post->ID)
			) );

		if($second_query->have_posts()) {
			echo '<section class="books-related">';
			echo '<div class="books-related--slider">';
			while ($second_query->have_posts() ) : $second_query->the_post(); ?>
					<div class="books-related--item">
							 <?php if (has_post_thumbnail()) { ?>
								<a href="<?php the_permalink() ?>" title="<?php the_title(); ?>"> <?php the_post_thumbnail( 'medium', array('alt' => get_the_title()) ); ?> </a>
								<?php } else { ?>
										 <a href="<?php the_permalink() ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>
								<?php } ?>
					 </div>
			<?php endwhile; wp_reset_query();
		echo '</div>';
		echo '</section>';
		} ?>

<?php endwhile; // end of the loop. ?>





<?php get_footer();
