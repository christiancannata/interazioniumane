<?php
/**
 * Post rendering content according to caller of get_template_part
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>

<article class="grid-item magazine--post" id="post-<?php the_ID(); ?>">

	<a href="<?php echo the_permalink(); ?>" class="magazine--post__image" title="<?php echo the_title(); ?>">
		<?php echo get_the_post_thumbnail( $post->ID, 'large' ); ?>
	</a>

	<header class="magazine--post__header">

		<div class="magazine--post__meta">
			<ul>
				<?php $current_post_categories = get_the_category();
				foreach( $current_post_categories as $category) {
					 echo '<li><a href="'.esc_url( home_url( '/' ) ).$category->slug.'" title="Vai alla categoria '.$category->cat_name.'">'.$category->cat_name.'</a></li>';
				} ?>
			</ul>
		</div>
		<?php
		the_title(
			sprintf( '<h2 class="magazine--post__title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ),
			'</a></h2>'
		);
		?>





	</header><!-- .entry-header -->


	<div class="magazine--post__content">
		<?php the_excerpt(); ?>
	</div><!-- .entry-content -->

</article><!-- #post-## -->
