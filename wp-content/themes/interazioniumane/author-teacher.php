<?php
/**
 * The template for displaying the author pages
 *
 * Learn more: https://codex.wordpress.org/Author_Templates
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();
$container = get_theme_mod( 'understrap_container_type' );

//Get User info
$current_user = wp_get_current_user();
global $current_user;

if ( isset( $_GET['author_name'] ) ) {
	$curauth = get_user_by( 'slug', $author_name );
} else {
	$curauth = get_userdata( intval( $author ) );
}
//Avatar
$this_user = 'user_' . $curauth->ID;
$user_avatar = get_field('user-avatar', $this_user);

//Corsi attivi
$featured_posts = get_field('course_teachers', $this_user);

$user_bio = get_field('teacher_role', $this_user);
?>

<div class="wrapper" id="author-wrapper">

	<div class="small-container" id="content" tabindex="-1">



			<main class="site-main" id="main">

				<header class="page-header author-header">

					<div class="user-profile__header">
						<div class="user-profile__header--flex">
 							<div class="user-profile__header--sx">
								<div class="user-profile--avatar">
									<?php if($user_avatar): ?>
										<img src="<?php echo $user_avatar; ?>" alt="<?php echo esc_html( $curauth->first_name ); ?> <?php echo esc_html( $curauth->last_name ); ?>" >
									<?php else: ?>
									<?php
									$string = $curauth->first_name;
									$firstCharacter = $string[0];
									echo '<span class="user-avatar">' .$firstCharacter . '</span>'; ?>
									<?php endif; ?>
								</div>
								<div class="user-profile--info">
									<h1 class="user-profile--name"><?php echo esc_html( $curauth->first_name ); ?> <?php echo esc_html( $curauth->last_name ); ?></h1>
									<?php if($user_bio): ?>
									<p class="user-profile--bio"><?php echo $user_bio; ?></p>
									<?php endif; ?>
								</div>
							</div>
							<?php if ( (is_user_logged_in()) && ($current_user->ID === $curauth->ID) ): ?>
							<div class="user-profile__header--dx">
								<a href="<?php echo esc_url( home_url( '/' ) ); ?>modifica-profilo" class="dstr-button dstr-button-secondary dstr-button-xsmall" title="Modifica il tuo profilo">Modifica</a>
							</div>
						<?php endif; ?>

						</div>
					</div>


				</header><!-- .page-header -->


				<?php//testttt

										$locations = get_field('course_teachers', $this_user);

										?>
										<?php if( $locations ): ?>
											<ul>
											<?php foreach( $locations as $location ): ?>
												<li>
													<a href="<?php echo get_permalink(); ?>">
														<?php echo get_the_title(); ?>
													</a>
												</li>
											<?php endforeach; ?>
											<?php wp_reset_postdata(); ?>
											</ul>
										<?php endif; ?>


				<section class="teacher-profile">

					<?php if( have_rows('teacher_curriculum') ): ?>

						<div class="teacher-profile__curriculum">
						<div class="xsmall-container">
							<?php while( have_rows('teacher_curriculum') ) : the_row();
							$teacher_curriculum_title = get_sub_field('teacher_curriculum_title');
							$teacher_curriculum_text = get_sub_field('teacher_curriculum_text');
							?>

							<div class="teacher-profile__curriculum--item">
									<h3><?php echo $teacher_curriculum_title; ?></h3>
									<div><?php echo $teacher_curriculum_text; ?></div>
							</div>


						<?php endwhile;?>
						</div>
						</div>
						<?php endif; ?>

				</section>


				ciaooo





				<?php

				if( $featured_posts ): ?>
				    <section class="user-profile__courses">
				    <?php $i = 1; foreach( $featured_posts as $post ):

				        // Setup this post for WP functions (variable must be named $post).
				        setup_postdata($post);
								$current_date = date('Ymd');
								$end_date = get_field('end_date');
								$end_year = substr($end_date, 0, 4);
								$end_month = substr($end_date, 4, -2);
								$end_day = substr($end_date, 6, 8);

								$last_month = '';
								if($end_month === '01') {
									$last_month = 'Gennaio';
								} else if($end_month === '02') {
									$last_month = 'Febbraio';
								} else if($end_month === '03') {
									$last_month = 'Marzo';
								} else if($end_month === '04') {
									$last_month = 'Aprile';
								} else if($end_month === '05') {
									$last_month = 'Maggio';
								} else if($end_month === '06') {
									$last_month = 'Giugno';
								} else if($end_month === '07') {
									$last_month = 'Luglio';
								} else if($end_month === '08') {
									$last_month = 'Agosto';
								} else if($end_month === '09') {
									$last_month = 'Settembre';
								} else if($end_month === '10') {
									$last_month = 'Ottobre';
								} else if($end_month === '11') {
									$last_month = 'Novembre';
								} else if($end_month === '12') {
									$last_month = 'Dicembre';
								}
								?>
				        <div class="user-profile__courses--item">
									<div class="user-profile__courses--sx">
									<div class="user-profile__courses--info">
										<?php if($current_date > $end_date): ?>
										<div class="user-profile__courses--status active">
											<span>
												<span>Fino a</span>
												<span><?php echo $last_month . ' ' . $end_year;?></span>
											</span>
										</div>
									<?php else: ?>
										<div class="user-profile__courses--status finished">
											<span>
												<span>Finito a</span>
												<span><?php echo $last_month . ' ' . $end_year;?></span>
											</span>
										</div>
									<?php endif; ?>
									</div>
									<div class="user-profile__courses--center">
									<div class="user-profile__courses--type">
											<?php
											$taxonomy = 'product_cat'; // Taxonomy slug.
											$terms = get_the_terms( $product->ID, $taxonomy );
											$children = '';
											if($terms) {
											foreach ( $terms as $term ) {
												$getslug = get_term( $term->parent, $taxonomy );
												$slug = $getslug->slug;
													if( $slug == 'livello' ) { // Parent ID (Livello)
															$children = $term->name; ?>

																<span><?php echo $children; ?></span>

													<?php }
											}
										}
												?>

											<?php if( get_field('online_course') ): ?>
												<span class="online-label">Online</span>
											<?php endif; ?>
											</div>
					            <a href="<?php the_permalink(); ?>" class="user-profile__courses--title" title="<?php the_title(); ?>"><?php the_title(); ?></a>
										</div>
										</div>
										<?php if( have_rows('file_list') ): ?>
											<div class="user-profile__courses--dx">
												<span class="show-downloads-items" data-download-item="down-<?php echo  $i; ?>">Materiali didattici <span class="icon-arrow-down"></span></span>
											</div>
										<?php endif; ?>
				        </div>

								<?php if( have_rows('file_list') ): ?>
									<div data-download-item="down-<?php echo  $i; ?>" class="user-profile__courses--downloads" style="display: none;">
										<div class="downloads">

											<div class="downloads--tabs">
												<?php $counter = 1;
												while( have_rows('file_list') ) : the_row();
													$parent_title = get_sub_field('downloads_megatitle'); ?>
														<span data-download="tab-<?php echo $counter; ?>" class="downloads--title get-tab <?php if($counter == 1){echo ' active';}?>"><?php echo $parent_title; ?></span>
												<?php $counter++;
											endwhile;?>
											</div>
										<?php endif; ?>

										<?php
										if( have_rows('file_list') ):
											$counter = 1;
											echo '<div class="downloads__all">';
										while( have_rows('file_list') ) : the_row(); ?>

											<div class="downloads__list" data-download="tab-<?php echo $counter; ?>" <?php if($counter == 1){echo 'style="display:block;"';}?>>
											<div class="downloads__list--flex">

												<?php if( have_rows('downloads_list') ):
								            while( have_rows('downloads_list') ) : the_row();
														$file_name = get_sub_field('file_name');
														$file_download = get_sub_field('file_download');
														$file_url = get_sub_field('file_url');

														?>
													<div class="downloads__list--item">
														<?php if($file_url): ?>
															<a href="<?php echo $file_url; ?>" class="downloads--link" target="_blank" title="Scarica il documento <?php echo $file_download['filename']; ?>">

																<?php if($file_name): ?>
																		<span class="downloads--label link"><?php echo $file_name; ?></span>
																	<?php else:?>
																			<span class="downloads--label link"><?php echo $file_url; ?></span>
																	<?php endif;?>

															</a>
														<?php else: ?>
															<a href="<?php echo $file_download['url']; ?>" class="downloads--link" target="_blank" title="Scarica il documento <?php echo $file_download['filename']; ?>">
																<span class="downloads--svg">
																	<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 66.34 72.91">
																		<path d="M59.5,54.2,47.61,66.09V59.85a5.67,5.67,0,0,1,5.66-5.66ZM4,63.26V9.66A5.67,5.67,0,0,1,9.66,4h47a5.67,5.67,0,0,1,5.66,5.66V50.19H53.27a9.68,9.68,0,0,0-9.66,9.66v9.06H9.66A5.67,5.67,0,0,1,4,63.26ZM66.34,9.66A9.68,9.68,0,0,0,56.68,0h-47A9.68,9.68,0,0,0,0,9.66v53.6a9.66,9.66,0,0,0,9.66,9.65h36A2,2,0,0,0,47,72.32L65.74,53.61a2,2,0,0,0,.6-1.42Zm-23.79,34V29.29a2,2,0,0,1,2-2h9a2,2,0,0,1,0,4h-7v3.17h4.64a2,2,0,0,1,0,4H46.53v5.17a2,2,0,0,1-4,0ZM35.68,40h0a1.6,1.6,0,0,1-1.61,1.6H30.68V31.29h3.39a1.61,1.61,0,0,1,1.14.47,1.59,1.59,0,0,1,.47,1.13Zm-1.6-12.75h-5.4a2,2,0,0,0-2,2V43.62a2,2,0,0,0,2,2h5.39A5.6,5.6,0,0,0,39.68,40V32.86a5.61,5.61,0,0,0-5.61-5.57ZM18.3,34.46H14.92V31.31H18.3a1.51,1.51,0,0,1,1.12.45,1.53,1.53,0,0,1,.45,1.13,1.57,1.57,0,0,1-1.57,1.57Zm0-7.15H12.92a2,2,0,0,0-2,2V43.6a2,2,0,0,0,4,0V38.46H18.3a5.6,5.6,0,0,0,5.57-5.57,5.48,5.48,0,0,0-1.61-4A5.55,5.55,0,0,0,18.3,27.31Z"/>
																	</svg>
																</span>
																<?php if($file_name): ?>
																	<span class="downloads--label"><?php echo $file_name; ?></span>
																<?php else:?>
																		<span class="downloads--label"><?php echo $file_download['filename']; ?></span>
																<?php endif;?>
															</a>
														<?php endif; ?>
													</div>
												<?php  endwhile;?>
												<?php endif; ?>
												</div>
												</div>


											<?php $counter++; endwhile;?>
											</div>
												</div>
											</div>

									<?php endif; ?>

				    <?php $i++; endforeach; ?>
					</section>
				    <?php
				    // Reset the global post object so that the rest of the page works correctly.
				    wp_reset_postdata(); ?>
				<?php endif; ?>



			</main><!-- #main -->



	</div><!-- #content -->

</div><!-- #author-wrapper -->

<?php get_footer();
