<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2022, WP Cloud Plugins
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
    !Helpers::check_user_role(Core::get_setting('permissions_link_users'))
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

                        <h1 class="text-3xl font-bold text-brand-color-900 mb-6"><?php esc_html_e('Link Private Folders', 'wpcloudplugins'); ?></h1>

                        <div>
                            <form method="post">
                                <input type="hidden" name="page" />
                                <?php
                    $users_list = new User_List_Table();
$users_list->views();
$users_list->prepare_items();
$users_list->search_box('search', 'search_id');
$users_list->display(); ?>
                            </form>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <!-- Modal Selector -->
    <div id="wpcp-modal-selector" class="wpcp-dialog hidden">
        <div class="relative z-20" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-90 transition-opacity backdrop-blur-sm"></div>
            <div class="fixed z-30 inset-0 overflow-y-auto">
                <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">

                    <div class="relative bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full sm:p-6">
                        <div>
                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-brand-color-900">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>

                            </div>
                            <div class="mt-3 text-center sm:mt-5">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title"><?php esc_html_e('Select Private Folder', 'wpcloudplugins'); ?></h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        <?php esc_html_e('Select the Private Folder the user should be linked to. Set in your Module the Private Folders feature to "manual" in order to give the user access to this folder only.', 'wpcloudplugins'); ?>
                                    </p>
                                </div>

                                <div class="mt-6 mb-4 sm:flex items-center justify-center">
                                    <div id='lb-embedded' class="w-full">
                                        <?php

echo Processor::instance()->create_from_shortcode(
    [
        'singleaccount' => '0',
        'maxheight' => '200px',
        'mode' => 'files',
        'filelayout' => 'list',
        'filesize' => '0',
        'filedate' => '0',
        'upload' => '0',
        'delete' => '0',
        'rename' => '0',
        'addfolder' => '0',
        'showfiles' => '0',
        'downloadrole' => 'none',
        'candownloadzip' => '0',
        'showsharelink' => '0',
        'popup' => 'private_folders_selector',
        'search' => '1',
    ]
); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-6 sm:flex sm:gap-3 sm:flex-row-reverse">
                            <button type="button" class="wpcp-button-primary wpcp-dialog-entry-select inline-flex justify-center w-full sm:w-auto"><?php esc_html_e('Select'); ?></button>                            
                            <button type="button" class="wpcp-button-secondary wpcp-dialog-close inline-flex justify-center w-full sm:w-auto"><?php esc_html_e('Close'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Modal Selector -->

</div>

<style>
#wpcp table {
    border: none;
}

#wpcp tfoot {
    display: none;
}

#wpcp th {
    font-weight: bold;
    font-size: 1.1rem;
}

#wpcp tbody tr:hover {
    background-color: rgb(0 0 0 / 7%) !important;
}

#wpcp td {
    padding: 8px 4px;
    vertical-align: middle;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}

#wpcp .column-avatar {
    width: 64px;
}

#wpcp .column-username {
    width: 150px;
}

#wpcp .column-role {
    width: 100px;
}

#wpcp .column-role {
    width: 100px;
}

#wpcp .column-buttons {
    width: 48px;
    text-align: center;
}
</style>