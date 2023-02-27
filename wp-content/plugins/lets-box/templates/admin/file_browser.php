<div class="letsbox admin-settings">

     <div class="letsbox-header">
<div class="letsbox-logo"><a href="https://www.wpcloudplugins.com" target="_blank"><img src="<?php echo LETSBOX_ROOTPATH; ?>/css/images/wpcp-logo-dark.svg" height="64" width="64"/></a></div>
    <div class="letsbox-title"><?php esc_html_e('File Browser', 'wpcloudplugins'); ?></div>
  </div>

  <div class="letsbox-panel letsbox-panel-full">
    <?php
    $processor = $this->get_processor();

    $params = ['mode' => 'files',
        'viewrole' => 'all',
        'downloadrole' => 'all',
        'uploadrole' => 'all',
        'upload' => '1',
        'rename' => '1',
        'delete' => '1',
        'deleterole' => 'all',
        'addfolder' => '1',
        //'createdocument' => '1',
        'edit' => '1',
        'move' => '1',
        'copy' => '1',
        'candownloadzip' => '1',
        'showsharelink' => '1',
        'search' => '1',
        'deeplink' => '1',
        'editdescription' => '1', ];

    $user_folder_backend = apply_filters('letsbox_use_user_folder_backend', $processor->get_setting('userfolder_backend'));

    if ('No' !== $user_folder_backend) {
        $params['userfolders'] = $user_folder_backend;

        $private_root_folder = $processor->get_setting('userfolder_backend_auto_root');
        if ('auto' === $user_folder_backend && !empty($private_root_folder) && isset($private_root_folder['id'])) {
            $params['dir'] = $private_root_folder['id'];

            if (!isset($private_root_folder['view_roles'])) {
                $private_root_folder['view_roles'] = ['none'];
            }
            $params['viewuserfoldersrole'] = implode('|', $private_root_folder['view_roles']);
        }
    }

    $params = apply_filters('letsbox_set_shortcode_filebrowser_backend', $params);

    echo $processor->create_from_shortcode($params);
    ?>
  </div>
</div>