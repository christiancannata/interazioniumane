<?php
/**
* Template Name: Esplora
*/
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

session_start();



if (!is_user_logged_in()) {
	wp_redirect( site_url( '/iscrizione' ) );
exit;
}

get_header('no-session');
$container = get_theme_mod( 'understrap_container_type' );

if ( isset( $_GET['author_name'] ) ) {
	$curauth = get_user_by( 'slug', $author_name );
} else {
	$curauth = get_userdata( intval( $author ) );
}
//Get User info
$current_user = wp_get_current_user();
global $current_user;

$user_id = $current_user->ID;

//$user_interest = iconic_get_userdata( $user_id, 'interest-checkboxes' );
$user_interest = $current_user->user_select_interests;
$interest_categories =  explode(',', $user_interest);

?>


<div class="wrapper" id="page-wrapper">

	<div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">

		<div class="row">

			<main class="site-main" id="main">

				<header class="page-header" data-aos="fade-down" data-aos-duration="1000" data-aos-delay="0">
					<h1 class="page-title">Esplora</h1>
				</header><!-- .page-header -->

				<div class="ffix"></div>


				<?php //Loop corsi
						$args = array(
							'post_type' => 'product',
							'tax_query'      => [
									[
											'taxonomy' => 'product_cat',
											'field'    => 'slug',
											'terms'    => $interest_categories,
									]
							],
						);
						$query = new WP_Query( $args );

						if ( $query->have_posts() ) : ?>

						<div class="books-section">
							<h2 class="books--category">Formazione</h2>
							<div class="books-slider">
									<?php while ( $query->have_posts() ) : $query->the_post(); ?>
										<div class="book--item">
											<div class="book--image">
												<a href="<?php the_permalink(); ?>" class="book--link image" title="<?php the_title(); ?>">
													<img src="<?php the_post_thumbnail_url('medium'); ?>" alt="<?php the_title(); ?>" />
												</a>
											</div>
											<div class="book--header">
												<p class="book--info">
													<span class="book--author"><?php echo the_field('book_author'); ?></span>
													<?php if(get_field('book_price')): ?>
														<span class="book--price"><span class="symbol">€</span><?php echo the_field('book_price'); ?></span>
													<?php endif; ?>
												</p>
												<h3 class="book--title">
													<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>" class="book--link"><?php the_title(); ?></a>
												</h3>
											</div>
										</div>
									<?php endwhile; ?>
							</div>
						</div>

				<?php endif; wp_reset_postdata(); ?>

					<?php //Loop books
							$args = array(
								'post_type' => 'books',
								'tax_query'      => [
							      [
							          'taxonomy' => 'books_category',
							          'field'    => 'slug',
							          'terms'    => $interest_categories,
							      ]
							  ],
							);
							$query = new WP_Query( $args );

							if ( $query->have_posts() ) : ?>

							<div class="books-section">
								<h2 class="books--category">Libreria</h2>
								<div class="books-slider">
										<?php while ( $query->have_posts() ) : $query->the_post(); ?>
											<div class="book--item">
												<div class="book--image">
													<a href="<?php the_permalink(); ?>" class="book--link image" title="<?php the_title(); ?>">
														<img src="<?php the_post_thumbnail_url('thumbnail'); ?>" alt="<?php the_title(); ?>" />
													</a>
												</div>
												<div class="book--header">
													<p class="book--info">
														<span class="book--author"><?php echo the_field('book_author'); ?></span>
														<?php if(get_field('book_price')): ?>
															<span class="book--price"><span class="symbol">€</span><?php echo the_field('book_price'); ?></span>
														<?php endif; ?>
													</p>
													<h3 class="book--title">
														<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>" class="book--link"><?php the_title(); ?></a>
													</h3>
												</div>
											</div>
										<?php endwhile; ?>
								</div>
							</div>

					<?php endif; wp_reset_postdata(); ?>


	<?php //Loop works
			$args = array(
				'post_type' => 'lavoro',
				'tax_query'      => [
						[
								'taxonomy' => 'work_category',
								'field'    => 'slug',
								'terms'    => $interest_categories,
						]
				],
			);
			$query = new WP_Query( $args );

			if ( $query->have_posts() ) : ?>

			<div class="books-section">
				<h2 class="books--category">Lavoro</h2>
				<div class="books--science">
						<?php while ( $query->have_posts() ) : $query->the_post(); ?>
							<article id="post-<?php the_ID(); ?>" class="career__item science">

								<header class="career__content">
									<div class="career__flex">
										<h2 class="career__title"><a href="<?php echo the_field('book_science_link'); ?>" target="_blank" title="<?php the_title(); ?>" class="career__link"><?php the_title(); ?></a></h2>
										<a href="<?php echo the_field('book_science_link'); ?>" target="_blank" title="Leggi tutto" class="arrow-link">Leggi tutto <span class="icon-arrow-right"></span></a>
									</div>
								</header>

							</article>
						<?php endwhile; ?>
				</div>
			</div>

	<?php endif; wp_reset_postdata(); ?>


			</main><!-- #main -->


		</div><!-- .row -->

	</div><!-- #content -->

</div><!-- #page-wrapper -->

<?php get_footer();
