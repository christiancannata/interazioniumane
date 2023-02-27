<?php

/**
* Template Name: Chi siamo
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
					<h1 class="page-title">Chi siamo</h1>
				</header><!-- .page-header -->


				<div class="dstr-story" data-aos="fade-up" data-aos-duration="600" data-aos-delay="100">
					<div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">
						<div class="dstr-story__row">
							<div class="dstr-story__text">
								<h4><?php echo the_field('about_section_title_1'); ?></h4>
								<p><?php echo the_field('about_section_description_1'); ?></p>
							</div>
							<div class="dstr-story__img">
								<img src="<?php echo the_field('about_section_image_1'); ?>" class="img-about" alt="<?php echo the_field('about_section_title_1'); ?>" />
							</div>
						</div>
						<div class="dstr-story__row">
							<div class="dstr-story__img">
								<img src="<?php echo the_field('about_section_image_2'); ?>" class="img-about" alt="<?php echo the_field('about_section_title2'); ?>" />
							</div>
							<div class="dstr-story__text">
								<h4><?php echo the_field('about_section_title2'); ?></h4>
								<p><?php echo the_field('about_section_description_2'); ?></p>
						</div>
					</div>
				</div>


				<div class="to-creatives">
				<div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">
					<div class="row">

				<div id="creatives" class="dstr-section dstr-creatives">
					<h5 class="dstr-section__minititle"><?php echo the_field('about_professional_subtitle'); ?></h5>
					<h2 class="dstr-section__title"><?php echo the_field('about_professional_title'); ?></h2>
					<div class="to-creatives__top">
						<div class="to-creatives__descr">
							<p>
								<?php echo the_field('about_professional_description'); ?>
							</p>
						</div>
					</div>
					<div class="ffix"></div>
					<div class="to-creatives__bottom">
						<div class="to-creatives__image">
							<img src="<?php echo the_field('about_professional_image'); ?>" class="img-about" alt="<?php echo the_field('about_professional_title'); ?>" />
						</div>

						<div class="dstr-reviews">
							<div class="dstr-reviews--stars">
								<span class="icon-star"></span>
								<span class="icon-star"></span>
								<span class="icon-star"></span>
								<span class="icon-star"></span>
								<span class="icon-star"></span>
							</div>

								<?php	if( have_rows('about_reviews_list') ):

									echo '<div class="dstr-reviews--slider">';

											while( have_rows('about_reviews_list') ) : the_row();
													$about_reviews_list_name = get_sub_field('about_reviews_list_name');
													$about_reviews_list_quote = get_sub_field('about_reviews_list_quote');?>

										<div class="dstr-reviews--item">
											<p class="dstr-reviews--quote"><?php echo $about_reviews_list_quote; ?></p>
											<p class="dstr-reviews--name"><?php echo $about_reviews_list_name; ?></p>
										</div>

								<?php endwhile;
								echo '</div>';
							endif;?>

						</div>
					</div>
				</div>

			</div>
		</div>
		</div>

		<div class="dstr-guerrilla">
			<div class="medium-container" tabindex="-1">
				<div class="row">
					<h2 class="dstr-section__title">Al tuo fianco</h2>
					<div class="dstr-guerrilla__list">

						<?php	if( have_rows('about_values_list') ):
									while( have_rows('about_values_list') ) : the_row();
										$about_values_list_title = get_sub_field('about_values_list_title');
										$about_values_list_text = get_sub_field('about_values_list_text');
									?>

						<div class="dstr-guerrilla__item">
							<span class="asterix colortxt">*</span>
							<h4><?php echo $about_values_list_title; ?></h4>
							<p><?php echo $about_values_list_text; ?></p>
						</div>

					<?php endwhile;
				endif;?>


					</div>
				</div>
			</div>
		</div>

				<div id="companies" class="dstr-section dstr-companies to-companies">
					<div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">
						<div class="row">
							<div class="dstr-companies--content">
								<h5 class="dstr-section__minititle"><?php echo the_field('about_student_subtitle'); ?></h5>
							<h2 class="dstr-section__title"><?php echo the_field('about_student_title'); ?></h2>
							<div class="to-companies__text">
								<div class="to-companies__intro">
									<p><?php echo the_field('about_student_description'); ?></p>
								</div>
								<?php	if( have_rows('about_student_list') ):

									echo '<div class="to-companies__list"><ul class="colortxt"><li class="first">*</li>';

											while( have_rows('about_student_list') ) : the_row();
												$about_student_list_profession = get_sub_field('about_student_list_profession');
											?>

										<li><?php echo $about_student_list_profession; ?></li>

									<?php endwhile;
									echo '</ul></div>';
								endif;?>

								</div>
							</div>

							</div>
						</div>
					</div>
				</div>




			</main><!-- #main -->



</div><!-- #page-wrapper -->

<?php get_footer();
