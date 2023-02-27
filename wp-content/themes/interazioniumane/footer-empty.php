<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$container = get_theme_mod( 'understrap_container_type' );
?>


</div><!-- #page we need this extra closing tag here -->


<?php wp_footer(); ?>

<script>
	AOS.init({
    easing: 'ease-in-out-sine'
  });
</script>

</body>

</html>
