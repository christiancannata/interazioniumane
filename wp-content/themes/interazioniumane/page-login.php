<?php
/**
* Template Name: Accedi
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

session_start();

//Redirect se l'utente è loggato
if (is_user_logged_in() && isset($_SESSION['from-course']) ) {
	wp_redirect( site_url( '/iscrizione' ) );
exit;
}

get_header('no-session');

$container = get_theme_mod( 'understrap_container_type' );

?>


<div class="wrapper" id="page-wrapper">

	<div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">

		<div class="row">


			<main class="site-main" id="main">

				<?php while ( have_posts() ) : the_post(); ?>


					<div class="login-flex">
						<?php if(isset($_SESSION['from-course'])): ?>
						<div class="course-cart">
							<?php if(isset($_SESSION['thumb'])): ?>
								<img class="course-cart--img" src="<?php echo $_SESSION['thumb']; ?>" alt="<?php echo $_SESSION['title']; ?>" />
							<?php endif; ?>
							<div class="course-cart--categories">
								<?php if(isset($_SESSION['category'])): ?>
									<span class="course-cart--categories__label"><?php echo $_SESSION['category']; ?></span>
								<?php endif; ?>
								<?php if(isset($_SESSION['online'])): ?>
									<span class="course-cart--categories__label online"><?php echo $_SESSION['online']; ?></span>
								<?php endif; ?>
								<?php if(isset($_SESSION['type'])): ?>
									<span class="course-cart--categories__label category"><?php echo $_SESSION['type']; ?></span>
								<?php endif; ?>
							</div>
							<?php if(isset($_SESSION['title'])): ?>
								<h3 class="course-cart--title"><?php echo $_SESSION['title']; ?></h3>
							<?php endif; ?>
							<?php if(isset($_SESSION['price'])): ?>
								<div class="course-cart--price">
									<span class="total--label">Totale</span>
									<span class="total--price"><span class="symbol">€</span><span class="total"><?php echo $_SESSION['price']; ?></span></span>
								</div>
							<?php endif; ?>
						</div>
						<?php endif; ?>

					<div class="beautiful-forms">

					<div class="check-register-form">
						<h2 class="beautiful-forms--title">Registrati</h2>
						<?php if(isset($_SESSION['title'])): ?>
						<p class="beautiful-forms--subtitle">Per iscriverti a questo corso, devi creare un account :)</p>
						<p class="beautiful-forms--message">Sei già dei nostri? <a href="#" class="show-login-form" title="Accedi">Accedi</a>.</p>
						<?php else: ?>
						<p class="beautiful-forms--subtitle">Crea un account, ci vuole meno di un minuto :)</p>
						<p class="beautiful-forms--message">Sei già dei nostri? <a href="#" class="show-login-form" title="Accedi">Accedi</a>.</p>
						<?php endif; ?>
						<?php //Form di registrazione - CAMBIARE ID
						echo do_shortcode('[wpforms id="696" title="false" description="false"]'); ?>
					</div>

					<div class="check-login-form">
						<h2 class="beautiful-forms--title">Accedi</h2>
						<?php if($_SESSION['title']): ?>
						<p class="beautiful-forms--subtitle">Velocizza la tua domanda di iscrizione!</p>
						<p class="beautiful-forms--message">Sei nuovo qui? <a href="#" class="show-register-form" title="Accedi">Registrati</a>.</p>
						<?php else: ?>
						<p class="beautiful-forms--subtitle">Controlla cosa succede sul tuo profilo.</p>
						<p class="beautiful-forms--message">Sei nuovo qui? <a href="#" class="show-register-form" title="Accedi">Registrati</a>.</p>
						<?php endif; ?>
						<?php //Form di login - CAMBIARE ID
						echo do_shortcode('[wpforms id="727" title="false" description="false"]'); ?>
					</div>
				</div>
				</div>

				<?php endwhile; // end of the loop. ?>

			</main><!-- #main -->


		</div><!-- .row -->

	</div><!-- #content -->

</div><!-- #page-wrapper -->

<?php get_footer();
