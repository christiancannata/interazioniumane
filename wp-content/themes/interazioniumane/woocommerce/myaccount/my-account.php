<?php
/**
 * My Account page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/my-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * My Account navigation.
 *
 * @since 2.6.0
 */

 $current_user = wp_get_current_user();
 global $current_user;

 //Avatar
 $user_id = $current_user->ID;
 $this_user = 'user_' .  $user_id;


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

 $user_bio = get_field('user-bio', $this_user);
 ?>


 <header class="page-header author-header">

	 <div class="user-profile__header">
		 <div class="user-profile__header--flex">
			 <div class="user-profile__header--sx">
				 <div class="user-profile--avatar big">
					 <?php if( $user_avatar === true): ?>
 						<div class="top-user__avatar">
 							<?php echo get_avatar( $user_id, 300 ); ?>
 						</div>
 					<?php else: ?>
 					<?php
 					if($user_first_name) {
 						echo '<span class="user-avatar">' .$firstCharacter . '</span>';
 					}	?>
 				<?php endif; ?>
				 </div>
				 <div class="user-profile--info">
					 <h1 class="user-profile--name"><?php echo esc_html( $current_user->first_name ); ?> <?php echo esc_html( $current_user->last_name ); ?></h1>

           <?php
           $featured_posts = get_field('user_courses', $this_user);

           if( $featured_posts ) { 
              echo '<p class="user-profile--bio">Sei iscritto a ';
           		echo count($featured_posts);
              echo ' corsi!';
              echo '</p>';
            }
           				?>



				 </div>
			 </div>
		 </div>
	 </div>

 </header><!-- .page-header -->

 <div class="woocommerce-flex">

	<?php do_action( 'woocommerce_account_navigation' ); ?>

	<div class="woocommerce-MyAccount-content">

		<?php
			/**
			 * My Account content.
			 *
			 * @since 2.6.0
			 */
			do_action( 'woocommerce_account_content' );
		?>
	</div>
	</div>
