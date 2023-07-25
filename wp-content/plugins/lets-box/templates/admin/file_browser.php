<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\LetsBox;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Exit if no permission
if (
    !Helpers::check_user_role(Core::get_setting('permissions_see_filebrowser'))
) {
    exit;
}

?>
<div id="wpcp" class="wpcp-app hidden" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
    <div class="absolute z-10 inset-0 bg-gray-100">
        <div class="min-h-full bg-gray-100">
            <div class="pb-32 bg-gradient-to-br from-brand-color-900 to-brand-color-secondary-900">
                <header class="flex items-center justify-between h-24 px-4 sm:px-0">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        <a href="https://www.wpcloudplugins.com" target="_blank">
                            <img class="h-12 w-auto" src="<?php echo LETSBOX_ROOTPATH; ?>/css/images/wpcloudplugins-logo-light.png" />
                        </a>
                    </div>
                </header>
            </div>

            <main class="-mt-32">
                <div class="max-w-7xl mx-auto pb-12 px-4 sm:px-6 lg:px-8">

                    <div class="bg-white rounded-lg shadow px-5 py-6 sm:px-6">

                        <h1 class="text-3xl font-bold text-brand-color-900 mb-6"><?php esc_html_e('File Browser', 'wpcloudplugins'); ?></h1>

                        <?php
                $processor = Processor::instance();

$params = [
    'singleaccount' => '0',
    'dir' => '0',
    'mode' => 'files',
    'viewrole' => 'all',
    'downloadrole' => 'all',
    'uploadrole' => 'all',
    'upload' => '1',
    'rename' => '1',
    'delete' => '1',
    'deleterole' => 'all',
    'addfolder' => '1',
    // 'createdocument' => '1',
    'edit' => '1',
    'move' => '1',
    'copy' => '1',
    'candownloadzip' => '1',
    'showsharelink' => '1',
    'search' => '1',
    'deeplink' => '1',
    'editdescription' => '1',
    'themestyle' => 'light',
];

$user_folder_backend = apply_filters('letsbox_use_user_folder_backend', $processor->get_setting('userfolder_backend'));

if ('No' !== $user_folder_backend) {
    $params['userfolders'] = $user_folder_backend;

    $private_root_folder = $processor->get_setting('userfolder_backend_auto_root');
    if ('auto' === $user_folder_backend && !empty($private_root_folder) && isset($private_root_folder['id'])) {
        if (!isset($private_root_folder['account']) || empty($private_root_folder['account'])) {
            $main_account = Accounts::instance()->get_primary_account();
            $params['account'] = $main_account->get_id();
        } else {
            $params['account'] = $private_root_folder['account'];
        }

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
            </main>
        </div>
    </div>

</div>