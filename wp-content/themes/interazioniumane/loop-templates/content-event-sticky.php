<?php
/**
 * Post rendering content according to caller of get_template_part
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$label = get_field('card_label');
$expired = get_field('card_date');

?>

<article class="cards-list__item sticky" id="post-<?php the_ID(); ?>">
	<div class="cards-list__image" <?php if ( has_post_thumbnail() ): ?>style="background-image: url(<?php the_post_thumbnail_url('large'); ?>)"<?php endif; ?>>
		<div class="cards-list__text">
			<header class="cards-list__header">
				<div class="category-list">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>/event" title="Scopri tutti gli eventi" class="category-list__label">Eventi</a>
				</div>

				<h2 class="cards-list__title"><a href="<?php the_permalink(); ?>" title="Scopri di più su <?php the_title(); ?>" ><?php the_title(); ?></a></h2>
				<?php if($label): ?>
				<h3 class="cards-list__company"><?php echo $label; ?></h3>
				<?php endif; ?>
			</header><!-- .entry-header -->

			<footer class="cards-list__footer">
				<?php if($expired): ?>
				<p class="cards-list__date"><?php echo $expired; ?></p>
				<?php endif; ?>
				<a href="<?php the_permalink(); ?>" class="dstr-button dstr-button-white dstr-button-small" title="Partecipa a <?php the_title(); ?>">Partecipa</a>
			</footer>
		</div>

		<?php if ( has_post_thumbnail() ): ?>
			<div class="cards-list__overlay"></div>
		<?php endif; ?>
	</div>
</article><!-- #post-## -->
