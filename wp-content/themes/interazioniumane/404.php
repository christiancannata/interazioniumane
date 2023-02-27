<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();

$container = get_theme_mod( 'understrap_container_type' );
?>

<div class="wrapper" id="error-404-wrapper">

	<div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">

		<div class="row">

			<div class="col-md-12 content-area" id="primary">

				<main class="site-main" id="main">

					<section class="error-404 not-found">

						<div class="empty-states">
							<div class="empty-states--image big">
								<img src="<?php echo get_template_directory_uri(); ?>/assets/img/auth/empty-page.jpg" class="empty-states--image__img" alt="Nessun corso avviato" />
							</div>
							<div class="empty-states--text">
								<h3 class="empty-states--title">Oooops, c'è un buco qui.</h3>
								<p class="empty-states--subtitle">La pagina che cerchi non esiste (o è stata rimossa).<br/>Perché non sfogli il catalogo?</p>
								<a href="<?php echo esc_url( home_url( '/' ) ); ?>formazione" class="dstr-button dstr-button-primary dstr-button-small empty-states--button" title="Sfoglia il catalogo" class="empty-states--subtitle">Sfoglia il catalogo</a>
							</div>
						</div>
					</section>

				</main><!-- #main -->

			</div><!-- #primary -->

		</div><!-- .row -->

	</div><!-- #content -->

</div><!-- #error-404-wrapper -->

<?php get_footer();
