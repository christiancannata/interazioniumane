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
 ?>

 <div class="article-hero">
	 <div class="article-hero__content">
		<div class="article-hero__text">
			<h2 class="article-hero__title"><?php the_title(); ?></h2>
			<div class="article-hero__description"><?php the_excerpt(); ?></div>
		</div>
		<div class="article-hero__hover"></div>
		<div class="article-hero__image" style="background-image: url(<?php echo $thumb_url; ?>);"></div>
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
