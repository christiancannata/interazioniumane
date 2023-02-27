<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$container = get_theme_mod( 'understrap_container_type' );
?>


<div class="wrapper colorbg" id="wrapper-footer">

	<div class="main-container">

				<footer class="site-footer" id="colophon">


					<div class="small-container">
						<div class="footer-content">

							<div class="footer-content__brand">
								<div class="footer-claim">
						        <div class="footer--logo">
											<?php get_template_part( 'global-templates/logo-iu' ); ?>
										</div>
						    </div>
							</div>
							<div class="footer-content__menu">
								<div class="footer-menu">
									<h6 class="footer-menu--title">Formazione <span class="icon-arrow-down footer-menu--title__icon only-mobile"></span></h6>
									<?php
										wp_nav_menu( array(
												'theme_location' => 'footer_menu_one',
												'container_class' => 'footer-menu--list' ) );
										?>
								</div>
								<div class="footer-menu">
									<h6 class="footer-menu--title">Learning Center <span class="icon-arrow-down footer-menu--title__icon only-mobile"></span></h6>
									<?php
										wp_nav_menu( array(
												'theme_location' => 'footer_menu_two',
												'container_class' => 'footer-menu--list' ) );
										?>
								</div>
								<div class="footer-menu">
									<h6 class="footer-menu--title">IESCUM <span class="icon-arrow-down footer-menu--title__icon only-mobile"></span></h6>
									<?php
										wp_nav_menu( array(
												'theme_location' => 'footer_menu_three',
												'container_class' => 'footer-menu--list' ) );
										?>
								</div>
							</div>
						</div>
					</div>

					<div class="footer--divisions">
						<div class="small-container">
							<div class="footer--divisions__row">
								<div class="footer--divisions__item">
									<a href="http://www.iescum.org/" class="footer--divisions__link" target="_blank" title="IESCUM">
										<img src="<?php echo get_template_directory_uri(); ?>/assets/img/footer/IESCUM.png" class="footer--divisions__image" />
									</a>
								</div>
								<div class="footer--divisions__item">
									<a href="http://www.abaxitalia.it/" class="footer--divisions__link" target="_blank" title="ABAxItalia">
										<img src="<?php echo get_template_directory_uri(); ?>/assets/img/footer/ABAxItalia.png" class="footer--divisions__image" />
									</a>
								</div>
								<div class="footer--divisions__item">
									<a href="http://www.masteraba.it/" class="footer--divisions__link" target="_blank" title="Master ABA">
										<img src="<?php echo get_template_directory_uri(); ?>/assets/img/footer/MasterABAit.png" class="footer--divisions__image" />
									</a>
								</div>
								<div class="footer--divisions__item">
									<a href="https://centrointerazioniumane.it/" class="footer--divisions__link" target="_blank" title="Centro Interazioni Umane">
										<img src="<?php echo get_template_directory_uri(); ?>/assets/img/footer/CentroInterazioniUmane.png" class="footer--divisions__image" />
									</a>
								</div>
								<div class="footer--divisions__item">
									<a href="http://www.nudgeitalia.it/" class="footer--divisions__link" target="_blank" title="Nudge">
										<img src="<?php echo get_template_directory_uri(); ?>/assets/img/footer/Nudge.png" class="footer--divisions__image" />
									</a>
								</div>
							</div>
							<div class="footer--divisions__row">
								<div class="footer--divisions__item">
									<a href="http://www.interazioniumane.it/" class="footer--divisions__link" target="_blank" title="Iterazioni Umane">
										<img src="<?php echo get_template_directory_uri(); ?>/assets/img/footer/InterazioniUmane.png" class="footer--divisions__image" />
									</a>
								</div>
								<div class="footer--divisions__item">
									<a href="http://www.abautismo.it/" class="footer--divisions__link" target="_blank" title="ABAutismo">
										<img src="<?php echo get_template_directory_uri(); ?>/assets/img/footer/Abautismo.png" class="footer--divisions__image" />
									</a> 
								</div>
								<div class="footer--divisions__item">
									<a href="https://abetterplace.it/" class="footer--divisions__link" target="_blank" title="A Better Place">
										<img src="<?php echo get_template_directory_uri(); ?>/assets/img/footer/aBetterPlace.png" class="footer--divisions__image" />
									</a>
								</div>
								<div class="footer--divisions__item">
									<a href="http://cescamilano.it/" class="footer--divisions__link" target="_blank" title="Cesca">
										<img src="<?php echo get_template_directory_uri(); ?>/assets/img/footer/Cesca.png" class="footer--divisions__image" />
									</a>
								</div>
								<div class="footer--divisions__item">
									<a href="http://www.mipia.org/doceboCms/" class="footer--divisions__link" target="_blank" title="MIPIA">
										<img src="<?php echo get_template_directory_uri(); ?>/assets/img/footer/Mipia.png" class="footer--divisions__image" />
									</a>
								</div>

							</div>
						</div>
					</div>

					<div class="site-info">
							<p>IESCUM - A non profit organization è iscritta al nr. 183 AS del Registro Provinciale delle Associazioni di Promozione Sociale</p>
					</div><!-- .site-info -->

				</footer><!-- #colophon -->





	</div><!-- container end -->

</div><!-- wrapper end -->

</div><!-- #page we need this extra closing tag here -->



<?php wp_footer(); ?>

<script>

</script>

</body>

</html>
