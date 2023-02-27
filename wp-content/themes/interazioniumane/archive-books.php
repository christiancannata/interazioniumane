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



					<header class="page-header" data-aos="fade-down" data-aos-duration="1000" data-aos-delay="0">
						<h1 class="page-title">Libreria</h1>
					</header><!-- .page-header -->


					<section class="books" data-aos="fade-up" data-aos-duration="600" data-aos-delay="100">

						<div class="lp-container">


								<?php //Loop books
										$args = array(
											'post_type' => 'books',
											'tax_query'      => [
													[
															'taxonomy' => 'books_category',
															'field'    => 'slug',
															'terms'    => 'aba-autismo',
													]
											],
										);
										$query = new WP_Query( $args );

										if ( $query->have_posts() ) : ?>

									<div class="books-section">
										<h2 class="books--category">ABA & Autismo</h2>
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
															'terms'    => 'psicologi-psicoterapeuti',
													]
											],
										);
										$query = new WP_Query( $args );

										if ( $query->have_posts() ) : ?>

									<div class="books-section">
										<h2 class="books--category">Psicologi & Psicoterapeuti</h2>
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
															'terms'    => 'benessere-psicologico',
													]
											],
										);
										$query = new WP_Query( $args );

										if ( $query->have_posts() ) : ?>

										<div class="books-section">
											<h2 class="books--category">Benessere psicologico</h2>
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
															'terms'    => 'essere-genitori',
													]
											],
										);
										$query = new WP_Query( $args );

										if ( $query->have_posts() ) : ?>

									<div class="books-section">
										<h2 class="books--category">Essere genitori</h2>
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
															'terms'    => 'articoli-scientifici',
													]
											],
										);
										$query = new WP_Query( $args );

										if ( $query->have_posts() ) : ?>

									<div class="books-section">
										<h2 class="books--category">Articoli scientifici</h2>
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


					</div>
				</section>



			</main><!-- #main -->




		</div> <!-- .row -->

	</div><!-- #content -->

	</div><!-- #archive-wrapper -->


<?php get_footer(); ?>
