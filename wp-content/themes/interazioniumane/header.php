<?php
/**
 * The header for our theme
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$container = get_theme_mod( 'understrap_container_type' );


//Get User info
$current_user = wp_get_current_user();
global $current_user;

$user_id = $current_user->ID;

$str = get_avatar( $user_id);
preg_match('/(src=["\'](.*?)["\'])/', $str, $match);  //find src="X" or src='X'
$split = preg_split('/["\']/', $match[0]); // split by quotes

$src = $split[1]; // X between quotes
$word = 'gravatar.com';
$user_avatar = '';
if(strpos($src, $word) !== false){
		$user_avatar = false;
} else{
		$user_avatar = true;
}

$user_first_name = $current_user->first_name;
$firstCharacter = '';
if($user_first_name) {
	$firstCharacter = $user_first_name[0];
}

//Banner codici sconto
$info_iu = get_field('info_iu', 'option');

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<!-- Google Tag Manager -->
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','GTM-PK9X5WQ');</script>
	<!-- End Google Tag Manager -->

	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="profile" href="http://gmpg.org/xfn/11">

	<link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_template_directory_uri(); ?>/assets/img/brand/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="<?php echo get_template_directory_uri(); ?>/assets/img/brand/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="<?php echo get_template_directory_uri(); ?>/assets/img/brand/favicon-16x16.png">
	<link rel="manifest" href="<?php echo get_template_directory_uri(); ?>/assets/img/brand/site.webmanifest">
	<link rel="mask-icon" href="<?php echo get_template_directory_uri(); ?>/assets/img/brand/safari-pinned-tab.svg" color="#4700f4">
	<meta name="msapplication-TileColor" content="#ffffff">
	<meta name="theme-color" content="#ffffff">

	<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/css/aos.css">
	<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/css/owl.carousel.min.css">
	<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/css/emoji.min.css">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?> <?php understrap_body_attributes(); ?> <?php if($info_iu): ?> style="padding-bottom:90px;" <?php endif; ?>>

	<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PK9X5WQ"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<?php do_action( 'wp_body_open' ); ?>
<div class="site" id="page">


	<!-- ******************* The Navbar Area ******************* -->
	<div class="header">
	<div class="header__flex">
		<div class="header__brand">
			<div class="main-logo">
	      <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="main-logo__link" title="Interazioni Umane">
					<span>
	        	<?php get_template_part( 'global-templates/logo-iu' ); ?>
					</span>
					<span>
		        <?php get_template_part( 'global-templates/logo-text' ); ?>
					</span>

	      </a>
	    </div>
		</div>
		<div class="header__search-mobile">
			<span class="icon-search openSearch"></span>
		</div>
		<div class="header__hamburger">
			<span class="hamburger-menu icon-hamburger"></span>
		</div>
		<div class="header__menu">
			<div class="header__menu--mobile">
				<span class="header__menu--mobile--menu">Menu</span>
				<span class="header__menu--mobile--close icon-close close-menu"></span>
			</div>
			<div class="main-menu">

				<?php wp_nav_menu(
					array(
						'theme_location'  => 'primary',
						'container_class' => 'main-menu-container',
						'container_id'    => 'navbarNavDropdown',
						'menu_class'      => 'top-menu',
						'fallback_cb'     => '',
						'menu_id'         => 'main-menu',
						'depth'           => 2,
						'walker'          => new Understrap_WP_Bootstrap_Navwalker(),
					)
				); ?>
				<ul class="menu--search top-menu only-desktop">
					<li><a href="#" class="openSearch" title="Cerca"><span class="icon-search"></span>Cerca</a></li>
				</ul>

				<?php if ( is_user_logged_in() ): ?>
				<div class="top-user-menu-mobile">
					<div class="top-user-menu-mobile__avatar">
						<?php if( $user_avatar === true): ?>
							<div class="top-user__avatar">
								<?php echo get_avatar( $user_id ); ?>
							</div>
						<?php else: ?>
						<?php
						if($user_first_name) {
							echo '<span class="user-avatar">' .$firstCharacter . '</span>';
						}?>
					<?php endif; ?>
					<span class="top-user-menu-mobile__name">Ciao, <?php echo $current_user->first_name; ?></span>
					</div>
					<?php wp_nav_menu(
						array(
							'theme_location'  => 'footer_menu_user',
							'container_class' => 'main-menu-container',
							'container_id'    => 'navbarNavDropdown',
							'menu_class'      => 'top-menu',
							'fallback_cb'     => '',
							'menu_id'         => 'main-menu',
							'walker'          => new Understrap_WP_Bootstrap_Navwalker(),
						)
					); ?>
					<ul class="top-menu">
						<li><a href="<?php echo wp_logout_url(home_url()) ?>" title="Esci">Esci</a></li>
					</ul>
				</div>
				<?php else: ?>
					<div class="top-user-menu-mobile">
						<ul class="top-menu">
							<li><a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>" title="Accedi">Accedi</a></li>
						</ul>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<div class="top-user">
			<?php if ( is_user_logged_in() ): ?>
				<a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>" class="top-user__link user-link-mobile">
					<?php if( $user_avatar === true): ?>
						<div class="top-user__avatar">
							<?php echo get_avatar( $user_id ); ?>
						</div>
					<?php else: ?>
					<?php
					if($user_first_name) {
						echo '<span class="user-avatar">' .$firstCharacter . '</span>';
					}	?>
				<?php endif; ?>
				<span class="top-user__name">Ciao, <?php echo $current_user->first_name; ?> <span class="icon-arrow-down top-user__arrow"></span></span>
				</a>
				<div class="top-user__menu">
					<?php wp_nav_menu(
						array(
							'theme_location'  => 'footer_menu_user',
							'container_class' => 'menu-container',
							'container_id'    => 'menu-user',
							'menu_class'      => 'user-menu',
							'fallback_cb'     => '',
							'menu_id'         => 'user-menu',
							'walker'          => new Understrap_WP_Bootstrap_Navwalker(),
						)
					); ?>
					<ul>
						<li><a href="<?php echo wp_logout_url(home_url()) ?>" title="Esci">Esci</a></li>
					</ul>
				</div>
			<?php else: ?>
				<a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>" class="dstr-button dstr-button-primary dstr-button-small top-user__login">Accedi</a>
			<?php endif; ?>
		</div>
	</div>
	</div>

	<div class="header--search">
		<?php get_search_form() ?>
	</div>

	<?php if($info_iu): ?>

		<div class="info_iu">
			<span class="info_iu--button"><span class="icon-arrow-down"></span></span>
			<h4 class="info_iu--title"><?php echo the_field('info_iu_title', 'option'); ?></h4>
			<div class="info_iu--subtitle"><?php echo the_field('info_iu_subtitle', 'option'); ?></div>
		</div>
	<?php endif;?>
