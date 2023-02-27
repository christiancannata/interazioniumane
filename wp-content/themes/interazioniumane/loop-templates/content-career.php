<?php
/**
 * Single post partial template.
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$seniority = get_field('work-ente');
$location = get_field('work-location');

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<header class="page-header career-page" data-aos="fade-down" data-aos-duration="1000" data-aos-delay="0">
		<h1 class="page-title"><?php the_title(); ?></h1>
		<p class="taxonomy-description">
			<span><?php echo $seniority; ?></span>
			<span><?php echo $location; ?></span>
		</p>
	</header><!-- .page-header -->



		<div class="the-career__content" data-aos="fade-up" data-aos-duration="600" data-aos-delay="100">
			<div class="lp-container">
				<div class="the-career__description"><?php the_content(); ?></div>
			</div>
		</div>

</article>
