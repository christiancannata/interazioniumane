<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\LetsBox;

// Exit if no permission to add shortcodes
if (
    !Helpers::check_user_role(Core::get_setting('permissions_add_shortcodes'))
) {
    $loaders = Core::get_setting('loaders');
    ?>
    <div id='LetsBox'>
        <div class='LetsBox list-container noaccess'>
            <div style="max-width:512px; margin: 0 auto; text-align:center;">
                <img src="<?php echo $loaders['protected']; ?>" data-src-retina="<?php echo $loaders['protected']; ?>" style="display:inline-block">
            </div>
        </div>
    </div>
    <?php
    exit;
}

// Specific Shortcode Builder configurations
$standalone = (isset($_REQUEST['standalone'])) ? true : false;
$uploadbox_only = (isset($_REQUEST['asuploadbox'])) ? true : false;
$for = (isset($_REQUEST['for'])) ? $_REQUEST['for'] : 'shortcode_buider';

AdminLayout::set_setting_value_location('GET');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" class="wpcp-h-full wpcp-bg-gray-100">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php esc_html_e('Shortcode Builder', 'wpcloudplugins'); ?></title>
    <?php wp_print_styles(); ?>
</head>

<body class="wpcp-h-full">
    <div id="wpcp" class="wpcp-app hidden" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
        <form action="#" data-callback="<?php echo isset($_REQUEST['callback']) ? $_REQUEST['callback'] : ''; ?>" data-configuration="<?php echo ($uploadbox_only) ? 'upload-field' : ''; ?>">

            <!-- Static sidebar for desktop -->
            <div class="flex w-64 flex-col fixed inset-y-0">
                <!-- Sidebar component, swap this element with another sidebar if you like -->
                <div class="flex-1 flex flex-col min-h-0 border-r border-gray-200 bg-white">
                    <div class="flex-1 flex flex-col pt-5 pb-4 overflow-y-auto">
                        <div class="flex items-center flex-shrink-0 px-4">
                            <img class="h-8 w-auto" src="<?php echo LETSBOX_ROOTPATH; ?>/css/images/wpcloudplugins-logo-dark.png" alt="WP Cloud Plugins">
                        </div>
                        <nav class="mt-5 flex-1 px-2 bg-white space-y-1">
                            <?php
                        foreach (ShortcodeBuilder::$nav_tabs as $nav_tab_key => $nav_tab_settings) {
                            AdminLayout::render_nav_tab(
                                array_merge(['key' => $nav_tab_key], $nav_tab_settings)
                            );
                        }
