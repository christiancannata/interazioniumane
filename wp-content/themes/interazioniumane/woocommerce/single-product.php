<?php
/**
 * The Template for displaying all single products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header( 'shop' ); ?>

	<?php
		/**
		 * woocommerce_before_main_content hook.
		 *
		 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
		 * @hooked woocommerce_breadcrumb - 20
		 */
		//do_action( 'woocommerce_before_main_content' );
	?>

		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>

			<?php wc_get_template_part( 'content', 'single-product' ); ?>

		<?php endwhile; // end of the loop. ?>


<section class="course-partner--section">
	<div class="course-partner--flex">
		<?php $course_founder = get_field('course_founder');
					if( $course_founder ): ?>
					<div class="course-partner">
						<h4 class="course-partner--title">Organizzato da</h4>
						<div class="course-partner--slider">
						<?php $i = 1; foreach( $course_founder as $partner ): ?>
							<div class="course-partner--item">
								<a href="<?php echo the_field('partner_link', $partner->ID); ?>" target="_blank" class="course-partner--link" title="<?php echo get_the_title( $partner->ID ); ?>">
									<img src="<?php echo the_field('partner_logo', $partner->ID); ?>" class="course-partner--img" alt="<?php echo get_the_title( $partner->ID ); ?>" />
								</a>
							</div>
							<?php $i++; endforeach; ?>
						</div>
							<?php wp_reset_postdata(); ?>
						</div>

				<?php endif; ?>

		<?php $course_partner = get_field('course_partner');
					if( $course_partner ): ?>
					<div class="course-partner">
						<h4 class="course-partner--title">Con il patrocinio di</h4>
						<div class="course-partner--slider">
						<?php $i = 1; foreach( $course_partner as $partner ): ?>
							<div class="course-partner--item">
								<a href="<?php echo the_field('partner_link', $partner->ID); ?>" target="_blank" class="course-partner--link" title="<?php echo get_the_title( $partner->ID ); ?>">
									<img src="<?php echo the_field('partner_logo', $partner->ID); ?>" class="course-partner--img" alt="<?php echo get_the_title( $partner->ID ); ?>" />
								</a>
							</div>
							<?php $i++; endforeach; ?>
						</div>
							<?php wp_reset_postdata(); ?>
					</div>

				<?php endif; ?>
			</div>
	</section>

			<?php global $post;
						$course_faqs = get_field('course_faqs');
						if( $course_faqs ): ?>
						<section class="faq course-faq">

							<?php
										$taxonomy = 'product_cat'; // Taxonomy slug.
										$terms = get_the_terms( $post->ID, $taxonomy );
										$children = '';
										if($terms) {
										foreach ( $terms as $term ) {
											$getslug = get_term( $term->parent, $taxonomy );
											$slug = $getslug->slug;
												if( $slug == 'livello' ) {// Parent ID (Livello)
														$children = $term->name; ?>
															<h3 class="course-faq--title">Domande su questo <span style="text-transform:lowercase;"><?php echo $children; ?></span>?</h3>
												<?php }
										}
									}
								?>
							<?php $i = 1; foreach( $course_faqs as $faq ) : setup_postdata( $faq );  ?>

									<article id="post-<?php the_ID(); ?>" class="faq__item <?php echo 'faq-'.$i; ?>">
										<header class="faq__content">
											<h2 class="faq__title"><a href="<?php echo get_permalink( $faq->ID ); ?>" title="<?php echo get_the_title( $faq->ID ); ?>" class="faq__link"><span class="faq__icon icon-close"></span><?php echo get_the_title( $faq->ID ); ?></a></h2>
											<div class="faq__description"><?php echo $faq->post_content; ?></div>
										</header>
									</article>

								<?php $i++; endforeach; ?>
								<?php wp_reset_postdata(); ?>


						</section>

					<?php endif; ?>


					<?php
					//Related books
					$taxonomy = 'product_cat'; // Taxonomy slug.
					$terms = get_the_terms( $post->ID, $taxonomy );
					$term_slug = wp_list_pluck($terms,'slug');

					$second_query = new WP_Query( array(
						'post_type' => 'books',
						'tax_query'      => [
								[
										'taxonomy' => 'category',
										'field'    => 'slug',
										'terms'    => $term_slug ,
								]
						],
						'posts_per_page' => 3,
						'ignore_sticky_posts' => 1,
						'orderby' => 'rand',
						'post__not_in'=>array($post->ID)
					) );

				if($second_query->have_posts()) {
					echo '<section class="books-related">';
					echo '<h3 class="course-faq--title">Libri su questo argomento</h3>';
					echo '<div class="books-related--content">';
					while ($second_query->have_posts() ) : $second_query->the_post(); ?>
					<div class="book--item">
						<div class="book--image">
							<a href="<?php the_permalink(); ?>" class="book--link image" title="<?php the_title(); ?>">
								<img src="<?php the_post_thumbnail_url('medium'); ?>" alt="<?php the_title(); ?>" />
							</a>
						</div>
						<div class="book--header">
							<p class="book--info">
								<span class="book--author"><?php echo the_field('book_author'); ?></span>
								<?php if(get_field('book_price')): ?>
									<span class="book--price"><span class="symbol">€</span><?php echo the_field('book_price'); ?></span>
								<?php endif; ?>
							</p>
							<h3 class="book--title">
								<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>" class="book--link"><?php the_title(); ?></a>
							</h3>
						</div>
					</div>
					<?php endwhile; wp_reset_query();
				echo '</div>';
				echo '</section>';
				} ?>

	<?php
		/**
		 * woocommerce_after_main_content hook.
		 *
		 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
		 */
		//do_action( 'woocommerce_after_main_content' );
	?>
<?php
get_footer( 'shop' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
