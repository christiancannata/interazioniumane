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
						<h1 class="page-title">Lavoro</h1>
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
										<a href="<?php the_permalink(); ?>" title="Scopri di più" class="arrow-link">Scopri di più <span class="icon-arrow-right"></span></a>
									</div>
									<div class="career__description">
										<span><?php echo $seniority; ?></span>
										<span><?php echo $location; ?></span>
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