?>
                        </nav>
                    </div>
                    <div class="flex-shrink-0 flex border-t border-gray-200 p-4">
                        <div class="flex flex-shrink-0 items-center w-full  space-x-1">
                            <button id="wpcp-button-create-shortcode" type="button" class="wpcp-button-secondary inline-flex justify-center" data-dialog-id="#wpcp-modal-show-shortcode">
                                <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                </svg>
                                Shortcode
                            </button>
                            <?php if (false === $standalone) { ?>
                            <button id="wpcp-button-save-shortcode" type="button" class="wpcp-button-primary wpcp-dialog-close inline-flex justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Save
                            </button>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="pl-64 flex flex-col flex-1">
                <main class="flex-1">
                    <?php

                    foreach (ShortcodeBuilder::$nav_tabs as $nav_tab_key => $nav_tab_settings) {
                        AdminLayout::render_nav_panel_open(
                            array_merge(['key' => $nav_tab_key], $nav_tab_settings)
                        ); ?>

                    <div class="max-w-7xl mx-auto px-4">

                        <?php
                        foreach (ShortcodeBuilder::$fields[$nav_tab_key] as $field_key => $field) {
                            $field['key'] = $field_key;
                            AdminLayout::render_field($field_key, $field);
                        } ?>
                    </div>
                    <?php

                    AdminLayout::render_nav_panel_close();
                    }
?>
                </main>
            </div>


            <!-- Modal Missing Content -->
            <div id="wpcp-modal-missing-content" class="wpcp-dialog hidden">
                <div class="relative z-20" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
                    <div class="fixed z-30 inset-0 overflow-y-auto">
                        <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">

                            <div class="relative bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full sm:p-6">
                                <div>
                                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-brand-color-900">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="mt-3 text-center sm:mt-5">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title"><?php esc_html_e('Configuration problem', 'wpcloudplugins'); ?></h3>
                                        <div class="my-3 p-4">
                                            <p><?php esc_html_e('This module is currently linked to a cloud account and/or folder which is no longer accessible by the plugin. To resolve this, please relink the module again to the correct folder.', 'wpcloudplugins'); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="pt-3 sm:flex sm:justify-center">
                                    <button type="button" class="wpcp-button-primary wpcp-dialog-close w-full justify-center sm:ml-3 sm:w-auto sm:text-sm">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Modal Missing Content -->

            <!-- Modal Raw Shortcode -->
            <div id="wpcp-modal-show-shortcode" class="wpcp-dialog hidden">
                <div class="relative z-20" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
                    <div class="fixed z-30 inset-0 overflow-y-auto">
                        <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">

                            <div class="relative bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full sm:p-6">
                                <div>
                                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-brand-color-900">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                        </svg>
                                    </div>
                                    <div class="mt-3 text-center sm:mt-5">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title"><?php esc_html_e('Raw Shortcode', 'wpcloudplugins'); ?></h3>
                                        <div class="my-3 p-4 border-2 border-gray-200 rounded-md break-all text-xs">
                                            <textarea id="wpcp-raw-shortcode-preview" rows="5" class="wpcp-input-textarea max-w-xl block w-full shadow-sm focus:ring-brand-color-700 focus:border-brand-color-700 sm:text-sm border border-gray-300 rounded-md"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="pt-3 sm:flex sm:justify-center">
                                    <button id="wpcp-button-import-shortcode" type="button" class="wpcp-button-secondary w-full justify-center sm:ml-3 sm:w-auto sm:text-sm"><?php esc_html_e('Import', 'wpcloudplugins'); ?></button>
                                    <button id="wpcp-copy-raw-shortcode" type="button" class="wpcp-button-secondary w-full justify-center sm:ml-3 sm:w-auto sm:text-sm"><?php esc_html_e('Copy to clipboard', 'wpcloudplugins'); ?></button>
                                    <button type="button" class="wpcp-button-primary wpcp-dialog-close w-full justify-center sm:ml-3 sm:w-auto sm:text-sm"><?php esc_html_e('Close', 'wpcloudplugins'); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Modal Raw Shortcode -->

            <!-- Modal Review -->
            <div id="wpcp-modal-review" class="wpcp-dialog <?php echo Core::ask_for_review() ? '' : 'hidden'; ?>">
                <div class="relative z-20" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
                    <div class="fixed z-30 inset-0 overflow-y-auto">
                        <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">

                            <div class="relative bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full sm:p-6">
                                <div>
                                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-brand-color-900">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                        </svg>
                                    </div>

                                    <div class="mt-3 text-center sm:mt-5 enjoying-container lets-ask">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title"><?php esc_html_e('Enjoying this plugin?', 'wpcloudplugins'); ?></h3>
                                        <div class="pt-6 sm:flex sm:justify-center">
                                            <button type="button" id="enjoying-button-lets-ask-no" class="wpcp-button-secondary w-full justify-center sm:ml-3 sm:w-auto sm:text-sm"><?php esc_html_e('Not really', 'wpcloudplugins'); ?></button>
                                            <button type="button" id="enjoying-button-lets-ask-yes" class="wpcp-button-primary w-full justify-center sm:ml-3 sm:w-auto sm:text-sm" id="enjoying-button-mwah-yes"><?php esc_html_e('Yes!', 'wpcloudplugins'); ?></button>
                                        </div>
                                    </div>

                                    <div class="mt-3 text-center sm:mt-5 enjoying-container go-for-it hidden">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title"><?php esc_html_e('Great! How about a review, then?', 'wpcloudplugins'); ?></h3>
                                        <div class="pt-6 sm:flex sm:justify-center">
                                            <button type="button" id="enjoying-button-go-for-it-no" class="wpcp-button-secondary wpcp-dialog-close w-full justify-center sm:ml-3 sm:w-auto sm:text-sm"><?php esc_html_e('No, thanks', 'wpcloudplugins'); ?></button>
                                            <a type="button" id="enjoying-button-go-for-it-yes" class="wpcp-button-primary wpcp-dialog-close w-full justify-center sm:ml-3 sm:w-auto sm:text-sm" id="enjoying-button-mwah-yes" href="https://1.envato.market/c/1260925/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fletsbox-box-plugin-for-wordpress%2Freviews%2F8204640" target="_blank"><?php esc_html_e('Ok, sure!', 'wpcloudplugins'); ?></a>
                                        </div>
                                    </div>


                                    <div class="mt-3 text-center sm:mt-5 enjoying-container mwah hidden">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title"><?php esc_html_e('Would you mind giving us some feedback?', 'wpcloudplugins'); ?></h3>
                                        <div class="pt-6 sm:flex sm:justify-center">
                                            <a type="button" class="wpcp-button-secondary wpcp-dialog-close w-full justify-center sm:ml-3 sm:w-auto sm:text-sm" id="enjoying-button-mwah-yes" href="https://docs.google.com/forms/d/e/1FAIpQLSct8a8d-_7iSgcvdqeFoSSV055M5NiUOgt598B95YZIaw7LhA/viewform?entry.83709281=Lets-Box+(Box.com)&entry.450972953&entry.1149244898" target="_blank"><?php esc_html_e('Ok, sure!', 'wpcloudplugins'); ?></a>
                                            <button type="button" id="enjoying-button-mwah-no" class="wpcp-button-primary wpcp-dialog-close w-full justify-center sm:ml-3 sm:w-auto sm:text-sm"><?php esc_html_e('No, thanks', 'wpcloudplugins'); ?></button>
                                        </div>
                                    </div>


                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Modal Review -->


        </form>
    </div>

    <?php wp_print_scripts(); ?>
</body>

</html>