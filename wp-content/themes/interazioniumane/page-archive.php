<?php

/**
* Template Name: Archivio
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

get_header();

$container = get_theme_mod( 'understrap_container_type' );

?>

<div class="wrapper" id="page-wrapper">




			<main class="site-main" id="main">

				<header class="page-header" data-aos="fade-down" data-aos-duration="1000" data-aos-delay="0">
					<h1 class="page-title">Archivio</h1>
				</header><!-- .page-header -->

				<section class="archive-flex">

					<?php
					if ( get_query_var('paged') ) {
					    $paged = get_query_var('paged');
					} elseif ( get_query_var('page') ) { // 'page' is used instead of 'paged' on Static Front Page
					    $paged = get_query_var('page');
					} else {
					    $paged = 1;
					}
					$today = date('Ymd');
					$yesterday = date('Ymd', strtotime('+1 days'));
					$args = array(
					  'post_type' => 'product',
					  'posts_per_page' => get_option('posts_per_page'),
						'paged' => $paged,
					  'meta_query' => array(
					    array(
					      'key' => 'end_booking',
					      'value' => $yesterday,
					      'compare' => '<'
					    )
					  )
					);

					$old_events = new WP_Query($args);
					$post__not_in = array();

					if ($old_events->have_posts()) {
					  $post__not_in = wp_list_pluck($old_events->posts, 'ID');

						while ( $old_events->have_posts() ) : $old_events->the_post();
							wc_get_template_part( 'content', 'product' );
						endwhile; ?>

						<?php if ($old_events->max_num_pages > 1) : // custom pagination  ?>
        <?php
        $orig_query = $wp_query; // fix for pagination to work
        $wp_query = $old_events;
        ?>
				</section>



				<?php understrap_pagination(); ?>
        <?php
        $wp_query = $orig_query; // fix for pagination to work
        ?>
    	<?php endif; ?>

					<?php }
					wp_reset_postdata();
				?>




			</main><!-- #main -->



</div><!-- #page-wrapper -->

<?php get_footer();
