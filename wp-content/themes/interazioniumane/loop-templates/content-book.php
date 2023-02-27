<?php
/**
 * Single post partial template
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>

<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">

	<?php setup_postdata( $post );
		$thumb_id = get_post_thumbnail_id();
		$thumb_url_array = wp_get_attachment_image_src($thumb_id, 'full', true);
		$thumb_url = $thumb_url_array[0];
		$book_cta = get_field('book_cta');
		$book_link = get_field('book_link');
		$book_price = get_field('book_price');
		$book_author = get_field('book_author');
 ?>

 <div class="book-hero">
	 <div class="small-container">
		 <div class="book-hero__flex">
			 <div class="book-hero__image">
				 <img src="<?php echo $thumb_url; ?>" class="book-hero__image--img" alt="<?php the_title(); ?>" />
			 </div>
			 <div class="book-hero__info">
				 <?php if($book_author): ?>
					 <h2 class="book-hero__author"><?php echo $book_author; ?></h2>
				 <?php endif; ?>
				 <h1 class="book-hero__title"><?php the_title(); ?></h1>
				 <div class="book-hero__description"><?php the_excerpt(); ?></div>
				 <div class="course__hero--bottom">
					 <?php if($book_price): ?>
					<div class="course__hero--price">
						<span class="course--price"><span class="course--price--symbol">€</span><span class="course--price--value"><?php echo $book_price; ?></span></span>
					</div>
					<?php endif; ?>
					<div class="course__hero--button">
						<?php if($book_link): ?>
						<a href="<?php echo $book_link; ?>" target="_blank" class="dstr-button dstr-button-primary"><?php if($book_cta) { echo $book_cta; } else { echo 'Acquista<span class="only-desktop"> su Amazon</span>'; } ?></a>
						<?php endif; ?>
					</div>
				</div>
			 </div>
		 </div>
	 </div>
 </div>

 <div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">
	 <div class="row">
		 	<div class="article__entry-content typography-content">
			 	<div class="entry-content">
					<?php the_content(); ?>
				</div>
			</div>
		</div>
	</div>

	<footer class="entry-footer">

		<?php //understrap_entry_footer(); ?>

	</footer><!-- .entry-footer -->

</article><!-- #post-## -->
