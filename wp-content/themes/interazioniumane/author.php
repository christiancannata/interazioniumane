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

if ( isset( $_GET['author_name'] ) ) {
	$curauth = get_user_by( 'slug', $author_name );
} else {
	$curauth = get_userdata( intval( $author ) );
}
//Avatar
$this_user = 'user_' . $curauth->ID;
$user_avatar = get_field('user-avatar', $this_user);

//Corsi attivi
//$featured_posts = get_field('user_courses', $this_user);

$user_bio = get_field('user-bio', $this_user);
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
									$string = $current_user->first_name;
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
							<div class="user-profile__header--dx">
								<a href="<?php echo esc_url( home_url( '/' ) ); ?>modifica-profilo" class="dstr-button dstr-button-secondary dstr-button-xsmall" title="Modifica il tuo profilo">Modifica profilo</a>
							</div>
						</div>
					</div>



				</header><!-- .page-header -->



				<?php

				$featured_posts = get_field('user_courses', $this_user);


				if( $featured_posts ): ?>
				    <section class="user-profile__courses">
				    <?php $i = 1; foreach( $featured_posts as $post ):

				        // Setup this post for WP functions (variable must be named $post).
				        //setup_postdata($post);
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
										<?php if($current_date < $end_date): ?>
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
											$terms = get_the_terms( $post, $taxonomy );
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
										<?php if( have_rows('file_list' )): ?>
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
										while( have_rows('file_list' ) ) : the_row(); ?>

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
															<?php //Capisco di che file si tratta
															$path = $file_download['url'];
															$ext = pathinfo($path, PATHINFO_EXTENSION);
															?>
															<a href="<?php echo $file_download['url']; ?>" class="downloads--link" target="_blank" title="Scarica il documento <?php echo $file_download['filename']; ?>">
																<span class="downloads--svg">
																	<span class="downloads--type"><?php echo $ext; ?></span>
																	<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 73.75 90.75">
																	  <path d="M40.56,0H9.38A9.38,9.38,0,0,0,0,9.38v25H0v47a9.38,9.38,0,0,0,9.38,9.37h55a9.38,9.38,0,0,0,9.37-9.37V29.84ZM42.5,10.13,65.94,31.25H45.62a3.11,3.11,0,0,1-3.12-3.13ZM66.59,83.59a3.17,3.17,0,0,1-2.22.91h-55a3.11,3.11,0,0,1-3.12-3.12v-47h0v-25A3.13,3.13,0,0,1,9.38,6.25H36.25V28.12a9.38,9.38,0,0,0,9.37,9.38H67.5V81.38A3.17,3.17,0,0,1,66.59,83.59Z"/>
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
