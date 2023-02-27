<?php
/* Template Name: Homepage */

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
?>

<?php if ( !is_user_logged_in() ): ?>
<div class="old-users-info" style="display: none;">
	<div class="main-container">
		<div class="old-users-info--flex">
			<div class="old-users-info--sx">
				<div class="old-users-info--sx--flex">
					<div class="old-users-info--sx--flex--sx">
						<h5 class="old-users-info--title">Ci siamo rifatti il look!</h5>
						<p class="old-users-info--text">Se sei già un nostro studente, registrati e scopri la tua nuova bacheca virtuale.</p>
					</div>
					<div class="old-users-info--sx--flex--dx">
						<a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>" class="dstr-button dstr-button-mixed dstr-button-small">Registrati</a>
					</div>
				</div>
			</div>
			<div class="old-users-info--dx">
				<span class="icon-close close-info"></span>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>

<div class="wrapper" id="index-wrapper">


	<?php get_template_part( 'global-templates/hero' ); ?>

	<div class="main-container" id="content" tabindex="-1">

		<div class="row">


			<main class="site-main" id="main">

				<?php get_template_part( 'global-templates/home', 'sections' ); ?>

				<?php get_template_part( 'global-templates/home', 'categories' ); ?>

				<?php get_template_part( 'global-templates/home', 'intro' ); ?>

			</main><!-- #main -->


		</div><!-- .row -->

	</div><!-- #content -->

</div><!-- #index-wrapper -->

<?php get_footer();
