<?php
/**
* Template Name: Magazine
*/

/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
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
?>

<div class="wrapper" id="index-wrapper">

	<?php //Loop opportunità
			$args = array(
				//'post_type' => array( 'post', 'edu', 'work', 'event' ),
				'post_type' => 'post',
				'posts_per_page' => 5,
				'featured'       => true
			);
			$query = new WP_Query( $args );

			if ( $query->have_posts() ) : ?>

		<div class="sticky-post">

			<div class="sticky-post__slider">
				<?php while ( $query->have_posts() ) : $query->the_post(); ?>
					<?php get_template_part( 'loop-templates/content-sticky', get_post_format() ); ?>
				<?php endwhile; ?>
			</div>

		</div>


	<?php endif; wp_reset_postdata(); ?>


	<div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">

		<div class="row">


			<main class="site-main magazine" id="main">


				<?php //Loop blog
						$args = array(
							'post_type' => 'post',
							'posts_per_page' => -1,
							'orderby' => 'publish_date',
    					'order' => 'DESC'
						);
						$query = new WP_Query( $args );

						if ( $query->have_posts() ) : ?>

					<div class="magazine--list">
						<div class="grid-sizer"></div>

							<?php while ( $query->have_posts() ) : $query->the_post(); ?>
								<?php get_template_part( 'loop-templates/content-magazine', get_post_format() ); ?>
							<?php endwhile; ?>

					</div>

					<?php
						global $wp_query;

						if (  $wp_query->max_num_pages > 1 )
							echo '<div class="load-more misha_loadmore"><span class="button button-primary">Ne voglio ancora</span></div>'; // you can use <a> as well
						?>

				<?php endif; wp_reset_postdata(); ?>



			</main><!-- #main -->

			<!-- The pagination component -->
			<?php understrap_pagination(); ?>

		</div><!-- .row -->

	</div><!-- #content -->

</div><!-- #index-wrapper -->

<?php get_footer();
