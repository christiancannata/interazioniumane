<?php
/* Template Name: Modifica profilo */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

//acf_form_head();
get_header();

$container = get_theme_mod( 'understrap_container_type' );
?>


<div class="wrapper" id="explore-wrapper">

	<div class="container-explore" id="content" tabindex="-1">


			<main class="site-main" id="main">

				<?php /* The loop */ ?>
				<?php while ( have_posts() ) : the_post(); ?>

					<h1><?php the_title(); ?></h1>

					<?php the_content(); ?>

				<?php endwhile; ?>

			</main><!-- #main -->



	</div><!-- #content -->

</div><!-- #index-wrapper -->

<?php get_footer();
