<?php
/**
* Template Name: Password Lost
*/
get_header('empty');

?>

<div class="top-empty">
  <div class="top-empty__content">
    <div class="main-logo white">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="main-logo__link" title="District">
        <?php get_template_part( 'global-templates/logo-district' ); ?>
        <?php get_template_part( 'global-templates/logo-circle' ); ?>
      </a>
    </div>
    <div class="top-empty__menu">
      <p>Ti sei sbagliato? <a href="<?php echo esc_url( home_url( '/' ) ); ?>accedi" class="top-empty__link" title="Accedi">Accedi</a>.</p>
    </div>
  </div>
</div>


<div class="wrapper" id="page-wrapper">


	<div class="signup-container" id="content" tabindex="-1">

    <div class="signup-sidebar" style="background-image:url('<?php echo get_template_directory_uri() .'/assets/img/backgrounds/district-bg-'; ?><?php $random = rand(1,10); echo $random; ?>.jpg')">
      <div class="signup-sidebar__text">
        <h2 class="signup-sidebar__title">Ok, c’è LinkedIn, ma è brutto.</h2>
      </div>
    </div>

    <div class="signup-main">

			<main class="site-main" id="main">

				<div class="form-contacts">
          <header class="entry-header display center-page">
  	        <h1 class="entry-title">Recupera la password</h1>
  	        <h2 class="entry-subtitle">Inserisci la tua email per reimpostare la password.</h2>
  	      </header>

          <div class="signup-form">
            <?php echo do_shortcode('[custom-password-lost-form]'); ?>
          </div>
        </div>

			</main><!-- #main -->

    </div>



	</div><!-- #content -->

</div><!-- #page-wrapper -->


<?php get_footer('empty'); ?>
