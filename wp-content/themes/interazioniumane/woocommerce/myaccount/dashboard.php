<?php
/**
 * My Account Dashboard
 *
 * Shows the first intro screen on the account dashboard.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/dashboard.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$allowed_html = array(
	'a' => array(
		'href' => array(),
	),
);

$this_user = 'user_' . $current_user->ID;
?>




<?php

$featured_posts = get_field('user_courses', $this_user);


if( $featured_posts ): ?>
		<section class="user-profile__courses">
		<?php $i = 1; foreach( $featured_posts as $post ):

				// Setup this post for WP functions (variable must be named $post).
				setup_postdata($post);
				$current_date = date('Ymd');
				$end_date = get_field('end_date', $post);
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

							<?php if( get_field('online_course', $post) ): ?>
								<span class="online-label">Online</span>
							<?php endif; ?>
							</div>
							<a href="<?php echo get_permalink($post); ?>" class="user-profile__courses--title" title="<?php echo get_the_title($post); ?>"><?php echo get_the_title($post); ?></a>
						</div>
						</div>
						<?php if( have_rows('file_list', $post ) || get_field('directory_box', $post )): ?>
							<div class="user-profile__courses--dx">
								<span class="show-downloads-items" data-download-item="down-<?php echo $i; ?>">Materiali didattici <span class="icon-arrow-down"></span></span>
							</div>
						<?php endif; ?>
				</div>


				<?php	if( have_rows('file_list', $post) || get_field('directory_box', $post ) ): ?>

				<div data-download-item="down-<?php echo  $i; ?>" class="user-profile__courses--downloads" style="display: none;">

					<?php if( have_rows('file_list', $post)): ?>
					<div class="downloads">
					<?php endif; ?>
							<div class="downloads--tabs">
								<?php $tabnr = 1; while( have_rows('file_list', $post) ) : the_row();

									$parent_title = get_sub_field('downloads_megatitle'); ?>
										<span data-download="tab-<?php echo $tabnr; ?>" class="downloads--title get-tab <?php if($tabnr == 1){echo ' active';}?>"><?php echo $parent_title; ?></span>
								<?php $tabnr++;
							endwhile;?>
							</div>

						<div class="downloads__all">
						<?php $filesnr = 1; while( have_rows('file_list', $post ) ) : the_row();
						?>

							<div class="downloads__list" data-download="tab-<?php echo $filesnr; ?>" <?php if($filesnr == 1){echo 'style="display:block;"';}?>>
							<div class="downloads__list--flex">

								<?php if( have_rows('downloads_list', $post) ):
										while( have_rows('downloads_list', $post) ) : the_row();
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
												<?php endif;?> <!-- $file_name  -->
											</a>
										<?php endif; ?> <!-- $file_url  -->
									</div>
								<?php endwhile; ?>
							<?php endif; ?><!-- $download_list  -->
								</div>
								</div>

							<?php $filesnr++; endwhile;?> <!-- $file_list  -->
							</div>
						<?php if( have_rows('file_list', $post)): ?>
							</div>
						<?php endif; ?>


						<?php if( get_field('directory_box', $post) ):
									$box = get_field('directory_box', $post);?>
									<div class="download--box">
										<?php echo do_shortcode('[letsbox dir='. $box .' mode="files" viewrole="administrator|author|contributor|editor|subscriber|guest" downloadrole="all" filelayout="list" showbreadcrumb="0" ]'); ?>
									</div>
						<?php endif; ?>

					</div><!-- finale -->


					<?php endif; ?>

		<?php $i++; endforeach; ?>




	</section>
		<?php

		// Reset the global post object so that the rest of the page works correctly.
		wp_reset_postdata(); ?>
	<?php else: ?>
		<div class="empty-states">
			<div class="empty-states--image">
				<img src="<?php echo get_template_directory_uri(); ?>/assets/img/auth/empty-courses.jpg" class="empty-states--image__img" alt="Nessun corso avviato" />
			</div>
			<div class="empty-states--text">
				<h3 class="empty-states--title">Troppo tempo libero?</h3>
				<p class="empty-states--subtitle">Consulta il nostro catalogo e trova la formazione che fa per te.</p>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>esplora" class="dstr-button dstr-button-primary dstr-button-small empty-states--button" title="Sfoglia il catalogo" class="empty-states--subtitle">Sfoglia il catalogo</a>
			</div>
		</div>
<?php endif; ?>
<?php
	/**
	 * My Account dashboard.
	 *
	 * @since 2.6.0
	 */
	do_action( 'woocommerce_account_dashboard' );

	/**
	 * Deprecated woocommerce_before_my_account action.
	 *
	 * @deprecated 2.6.0
	 */
	do_action( 'woocommerce_before_my_account' );

	/**
	 * Deprecated woocommerce_after_my_account action.
	 *
	 * @deprecated 2.6.0
	 */
	do_action( 'woocommerce_after_my_account' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
