<?php
/**
* Template Name: Iscrizione
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

//acf_form_head();

get_header('no-session');

$container = get_theme_mod( 'understrap_container_type' );

?>


<div class="wrapper" id="page-wrapper">

	<div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">

		<div class="row">


			<main class="site-main" id="main">

				<?php while ( have_posts() ) : the_post(); ?>


					<?php $field = get_field_object('user-invoice-address', 'user_1'); ?><br><br>
					<?php echo $field['ID']; ?><br/>
					<?php echo $field['value']; ?><br/>
<br>
<br>
<br>
<br>
<br>

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
							<?php //Form di registrazione - CAMBIARE ID
	 					 echo do_shortcode('[wpforms id="742" title="false" description="false"]'); ?>
						</div>
					</div>



				<?php endwhile; // end of the loop. ?>

			</main><!-- #main -->


		</div><!-- .row -->

	</div><!-- #content -->

</div><!-- #page-wrapper -->

<?php get_footer();
