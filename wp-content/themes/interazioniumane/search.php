<?php
/**
 * The template for displaying search results pages
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();

$container = get_theme_mod( 'understrap_container_type' );

?>

<div class="wrapper" id="search-wrapper">

	<div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">

		<div class="row">


			<main class="site-main" id="main">

				<header class="page-header" data-aos="fade-down" data-aos-duration="1000" data-aos-delay="0">
					<h1 class="page-title small"><?php
					printf(
						/* translators: %s: query term */
						esc_html__( 'Hai cercato %s', 'understrap' ),
						'<span>"' . get_search_query() . '"</span>'
					);
					?></h1>
				</header><!-- .page-header -->



				<?php if ( have_posts() ) : ?>

					<section class="archive-flex">



					<?php /* Start the Loop */ ?>
					<?php while ( have_posts() ) : the_post(); ?>

						<?php
						/**
						 * Run the loop for the search to output the results.
						 * If you want to overload this in a child theme then include a file
						 * called content-search.php and that will be used instead.
						 */
						get_template_part( 'loop-templates/content', 'search' );
						?>

					<?php endwhile; ?>

					</section>

				<?php else : ?>


					<section class="error-404 not-found" style="margin-top: -60px;">

						<div class="empty-states">
							<div class="empty-states--image big">
								<img src="<?php echo get_template_directory_uri(); ?>/assets/img/auth/empty-page.jpg" class="empty-states--image__img" alt="Nessun corso avviato" />
							</div>
							<div class="empty-states--text">
								<h3 class="empty-states--title">Oooops, non abbiamo trovato niente.</h3>
								<p class="empty-states--subtitle">La parola che cerchi non esiste.<br/>Perché non sfogli il catalogo?</p>
								<a href="<?php echo esc_url( home_url( '/' ) ); ?>formazione" class="dstr-button dstr-button-primary dstr-button-small empty-states--button" title="Sfoglia il catalogo" class="empty-states--subtitle">Sfoglia il catalogo</a>
							</div>
						</div>
					</section>

				<?php endif; ?>

			</main><!-- #main -->

			<!-- The pagination component -->
			<?php understrap_pagination(); ?>


		</div><!-- .row -->

	</div><!-- #content -->

</div><!-- #search-wrapper -->

<?php get_footer();
