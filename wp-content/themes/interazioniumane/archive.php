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

//ID Categoria
$category = get_queried_object();
$categoryID = $category->term_id;
$categorySlug = $category->slug;
$categoryName = $category->name;
$parent = $category->parent;
$parent_name = get_category($parent);
$parent_slug = $parent_name->slug;
?>

<div class="wrapper" id="archive-wrapper">

	<div class="archive-container" id="content" tabindex="-1">

			<main class="archive-main" id="main">
				<header class="page-header">
					<?php
					the_archive_title( '<h1 class="page-title">', '</h1>' );
					the_archive_description( '<div class="taxonomy-description">', '</div>' );
					?>
				</header><!-- .page-header -->


					<?php //Loop opportunità
							$args = array(
								//'post_type' => array( 'post', 'edu', 'work', 'event' ),
								'post_type' => 'corsi',
								'cat' => $categoryID
							);
							$query = new WP_Query( $args );

							if ( $query->have_posts() ) : ?>

						<div class="cards-list section-works">

							<h2 class="cards-list__megatitle">Opportunità <?php if($parent_slug == "distretti") { echo 'su'; } else { echo 'di'; } ?> <?php  echo $categoryName; ?></h2>

							<div class="cards-list__container cards-list__slider">
								<?php while ( $query->have_posts() ) : $query->the_post(); ?>
									<?php get_template_part( 'loop-templates/content-card', get_post_format() ); ?>
								<?php endwhile; ?>
							</div>

						</div>

					<?php endif; wp_reset_postdata(); ?>


					<?php //Loop di tutti i custom post types
							$args = array(
								'post_type' => 'event',
								'cat' => $categoryID
							);
							$query = new WP_Query( $args );

							if ( $query->have_posts() ) : ?>

							<div class="cards-list section-events">

								<h2 class="cards-list__megatitle">Eventi <?php if($parent_slug == "distretti") { echo 'a'; } else { echo 'di'; } ?> <?php  echo $categoryName; ?></h2>

								<div class="cards-list__container cards-list__slider">
									<?php while ( $query->have_posts() ) : $query->the_post(); ?>
										<?php get_template_part( 'loop-templates/content-card', get_post_format() ); ?>
									<?php endwhile; ?>
								</div>

							</div>

					<?php endif; wp_reset_postdata(); ?>




				<?php //Loop di tutti i custom post types
						$args = array(
							'post_type' => 'edu',
							'cat' => $categoryID
						);
						$query = new WP_Query( $args );

						if ( $query->have_posts() ) : ?>

						<div class="cards-list section-edu">

							<h2 class="cards-list__megatitle">Educational</h2>

							<div class="cards-list__container cards-list__slider">
								<?php while ( $query->have_posts() ) : $query->the_post(); ?>
									<?php get_template_part( 'loop-templates/content-card', get_post_format() ); ?>
								<?php endwhile; ?>
							</div>

						</div>

				<?php endif; wp_reset_postdata(); ?>



			</main><!-- #main -->

			<div class="archive-sidebar">
				<?php //Opportunità in evidenza
						$args = array(
							'post_type' => 'work',
							'posts_per_page'      => 1,
							'cat' => $categoryID,
							'meta_key'		=> 'sticky_article',
							'value'				=> 'yes',
							//'order'				=> 'DESC'

						);
						$query = new WP_Query( $args );

						if ( $query->have_posts() ) : ?>

						<div class="cards-sticky sticky-work">

							<div class="cards-list__container">
								<?php while ( $query->have_posts() ) : $query->the_post(); ?>
									<?php get_template_part( 'loop-templates/content-work-sticky', get_post_format() ); ?>
								<?php endwhile; ?>
							</div>

						</div>

				<?php endif; wp_reset_postdata(); ?>

				<?php //evento in evidenza
						$args = array(
							'post_type' => 'event',
							'posts_per_page'      => 1,
							'cat' => $categoryID,
							'meta_key'		=> 'sticky_article',
							'value'				=> 'yes',
							//'order'				=> 'DESC'

						);
						$query = new WP_Query( $args );

						if ( $query->have_posts() ) : ?>

						<div class="cards-sticky sticky-work">

							<div class="cards-list__container">
								<?php while ( $query->have_posts() ) : $query->the_post(); ?>
									<?php get_template_part( 'loop-templates/content-event-sticky', get_post_format() ); ?>
								<?php endwhile; ?>
							</div>

						</div>

				<?php endif; wp_reset_postdata(); ?>
			</div>

			<!-- The pagination component -->
			<?php understrap_pagination(); ?>



	</div><!-- #content -->

	</div><!-- #archive-wrapper -->

<?php get_footer();
