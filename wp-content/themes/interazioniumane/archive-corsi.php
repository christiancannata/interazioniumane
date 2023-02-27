<?php
/**
 * The template for displaying archive pages
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();

$container = get_theme_mod( 'understrap_container_type' );

//ID Categoria
$category = get_queried_object();
$categoryName = $category->name;
?>

<?php
$courses = new WP_Query([
'post_type' => 'corsi',
'posts_per_page' => -1,
'order_by' => 'date',
'order' => 'desc',
]);
//global $wp_query;
		$count = $courses->post_count;
		$totalpost = $courses->found_posts;
?>

<div class="wrapper" id="archive-wrapper">

	<div class="archive-container" id="content" tabindex="-1">

			<main class="archive-main" id="main">
				<header class="page-header">
					<h1 class="page-title">Formazione</h1>
				</header><!-- .page-header -->

				<div class="ffix"></div>


					<form style="display: none;" action="<?php echo site_url() ?>/wp-admin/admin-ajax.php" method="POST" id="filter">
						<div class="filtri-flex">
					<?php $taxonomyName = "corsi";
						//This gets top layer terms only.  This is done by setting parent to 0.
						$parent_terms = get_terms( $taxonomyName, array( 'parent' => 0, 'orderby' => 'slug', 'hide_empty' => false ) );
						foreach ( $parent_terms as $pterm ) {
							echo '<div>';
							echo '<select class="filter--select" name="category-'.$pterm->slug.'"><option value="">' . $pterm->name . '</option>';
						    //Get the Child terms
						    $terms = get_terms( $taxonomyName, array( 'parent' => $pterm->term_id, 'orderby' => 'slug', 'hide_empty' => false ) );
						    foreach ( $terms as $term ) {
						        echo '<option value="' . $term->term_id . '">' . $term->name . '</option>';
						    }
								echo '</select>';
								echo '</div>';
						}
				 ?>




<span class="count-posts"><span class="count-posts--showed"><?php echo $count; ?></span> di <span class="count-posts--total"><?php echo $totalpost; ?></span></span>
 <button id="apply-filters">Apply filter</button>
 <input type="hidden" name="action" value="myfilter">
 </div>
</form>


<div id="response">

<?php if($courses->have_posts()): ?>


		<section class="archive-flex">
			<?php
			while($courses->have_posts()) : $courses->the_post();
			get_template_part( 'loop-templates/content-card', get_post_format() );
			endwhile;
			?>
		</section>

<?php wp_reset_postdata(); ?>
<?php endif; ?>

</div>


</div>








			</main><!-- #main -->




	</div><!-- #content -->

	</div><!-- #archive-wrapper -->
	<div id="custom-ajax-url" style="display: none !important;"><?php echo admin_url('admin-ajax.php'); ?></div>
<?php get_footer();
