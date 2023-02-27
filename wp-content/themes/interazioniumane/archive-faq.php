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


					<header class="page-header" data-aos="fade-up" data-aos-duration="600" data-aos-delay="50">
						<h1 class="page-title">Domande?</h1>
					</header><!-- .page-header -->

					<section class="faq" data-aos="fade-up" data-aos-duration="600" data-aos-delay="100">
						<div class="small-container">

						<?php /* Start the Loop */ ?>
						<?php $i = 1; while ( have_posts() ) : the_post(); ?>

							<?php if( get_field('hide_from_faqs') == 0 ):?>

							<article id="post-<?php the_ID(); ?>" class="faq__item <?php echo 'faq-'.$i; ?>">

								<header class="faq__content">
									<h2 class="faq__title"><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>" class="faq__link"><span class="faq__icon icon-close"></span><?php the_title(); ?></a></h2>
									<div class="faq__description"><?php the_content(); ?></div>
								</header>

							</article>
							<?php endif; ?>

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
