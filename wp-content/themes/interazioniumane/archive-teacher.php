<?php
/**
 * The template for displaying archive pages
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

	<div class="archive-container" id="content" tabindex="-1">

			<main class="archive-main" id="main">
				<header class="page-header" data-aos="fade-down" data-aos-duration="1000" data-aos-delay="0">
					<h1 class="page-title">Docenti</h1>
				</header><!-- .page-header -->

				<div class="ffix"></div>


				<?php if ( have_posts() ) : ?>

					<section class="archive-flex">

							<?php /* Start the Loop */ ?>
							<?php while ( have_posts() ) : the_post(); ?>

								<article class="archive-box teacher" data-aos="fade-up" data-aos-duration="600" data-aos-delay="50">
									<a href="<?php the_permalink(); ?>" class="archive-box--link teacher-profile-picture" title="<?php the_title(); ?>" style="background-image: url('<?php the_post_thumbnail_url('medium'); ?>')">

									</a>
									<header class="archive-box--header">
										<h3><a href="<?php the_permalink(); ?>" class="archive-box--title" title="<?php echo the_field('teacher_name'); ?> <?php echo the_field('teacher_lastname'); ?>"><?php echo the_field('teacher_name'); ?> <?php echo the_field('teacher_lastname'); ?></a></h3>
										<p><?php echo the_field('teacher_role'); ?></p>
									</header>
								</article>

							<?php endwhile; ?>

					</section>
						<?php endif; ?>

			</main><!-- #main -->




	</div><!-- #content -->

	</div><!-- #archive-wrapper -->

<?php get_footer();
