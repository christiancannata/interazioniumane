<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Exit if no permission to embed files
if (
  !(\TheLion\LetsBox\Helpers::check_user_role($this->settings['permissions_add_links']))
) {
    exit();
}

// Add own styles and script and remove default ones
$this->load_scripts();
$this->load_styles();

function LetsBox_remove_all_scripts()
{
    global $wp_scripts;
    $wp_scripts->queue = [];

    wp_enqueue_script('jquery-effects-fade');
    wp_enqueue_script('jquery');
    wp_enqueue_script('LetsBox');
    wp_enqueue_script('LetsBox.DocumentLinker');
}

function LetsBox_remove_all_styles()
{
    global $wp_styles;
    $wp_styles->queue = [];
    wp_enqueue_style('LetsBox.ShortcodeBuilder');
    wp_enqueue_style('LetsBox.CustomStyle');
    wp_enqueue_style('Eva-Icons');
}

add_action('wp_print_scripts', 'LetsBox_remove_all_scripts', 1000);
add_action('wp_print_styles', 'LetsBox_remove_all_styles', 1000);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title><?php esc_html_e('Insert Direct links', 'wpcloudplugins'); ?></title>
  <?php wp_print_scripts(); ?>
  <?php wp_print_styles(); ?>
</head>

<body class="LetsBox letsbox">

  <form action="#" data-callback="<?php echo isset($_REQUEST['callback']) ? $_REQUEST['callback'] : ''; ?>">

    <div class="wrap">
      <div class="letsbox-header">
        <div class="letsbox-logo"><a href="https://www.wpcloudplugins.com" target="_blank"><img src="<?php echo LETSBOX_ROOTPATH; ?>/css/images/wpcp-logo-dark.svg" height="64" width="64"/></a></div>
        <div class="letsbox-form-buttons">
          <div id="do_link" class="simple-button default" name="insert"><?php esc_html_e('Insert Links', 'wpcloudplugins'); ?>&nbsp;<i class="eva eva-play-circle-outline eva-lg" aria-hidden="true"></i></div>
        </div>

        <div class="letsbox-title"><?php esc_html_e('Insert Direct links', 'wpcloudplugins'); ?></div>

      </div>

      <div class="letsbox-panel letsbox-panel-full">
        <p><?php esc_html_e('Please note that the embedded files need to be public (with link)', 'wpcloudplugins'); ?></p>
        <?php

        $atts = [
            'mode' => 'files',
            'showfiles' => '1',
            'upload' => '0',
            'delete' => '0',
            'rename' => '0',
            'addfolder' => '0',
            'viewrole' => 'all',
            'candownloadzip' => '0',
            'showsharelink' => '0',
            'previewinline' => '0',
            'mcepopup' => 'links',
            'includeext' => '*',
            'search' => '1',
            '_random' => 'embed',
        ];

        $user_folder_backend = apply_filters('letsbox_use_user_folder_backend', $this->settings['userfolder_backend']);

        if ('No' !== $user_folder_backend) {
            $atts['userfolders'] = $user_folder_backend;

            $private_root_folder = $this->settings['userfolder_backend_auto_root'];
            if ('auto' === $user_folder_backend && !empty($private_root_folder) && isset($private_root_folder['id'])) {
                if (!isset($private_root_folder['account']) || empty($private_root_folder['account'])) {
                    $main_account = $this->get_processor()->get_accounts()->get_primary_account();
                    $atts['account'] = $main_account->get_id();
                } else {
                    $atts['account'] = $private_root_folder['account'];
                }

                $atts['dir'] = $private_root_folder['id'];

                if (!isset($private_root_folder['view_roles']) || empty($private_root_folder['view_roles'])) {
                    $private_root_folder['view_roles'] = ['none'];
                }
                $atts['viewuserfoldersrole'] = implode('|', $private_root_folder['view_roles']);
            }
        }

        echo $this->create_template($atts);
        ?>
      </div>

      <div class="footer"></div>
    </div>
  </form>

</body>
</html>