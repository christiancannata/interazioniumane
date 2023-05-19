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
    !Helpers::check_user_role(Core::get_setting('permissions_edit_settings'))
) {
    exit;
}

?><div id="wpcp" class="wpcp-app hidden" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
    <div class="absolute z-10 inset-0 bg-gray-100">
        <!-- Static sidebar for desktop -->
        <div class="font-sans flex w-64 flex-col fixed md:bottom-0 md:top-8">
            <!-- Sidebar component, swap this element with another sidebar if you like -->
            <div class="flex-1 flex flex-col min-h-0 bg-gradient-to-t from-brand-color-900 to-brand-color-secondary-900">
                <div class="flex flex-col flex-grow border-r border-gray-200 pt-5 bg-white overflow-y-auto">
                    <div class="flex items-center flex-shrink-0 px-4">
                        <img class="h-8 w-auto" src="<?php echo LETSBOX_ROOTPATH; ?>/css/images/wpcloudplugins-logo-dark.png" alt="WP Cloud Plugins">
                    </div>
                    <div class="mt-5 flex-grow flex flex-col">
                        <nav class="flex-1 px-2 pb-4 space-y-1">
                            <!-- Current: "bg-gray-100 text-gray-900", Default: "text-gray-600 hover:bg-gray-50 hover:text-brand-color-900" -->
                            <a href="#" data-nav-tab="wpcp-dashboard" class="wpcp-tab-active bg-gray-100 text-gray-900 hover:bg-gray-50 hover:text-brand-color-900 group active:text-brand-color-900 focus:text-brand-color-900 group flex items-center px-2 py-2 text-sm font-medium rounded-md  focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-brand-color-900">
                                <!-- Heroicon name: outline/home -->
                                <svg class="text-gray-500 mr-3 flex-shrink-0 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                <?php esc_html_e('Dashboard', 'wpcloudplugins'); ?>
                            </a>

                            <?php
            if (License::is_valid()) {
                AdminLayout::render_nav_tab([
                    'key' => 'layout',
                    'title' => esc_html__('Layout', 'wpcloudplugins'),
                    'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />',
                ]);

                AdminLayout::render_nav_tab([
                    'key' => 'private-folders',
                    'title' => esc_html__('Private Folders', 'wpcloudplugins'),
                    'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />',
                ]);

                AdminLayout::render_nav_tab([
                    'key' => 'advanced',
                    'title' => esc_html__('Advanced', 'wpcloudplugins'),
                    'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />',
                ]);

                AdminLayout::render_nav_tab([
                    'key' => 'integrations',
                    'title' => esc_html__('Integrations', 'wpcloudplugins'),
                    'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />',
                ]);

                AdminLayout::render_nav_tab([
                    'key' => 'notifications',
                    'title' => esc_html__('Notifications', 'wpcloudplugins'),
                    'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />',
                ]);

                AdminLayout::render_nav_tab([
                    'key' => 'permissions',
                    'title' => esc_html__('Permissions', 'wpcloudplugins'),
                    'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />',
                ]);

                AdminLayout::render_nav_tab([
                    'key' => 'statistics',
                    'title' => esc_html__('Statistics', 'wpcloudplugins'),
                    'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />',
                ]);

                AdminLayout::render_nav_tab([
                    'key' => 'tools',
                    'title' => esc_html__('Tools', 'wpcloudplugins'),
                    'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />',
                ]);

                AdminLayout::render_nav_tab([
                    'key' => 'system-information',
                    'title' => esc_html__('System information', 'wpcloudplugins'),
                    'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
                ]);

                AdminLayout::render_nav_tab([
                    'key' => 'support',
                    'title' => esc_html__('Support', 'wpcloudplugins'),
                    'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />',
                ]); ?>

                            <?php
            }
?>

                        </nav>
                    </div>
                </div>

                <div class="flex-shrink-0 flex flex-col py-2 px-4 space-y-1 bg-white">
                    <div class="flex flex-grow flex-col">
                        <?php echo esc_html__('Version:', 'wpcloudplugins').' '.LETSBOX_VERSION; ?>
                    </div>
                </div>
                
                <div class="flex-shrink-0 flex flex-col border-t border-brand-color-900 p-2 space-y-1">
                    <div class="flex flex-grow flex-col">
                        <div class="">
                            <p class="text-lg font-semibold text-white px-2 py-1 ">
                                Other Cloud Plugins
                            </p>
                            <a class="text-indigo-100 hover:bg-brand-color-700 hover:text-white group flex items-center px-2 py-1 text-sm font-medium rounded-md" href="https://1.envato.market/L6yXj" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg> Google Drive</a>
                            <a class="text-indigo-100 hover:bg-brand-color-700 hover:text-white group flex items-center px-2 py-1 text-sm font-medium rounded-md" href="https://1.envato.market/vLjyO" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg> Dropbox</a>
                            <a class="text-indigo-100 hover:bg-brand-color-700 hover:text-white group flex items-center px-2 py-1 text-sm font-medium rounded-md" href="https://1.envato.market/yDbyv" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg> OneDrive</a>
                            <!-- <a class="text-indigo-100 hover:bg-brand-color-700 hover:text-white group flex items-center px-2 py-1 text-sm font-medium rounded-md" href="https://1.envato.market/M4B53" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg> Box</a> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pl-64 flex flex-col flex-1">

            <main class="flex-1 bg-gray-100">
                <div class="py-6">

                    <div class="max-w-4xl px-4 sm:px-6 md:px-8 relative">

                        <!-- Dashboard Panel -->
                        <div data-nav-panel="wpcp-dashboard" class=" duration-200 space-y-6">
                            <!-- Start Account Block -->
                            <?php
  $manage_per_site = (false === Processor::instance()->is_network_authorized() || (Processor::instance()->is_network_authorized() && true === is_network_admin()));
$subtitle = sprintf(esc_html__('Manage your %s cloud accounts', 'wpcloudplugins'), 'Box');

if (false === $manage_per_site) {
    $subtitle = sprintf(esc_html__('Authorization is managed by the Network Admin via the %sNetwork Settings%s', 'wpcloudplugins'), '<a href="'.network_admin_url('admin.php?page=LetsBox_network_settings').'" class="wpcp-link-primary">', '</a>');
}

?>
                            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                                <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                                    <div class="-ml-4 -mt-2 flex items-center justify-between flex-wrap sm:flex-nowrap">
                                        <div class="ml-4 mt-2">
                                            <h3 class="text-2xl font-semibold text-gray-900"><?php esc_html_e('Accounts', 'wpcloudplugins'); ?></h3>
                                            <div class="text-base text-gray-500 max-w-xl"><?php echo $subtitle; ?></div>
                                        </div>
                                        <div class="ml-4 mt-2 flex-shrink-0">
                                            <?php
          if (License::is_valid() && $manage_per_site) {
              $app = App::instance(); ?>
                                            <button id='wpcp-add-account-button' type="button" class="wpcp-button-primary" data-url="<?php echo $app->build_auth_url(); ?>">
                                                <!-- Heroicon name: solid/plus-circle -->
                                                <svg xmlns="http://www.w3.org/2000/svg" class="-ml-0.5 mr-2 h-4 w-4"" viewBox=" 0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                                                </svg>
                                                <?php esc_html_e('Add Account', 'wpcloudplugins'); ?>
                                            </button>
                                            <?php
          }
?>
                                        </div>
                                    </div>
                                </div>

                                <ul id="wpcp-account-list" role="list" class="divide-y divide-gray-200 border-b border-gray-200 min-h-[100px] bg-no-repeat bg-center bg-[length:0px_0px] empty:bg-[length:50px_50px]" style="background-image: url('<?php echo LETSBOX_ROOTPATH; ?>/css/images/box_logo.svg')"><?php
                foreach (Accounts::instance()->list_accounts() as $account_id => $account) {
                    AdminLayout::render_account_box($account, !$manage_per_site);
                }
?></ul>

                                <div class="px-4 py-5 sm:px-6">
                                    <div class="-ml-4 -mt-2 flex items-center justify-between flex-wrap sm:flex-nowrap">
                                        <div class="ml-4 mt-2">
                                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                                <a href="#" id="wpcp-read-privacy-policy" class="wpcp-link-primary wpcp-modal-open-dialog" data-dialog-id="#wpcp-modal-privacy-policy">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                    <?php esc_html_e('What happens with my data when I authorize the plugin?', 'wpcloudplugins'); ?>
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <!-- End Account Block -->

                            <!-- Start License Block -->
                            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                                <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                                    <div class="-ml-4 -mt-2 flex items-center justify-between flex-wrap sm:flex-nowrap">
                                        <div class="ml-4 mt-2">
                                            <h3 class="text-2xl font-semibold text-gray-900"><?php esc_html_e('License', 'wpcloudplugins'); ?></h3>
                                            <div class="text-base text-gray-500 max-w-xl"><?php esc_html_e('Thanks for registering your product!', 'wpcloudplugins'); ?></div>
                                        </div>
                                        <div class="ml-4 mt-2 flex-shrink-0">
                                            <a href="<?php echo admin_url('update-core.php?force-check=1'); ?>" type="button" class="wpcp-button-primary">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                </svg>
                                                <?php esc_html_e('Check for updates', 'wpcloudplugins'); ?>
                                            </a>
                                            <a href="https://1.envato.market/M4B53" type="button" class="wpcp-button-secondary" target="_blank">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-3 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                                <?php esc_html_e('Buy License', 'wpcloudplugins'); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <?php
$license_code = License::get();

if (!empty($license_code)) {
    ?>
                                <ul role="list" class="divide-y divide-gray-200 border-b border-gray-200">
                                    <li class="wpcp-license" data-license-code="<?php echo $license_code; ?>">
                                        <div class="block hover:bg-gray-50">
                                            <div class="flex items-center px-4 py-4 sm:px-6">
                                                <div class="min-w-0 flex-1 flex items-center">
                                                    <div class="flex-shrink-0">
                                                        <img class="h-12 w-12 wpcp-license-icon" src="<?php echo LETSBOX_ROOTPATH; ?>/css/images/wpcp-logo-dark.svg" alt="">
                                                    </div>
                                                    <div class="min-w-0 flex-1 px-4 items-center">
                                                        <div>
                                                            <p class="text-xl font-medium text-brand-color-900 truncate"><code><?php echo $license_code; ?></code></p>
                                                            <div class="mt-2 wpcp-license-details hidden">
                                                                <div class="flex items-center justify-start space-x-4 text-sm text-gray-500">
                                                                    <div class="group flex items-center">
                                                                        <!-- Heroicon name: outline/user-circle -->
                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                        </svg>
                                                                        <span class="truncate"><a href="http://themeforest.net/user/" class="wpcp-link-primary wpcp-license-buyer" target="_blank"></a></span>
                                                                    </div>
                                                                    <div class="group flex items-center space">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                        </svg>
                                                                        <span class="wpcp-license-type"></span>
                                                                    </div>
                                                                    <div class="group flex items-center space">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                                                                        </svg>
                                                                        <span class="wpcp-license-support"></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <?php
          if ($manage_per_site) {
              ?>
                                                    <button id="wpcp-deactivate-license-button" type="button" class="wpcp-button-icon-only" title="<?php echo esc_html_e('Deactivate License', 'wpcloudplugins'); ?>">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="-h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                                        </svg>
                                                    </button>
                                                    <?php
          } ?>
                                                </div>
                                            </div>
                                            <div class="wpcp-license-error hidden">
                                                <div class="bg-red-50 border-red-400  border-l-4 p-4 mt-4">
                                                    <div class="flex">
                                                        <div class="flex-shrink-0">
                                                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                            </svg>
                                                        </div>
                                                        <div class="ml-3">
                                                            <div>
                                                                <h3 class="text-sm font-medium text-red-800"><?php echo esc_html_e('Support Expired', 'wpcloudplugins'); ?></h3>
                                                                <div class="mt-2 text-sm text-red-700">
                                                                    <p class="wpcp-license-error-message"></p>
                                                                </div>
                                                                <div class="mt-4">
                                                                    <div class="-mx-2 -my-1.5 flex">
                                                                        <a href="https://1.envato.market/M4B53" class="relative inline-flex items-center bg-red-50 px-2 py-1.5 rounded-md text-sm font-medium text-red-800 border border-solid border-red-800 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-red-50 focus:ring-red-600" target="_blank">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-3 h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                                                                <path d="M9 4.5a.75.75 0 01.721.544l.813 2.846a3.75 3.75 0 002.576 2.576l2.846.813a.75.75 0 010 1.442l-2.846.813a3.75 3.75 0 00-2.576 2.576l-.813 2.846a.75.75 0 01-1.442 0l-.813-2.846a3.75 3.75 0 00-2.576-2.576l-2.846-.813a.75.75 0 010-1.442l2.846-.813A3.75 3.75 0 007.466 7.89l.813-2.846A.75.75 0 019 4.5zM18 1.5a.75.75 0 01.728.568l.258 1.036c.236.94.97 1.674 1.91 1.91l1.036.258a.75.75 0 010 1.456l-1.036.258c-.94.236-1.674.97-1.91 1.91l-.258 1.036a.75.75 0 01-1.456 0l-.258-1.036a2.625 2.625 0 00-1.91-1.91l-1.036-.258a.75.75 0 010-1.456l1.036-.258a2.625 2.625 0 001.91-1.91l.258-1.036A.75.75 0 0118 1.5zM16.5 15a.75.75 0 01.712.513l.394 1.183c.15.447.5.799.948.948l1.183.395a.75.75 0 010 1.422l-1.183.395c-.447.15-.799.5-.948.948l-.395 1.183a.75.75 0 01-1.422 0l-.395-1.183a1.5 1.5 0 00-.948-.948l-1.183-.395a.75.75 0 010-1.422l1.183-.395c.447-.15.799-.5.948-.948l.395-1.183A.75.75 0 0116.5 15z" />
                                                                            </svg>
                                                                            <?php esc_html_e('Renew now!', 'wpcloudplugins'); ?>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                                <?php
}
?>

                                <div class="px-4 py-5 sm:px-6">
                                    <div class="flex flex-col space-y-2">
                                        <img src="<?php echo LETSBOX_ROOTPATH; ?>/css/images/envato-market.svg" width="200">
                                        <a href="https://1.envato.market/a4ggZ" target="_blank" class="wpcp-link-primary italic ">Envato Market is the only official distributor of the WP Cloud Plugins.</a>
                                    </div>
                                </div>

                            </div>
                            <!-- End License Block -->
                        </div>
                        <!-- End Dashboard Panel -->

                        <!-- Layout Panel -->
                        <div data-nav-panel="wpcp-layout" class="hidden space-y-6">
<?php

AdminLayout::render_open_panel(['title' => esc_html__('General', 'wpcloudplugins'), 'accordion' => true]);

AdminLayout::render_simple_number([
    'title' => esc_html__('Border radius', 'wpcloudplugins'),
    'description' => esc_html__('The roundness (px) of various plugin elements, such as file tiles, modal dialogs and buttons.', 'wpcloudplugins'),
    'placeholder' => '10',
    'default' => '',
    'min' => 0,
    'max' => 30,
    'key' => 'layout_border_radius',
]);

AdminLayout::render_simple_number([
    'title' => esc_html__('Grid gap', 'wpcloudplugins'),
    'description' => esc_html__('The gap (px) between rows and columns of various plugin elements, such as the File Browser grid.', 'wpcloudplugins'),
    'placeholder' => '10',
    'default' => '',
    'min' => 0,
    'max' => 30,
    'key' => 'layout_gap',
]);

AdminLayout::render_close_panel();


AdminLayout::render_open_panel(['title' => esc_html__('Color Palette', 'wpcloudplugins'), 'accordion' => true]);

// Select Theme Style
AdminLayout::render_simple_select([
    'title' => esc_html__('Theme Style', 'wpcloudplugins'),
    'description' => '',
    'type' => 'ddslickbox',
    'options' => [
        'dark' => ['title' => esc_html__('Dark', 'wpcloudplugins'), 'imagesrc' => LETSBOX_ROOTPATH.'/css/images/skin-dark.png'],
        'light' => ['title' => esc_html__('Light', 'wpcloudplugins'), 'imagesrc' => LETSBOX_ROOTPATH.'/css/images/skin-light.png'],
    ],
    'key' => 'colors[style]',
    'default' => 'light',
]);

// Color Palette
$colors = [
    'accent' => [
        'label' => esc_html__('Accent Color', 'wpcloudplugins'),
        'default' => '#590e54',
        'alpha' => false,
    ],
    'black' => [
        'label' => esc_html__('Black', 'wpcloudplugins'),
        'default' => '#222',
    ],
    'dark1' => [
        'label' => esc_html__('Dark 1', 'wpcloudplugins'),
        'default' => '#666666',
    ],
    'dark2' => [
        'label' => esc_html__('Dark 2', 'wpcloudplugins'),
        'default' => '#999999',
    ],
    'background-dark' => [
        'label' => esc_html__('Background color for dark theme', 'wpcloudplugins'),
        'default' => '#333333',
    ],
    'white' => [
        'label' => esc_html__('White', 'wpcloudplugins'),
        'default' => '#fff',
    ],
    'light1' => [
        'label' => esc_html__('Light 1', 'wpcloudplugins'),
        'default' => '#fcfcfc',
    ],
    'light2' => [
        'label' => esc_html__('Light 2', 'wpcloudplugins'),
        'default' => '#e8e8e8',
    ],
    'background' => [
        'label' => esc_html__('Background color for light theme', 'wpcloudplugins'),
        'default' => '#f2f2f2',
    ],
];

AdminLayout::render_color_selectors($colors);

AdminLayout::render_close_panel();

AdminLayout::render_open_panel(['title' => esc_html__('Loading Spinner & Images', 'wpcloudplugins'), 'accordion' => true]);

// Select Loader Spinner
AdminLayout::render_simple_select([
    'title' => esc_html__('Select Loader Spinner', 'wpcloudplugins'),
    'description' => '',
    'options' => [
        'beat' => ['title' => esc_html__('Beat', 'wpcloudplugins')],
        'spinner' => ['title' => esc_html__('Spinner', 'wpcloudplugins')],
        'custom' => ['title' => esc_html__('Custom Image (selected below)', 'wpcloudplugins')],
    ],
    'key' => 'loaders[style]',
    'default' => 'beat',
]);

AdminLayout::render_image_selector([
    'title' => esc_html__('General Loader', 'wpcloudplugins'),
    'description' => esc_html__('Loading image used in the File Browser and Gallery module', 'wpcloudplugins'),
    'key' => 'loaders[loading]',
    'default' => LETSBOX_ROOTPATH.'/css/images/wpcp-loader.svg',
]);

AdminLayout::render_image_selector([
    'title' => esc_html__('No Results', 'wpcloudplugins'),
    'description' => esc_html__('Image shown in the File Browser and Gallery module when no content is found in the opened folder.', 'wpcloudplugins'),
    'key' => 'loaders[no_results]',
    'default' => LETSBOX_ROOTPATH.'/css/images/loader_no_results.svg',
]);

AdminLayout::render_image_selector([
    'title' => esc_html__('Access Forbidden', 'wpcloudplugins'),
    'description' => esc_html__('Image shown when a module is not accessible for the user.', 'wpcloudplugins'),
    'key' => 'loaders[protected]',
    'default' => LETSBOX_ROOTPATH.'/css/images/loader_protected.svg',
]);

AdminLayout::render_image_selector([
    'title' => esc_html__('iFrame Loader', 'wpcloudplugins'),
    'description' => esc_html__('Loading image used for previews and iFrams.', 'wpcloudplugins'),
    'key' => 'loaders[iframe]',
    'default' => LETSBOX_ROOTPATH.'/css/images/wpcp-loader.svg',
]);

AdminLayout::render_close_panel();

AdminLayout::render_open_panel(['title' => esc_html__('Icon Set', 'wpcloudplugins'), 'accordion' => true]);

// Icon Set
AdminLayout::render_simple_textbox([
    'title' => esc_html__('File Browser Icon Set', 'wpcloudplugins'),
    'description' => wp_kses(sprintf('Location to the icon set you want to use for items without thumbnail. When you want to use your own set, just make a copy of the default icon set folder (<code>%s</code>) and place it in the <code>wp-content/</code> folder', LETSBOX_ROOTPATH.'/css/icons/'), 'wpcloudplugins'),
    'placeholder' => LETSBOX_ROOTPATH.'/css/icons/',
    'notice' => esc_html__('Modifications to the default icons set will be lost during an update.', 'wpcloudplugins'),
    'notice_class' => 'warning',
    'default' => '',
    'key' => 'icon_set',
]);

AdminLayout::render_close_panel();

AdminLayout::render_open_panel(['title' => esc_html__('LightBox', 'wpcloudplugins'), 'accordion' => true]);

// LightBox Skin
$skin_options = [];
foreach (new \DirectoryIterator(LETSBOX_ROOTDIR.'/vendors/iLightBox/') as $fileInfo) {
    if ($fileInfo->isDir() && !$fileInfo->isDot() && (false !== strpos($fileInfo->getFilename(), 'skin'))) {
        if (file_exists(LETSBOX_ROOTDIR.'/vendors/iLightBox/'.$fileInfo->getFilename().'/skin.css')) {
            $selected = '';
            $skinname = str_replace('-skin', '', $fileInfo->getFilename());
            $icon = file_exists(LETSBOX_ROOTDIR.'/vendors/iLightBox/'.$fileInfo->getFilename().'/thumb.jpg') ? LETSBOX_ROOTPATH.'/vendors/iLightBox/'.$fileInfo->getFilename().'/thumb.jpg' : '';

            $skin_options[$skinname] = [
                'title' => ucwords(str_replace(['_','-'], ' ',$fileInfo->getFilename())),
                'imagesrc' => $icon,
            ];
        }
    }
}

AdminLayout::render_simple_select([
    'title' => esc_html__('LightBox Skin', 'wpcloudplugins'),
    'description' => esc_html__('Select which skin you want to use for the Inline Preview.', 'wpcloudplugins'),
    'type' => 'ddslickbox',
    'options' => $skin_options,
    'key' => 'lightbox_skin',
    'default' => 'metro-black',
]);

// LightBox Lightbox Scroll
AdminLayout::render_simple_select([
    'title' => esc_html__('Lightbox Scroll', 'wpcloudplugins'),
    'description' => esc_html__("Sets path for switching windows. Possible values are 'vertical' and 'horizontal' and the default is 'vertical'.", 'wpcloudplugins'),
    'options' => [
        'horizontal' => ['title' => esc_html__('Horizontal', 'wpcloudplugins')],
        'vertical' => ['title' => esc_html__('Vertical', 'wpcloudplugins')],
    ],
    'key' => 'lightbox_path',
    'default' => 'horizontal',
]);

// LightBox Image Source
AdminLayout::render_simple_select([
    'title' => esc_html__('Image Source', 'wpcloudplugins'),
    'description' => esc_html__('Select the source of the images. Large thumbnails load fast, orignal files will take some time to load.', 'wpcloudplugins'),
    'options' => [
        'googlethumbnail' => ['title' => esc_html__('Fast - Large preview thumbnails.', 'wpcloudplugins')],
        'original' => ['title' => esc_html__('Slow - Show original files.', 'wpcloudplugins')],
    ],
    'key' => 'loadimages',
    'default' => 'googlethumbnail',
]);

// Show Thumbnails
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Show Thumbnails', 'wpcloudplugins'),
    'description' => esc_html__('Show thumbnails of the files inside the Lightbox.', 'wpcloudplugins'),
    'key' => 'lightbox_thumbnails',
    'default' => true,
]);

// Allow Mouse Click
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Allow Mouse Click on Image', 'wpcloudplugins'),
    'description' => esc_html__('Should people be able to access the right click context menu to e.g. save the image?', 'wpcloudplugins'),
    'key' => 'lightbox_rightclick',
    'default' => false,
]);

// LightBox Header
AdminLayout::render_simple_select([
    'title' => esc_html__('Header', 'wpcloudplugins'),
    'description' => esc_html__('When should the header containing title and action-menu be shown?', 'wpcloudplugins'),
    'options' => [
        'true' => ['title' => esc_html__('Always.', 'wpcloudplugins')],
        'click' => ['title' => esc_html__('Show after clicking on the Lightbox.', 'wpcloudplugins')],
        'mouseenter' => ['title' => esc_html__('Show when hovering over the Lightbox.', 'wpcloudplugins')],
        'false' => ['title' => esc_html__('Never.', 'wpcloudplugins')],
    ],
    'key' => 'lightbox_showheader',
    'default' => 'true',
]);

// LightBox Caption/Description
AdminLayout::render_simple_select([
    'title' => esc_html__('Caption / Description', 'wpcloudplugins'),
    'description' => esc_html__('When should the description be shown in the Gallery Lightbox?', 'wpcloudplugins'),
    'options' => [
        'true' => ['title' => esc_html__('Always.', 'wpcloudplugins')],
        'click' => ['title' => esc_html__('Show after clicking on the Lightbox.', 'wpcloudplugins')],
        'mouseenter' => ['title' => esc_html__('Show when hovering over the Lightbox.', 'wpcloudplugins')],
        'false' => ['title' => esc_html__('Never.', 'wpcloudplugins')],
    ],
    'key' => 'lightbox_showcaption',
    'default' => 'true',
]);

AdminLayout::render_close_panel();

AdminLayout::render_open_panel(['title' => esc_html__('Media Player', 'wpcloudplugins'), 'accordion' => true]);

// MediaPlayer Skin
$skin_options = [];
foreach (new \DirectoryIterator(LETSBOX_ROOTDIR.'/skins/') as $fileInfo) {
    if ($fileInfo->isDir() && !$fileInfo->isDot()) {
        if (file_exists(LETSBOX_ROOTDIR.'/skins/'.$fileInfo->getFilename().'/js/Player.js')) {
            $selected = '';
            $skinname = str_replace('-skin', '', $fileInfo->getFilename());
            $icon = file_exists(LETSBOX_ROOTDIR.'/skins/'.$fileInfo->getFilename().'/Thumb.jpg') ? LETSBOX_ROOTPATH.'/skins/'.$fileInfo->getFilename().'/Thumb.jpg' : '';

            $skin_options[$skinname] = [
                'title' => ucwords(str_replace(['_','-'], ' ',$fileInfo->getFilename())),
                'imagesrc' => $icon,
            ];
        }
    }
}

AdminLayout::render_simple_select([
    'title' => esc_html__('Media Player Skin', 'wpcloudplugins'),
    'description' => esc_html__('Select which Media Player skin you want to use by default.', 'wpcloudplugins'),
    'type' => 'ddslickbox',
    'options' => $skin_options,
    'key' => 'mediaplayer_skin',
    'default' => 'Default_Skin',
]);

// Load Native MediaElement.js
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Load native MediaElement.js library', 'wpcloudplugins'),
    'description' => esc_html__('Is the layout of the Media Player all mixed up and is it not initiating properly? If that is the case, you might be encountering a conflict between media player libraries on your site. To resolve this, enable this setting to load the native MediaElement.js library.', 'wpcloudplugins'),
    'key' => 'mediaplayer_load_native_mediaelement',
    'default' => false,
]);

AdminLayout::render_close_panel();

AdminLayout::render_open_panel(['title' => esc_html__('Custom CSS', 'wpcloudplugins'), 'accordion' => true]);

// Custom CSS
AdminLayout::render_simple_textarea([
    'title' => esc_html__('Custom CSS', 'wpcloudplugins'),
    'description' => esc_html__("If you want to modify the looks of the plugin slightly, you can insert here your custom CSS. Don't edit the CSS files itself, because those modifications will be lost during an update.", 'wpcloudplugins'),
    'placeholder' => '',
    'default' => '',
    'rows' => 10,
    'key' => 'custom_css',
]);

AdminLayout::render_close_panel();

?>
                        </div>
                        <!-- End Layout Panel -->

                        <!-- Private Folders Panel -->
                        <div data-nav-panel="wpcp-private-folders" class="hidden space-y-6">
                            <?php AdminLayout::render_open_panel(['title' => esc_html__('Global settings Automatically linked Private Folders', 'wpcloudplugins'), 'accordion' => true]);

AdminLayout::render_notice(esc_html__('The following settings are only used for all shortcodes with automatically linked Private Folders,  unless specified otherwise in the shortcode configuration.', 'wpcloudplugins'), 'warning');

// Update Private Folders
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Create Private Folders on registration', 'wpcloudplugins'),
    'description' => esc_html__('Automatically create the Private Folders for an user after their registration on the site.', 'wpcloudplugins'),
    'key' => 'userfolder_oncreation',
    'default' => true,
]);

// Update Private Folders
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Create all Private Folders the 1st time a module is used', 'wpcloudplugins'),
    'description' => esc_html__('Immediately create all the Private Folders during the first rendering of a module that has the Private Folders feature enabled.', 'wpcloudplugins'),
    'key' => 'userfolder_onfirstvisit',
    'default' => false,
    'notice_class' => 'warning',
    'notice' => esc_html__("Creating a Private Folder takes around 1 sec/folder. So it isn't recommended to enable this feature when you have tons of users.", 'wpcloudplugins'),
]);

// Update Private Folders
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Update Private Folders name after profile update', 'wpcloudplugins'),
    'description' => esc_html__('If needed, update the name of the Private Folder for an user after they have updated their profile.', 'wpcloudplugins'),
    'key' => 'userfolder_update',
    'default' => false,
]);

// Delete Private Folders
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Delete Private Folders after deleting WP User', 'wpcloudplugins'),
    'description' => esc_html__('Try to remove the users Private Folders after their account is deleted.', 'wpcloudplugins'),
    'key' => 'userfolder_remove',
    'default' => false,
]);

// Private Folders Name Template
AdminLayout::render_simple_textbox([
    'title' => esc_html__('Name Template', 'wpcloudplugins'),
    'description' => esc_html__('Template name for automatically created Private Folders.', 'wpcloudplugins').' '.esc_html__('The naming template can also be set per shortcode individually.', 'wpcloudplugins'),
    'placeholder' => '',
    'default' => '',
    'key' => 'userfolder_name',
    'notice_class' => 'info',
    'notice' => sprintf(esc_html__('Available placeholders: %s', 'wpcloudplugins'), '').'<code>%user_login%</code>,  <code>%user_firstname%</code>, <code>%user_lastname%</code>, <code>%user_email%</code>, <code>%display_name%</code>, <code>%ID%</code>, <code>%user_role%</code>, <code>%usermeta_{key}%</code>, <code>%post_id%</code>, <code>%post_title%</code>, <code>%postmeta_{key}%</code>, <code>%date_{date_format}%</code>, <code>%yyyy-mm-dd%</code>, <code>%hh:mm%</code>, <code>%uniqueID%</code>, <code>%directory_separator% (/)</code>',
]);

// Private Folders Name Template for Guests
AdminLayout::render_simple_textbox([
    'title' => esc_html__('Name Template Prefix for anonymous users', 'wpcloudplugins'),
    'description' => sprintf(esc_html__('As anonymous users will not have user metadata that can be used in the folder name template, the plugin will generate a unique user metadata object for those users instead. By default, their folder name will be prefixed with "%s" so all their folders are grouped together. You can change that prefix here.', 'wpcloudplugins'), esc_html__('Guests', 'wpcloudplugins').' - '),
    'placeholder' => esc_html__('Guests', 'wpcloudplugins').' - ',
    'default' => esc_html__('Guests', 'wpcloudplugins').' - ',
    'key' => 'userfolder_name_guest_prefix',
]);
AdminLayout::render_close_panel();

AdminLayout::render_open_panel(['title' => esc_html__('Global settings Manually linked Private Folders', 'wpcloudplugins'), 'accordion' => true]);

// Access Forbidden notice
AdminLayout::render_wpeditor(
    [
        'title' => esc_html__('"Access Forbidden" Notice', 'wpcloudplugins'),
        'description' => esc_html__("Message that is displayed when an user is visiting a module with the Private Folders feature set to 'Manual' mode while it doesn't have Private Folder linked to its account.", 'wpcloudplugins'),
        'placeholder' => '',
        'notice' => sprintf(esc_html__('You can manually link users to their Private Folder via the %s[Link Private Folders]%s menu page.', 'wpcloudplugins'), '<a href="'.admin_url('admin.php?page=LetsBox_settings_linkusers').'" target="_blank" class="wpcp-link-primary">', '</a>'),
        'notice_class' => 'info',
        'key' => 'userfolder_noaccess',
        'wpeditor' => [
            'teeny' => true,
            'tinymce' => false,
            'textarea_rows' => 12,
            'media_buttons' => false,
        ],
    ]
);

AdminLayout::render_close_panel();

$main_account = Accounts::instance()->get_primary_account();

if (!empty($main_account)) {
    AdminLayout::render_open_panel(['title' => esc_html__('Private Folders in WP Admin Dashboard', 'wpcloudplugins'), 'accordion' => true]);

    // Admin Private Folders
    AdminLayout::render_simple_select([
        'title' => esc_html__('Use Private Folders', 'wpcloudplugins'),
        'description' => esc_html__('Enable Private Folders in the Shortcode Builder and Back-End File Browser.', 'wpcloudplugins'),
        'options' => [
            'No' => ['title' => esc_html__('No', 'wpcloudplugins'), 'toggle_container' => ''],
            'manual' => ['title' => esc_html__('Yes, I link the users manually', 'wpcloudplugins'), 'toggle_container' => ''],
            'auto' => ['title' => esc_html__('Yes, let the plugin create the User Folders for me.', 'wpcloudplugins'), 'toggle_container' => '#toggle-private-folders-backend'],
        ],
        'key' => 'userfolder_backend',
        'default' => 'No',
        'notice' => esc_html__('This setting only restrict access of the File Browsers in the Admin Dashboard (e.g. the ones in the Shortcode Builder and the File Browser menu). To enable Private Folders for your own Shortcodes, use the Shortcode Builder', 'wpcloudplugins'),
        'notice_class' => 'warning',
    ]);

    AdminLayout::render_open_toggle_container(['key' => 'toggle-private-folders-backend']);

    // Root folder for Private Folders
    $folder_data = Core::get_setting('userfolder_backend_auto_root');

    $main_account = Accounts::instance()->get_primary_account();

    if ($main_account->get_authorization()->is_valid()) {
        if (empty($folder_data) || empty($folder_data['id'])) {
            App::set_current_account($main_account);

            try {
                $root = API::get_root_folder();
            } catch (\Exception $ex) {
                $root = false;
            }

            if (false === $root) {
                $folder_data = [
                    'account' => $main_account->get_id(),
                    'id' => '',
                    'name' => '',
                    'view_roles' => ['administrator'],
                ];
            } else {
                $folder_data = [
                    'account' => $main_account->get_id(),
                    'id' => $root->get_entry()->get_id(),
                    'name' => $root->get_entry()->get_name(),
                    'view_roles' => ['administrator'],
                ];
            }
        }

        $use_automatic_private_folders = Core::get_setting('userfolder_backend');

        $shortcode_attr = ['singleaccount' => '0'];

        if ('auto' === $use_automatic_private_folders) {
            $shortcode_attr = [
                'startaccount' => $folder_data['account'],
                'startid' => $folder_data['id'],
            ];
        }

        AdminLayout::render_folder_selectbox([
            'title' => esc_html__('Location Private Folders', 'wpcloudplugins'),
            'description' => esc_html__('Select in which folder the Private Folders should be created.', 'wpcloudplugins').' '.esc_html__('Current selected folder', 'wpcloudplugins'),
            'key' => 'userfolder_backend_auto_root',
            'shortcode_attr' => $shortcode_attr,
            'apply_backend_private_folder' => false,
            'inline' => false,
        ]);
    }

    // Full Access
    AdminLayout::render_user_selectbox([
        'title' => esc_html__('Full Access', 'wpcloudplugins'),
        'description' => esc_html__('By default only Administrator users will be able to navigate through all Private Folders. Add other user roles if they should be able to see all Private Folders as well.', 'wpcloudplugins'),
        'key' => 'userfolder_backend_auto_root[view_roles]',
        'default' => [],
    ]);

    AdminLayout::render_close_toggle_container();

    AdminLayout::render_close_panel();
}

?>
                        </div>
                        <!-- End Private Folders Panel -->


                        <!-- Advanced Panel -->
                        <div data-nav-panel="wpcp-advanced" class="hidden space-y-6">

                            <?php AdminLayout::render_open_panel([
                                'title' => esc_html__('API Application', 'wpcloudplugins'), 'accordion' => true,
                            ]);

AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Use Custom App', 'wpcloudplugins'),
    'description' => esc_html__('For an easy configuration you can just use the default App of the plugin itself.', 'wpcloudplugins'),
    'key' => 'box_app_own',
    'default' => false,
    'toggle_container' => '#toggle-custom-app-options',
]);

AdminLayout::render_open_toggle_container(['key' => 'toggle-custom-app-options']);

AdminLayout::render_simple_textbox([
    'title' => esc_html__('Client ID', 'wpcloudplugins'),
    'description' => esc_html__('Only if you want to use your own App, insert your Client ID here', 'wpcloudplugins'),
    'placeholder' => '<--- '.esc_html__('Leave empty for easy setup', 'wpcloudplugins').' --->',
    'default' => '',
    'key' => 'box_app_client_id',
]);

AdminLayout::render_simple_textbox([
    'title' => esc_html__('Client Secret', 'wpcloudplugins'),
    'description' => esc_html__('If you want to use your own App, insert your Client Secret here', 'wpcloudplugins'),
    'placeholder' => '<--- '.esc_html__('Leave empty for easy setup', 'wpcloudplugins').' --->',
    'default' => '',
    'key' => 'box_app_client_secret',
]);

AdminLayout::render_notice(esc_html__('Set the OAuth 2.0 Redirect URI in your application to the following uri:', 'wpcloudplugins').'<br/><code>'.App::instance()->get_auth_uri().'</code>', 'info');

AdminLayout::render_notice(esc_html__('We do not collect and do not have access to your personal data. See our privacy statement for more information.', 'wpcloudplugins'), 'info');

AdminLayout::render_close_toggle_container();

AdminLayout::render_notice('<strong>Using your own Box App is <u>optional</u> and <u>not recommended</u></strong>. The advantage of using your own app is limited. If you decided to create your own Box App anyway, please enter your settings. In the <a href="https://florisdeleeuwnl.zendesk.com/hc/en-us/articles/201804806--How-do-I-create-my-own-Box-App-" target="_blank" class="wpcp-link-primary">documentation</a> you can find how you can create a Box App.', 'warning');

AdminLayout::render_notice(esc_html__('If you encounter any issues when trying to use your own App, please fall back on the default App by disabling this setting.', 'wpcloudplugins'), 'warning');

AdminLayout::render_close_panel();

AdminLayout::render_open_panel(['title' => esc_html__('Box Account Settings', 'wpcloudplugins'), 'accordion' => true]);

// Scope shared-links
AdminLayout::render_simple_select([
    'title' => esc_html__('Business Accounts', 'wpcloudplugins').' | '.esc_html__('Scope shared-links', 'wpcloudplugins'),
    'description' => esc_html__('Who should be able to access the links that are created by the plugin? If set to Public the links will be accessible by anyone. Within Organization will make links accessible within your organization only. Anonymous links may be disabled by the tenant administrator', 'wpcloudplugins'),
    'options' => [
        'open' => ['title' => esc_html__('Public', 'wpcloudplugins')],
        'company' => ['title' => esc_html__('Within Organization', 'wpcloudplugins')],
    ],
    'key' => 'link_scope',
    'default' => 'open',
]);

AdminLayout::render_close_panel();
?>


                            <?php AdminLayout::render_open_panel(['title' => esc_html__('Advanced', 'wpcloudplugins'), 'accordion' => true]);

// Remember last position
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Remember last opened location', 'wpcloudplugins'),
    'description' => esc_html__('When opening a page with a previously visited File Browser module, the last opened folder location will be loaded. If you disable this setting, the plugin will always load the top folder.', 'wpcloudplugins'),
    'key' => 'remember_last_location',
    'default' => true,
]);

// Load Javascripts on all pages
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Load Javascripts on all pages', 'wpcloudplugins'),
    'description' => esc_html__('By default the plugin will only load it scripts when the shortcode is present on the page. If you are dynamically loading content via AJAX calls and the plugin does not show up, please enable this setting.', 'wpcloudplugins'),
    'key' => 'always_load_scripts',
    'default' => false,
]);

// Enable Gzip compression
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Enable Gzip compression', 'wpcloudplugins'),
    'description' => esc_html__("Enables gzip-compression if the visitor's browser can handle it. This will increase the performance of the plugin if you are displaying large amounts of files and it reduces bandwidth usage as well. It uses the PHP ob_gzhandler() callback. Please use this setting with caution. Always test if the plugin still works on the Front-End as some servers are already configured to gzip content!", 'wpcloudplugins'),
    'key' => 'gzipcompression',
    'default' => false,
]);

// Nonce Validation
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Nonce Validation', 'wpcloudplugins'),
    'description' => esc_html__('The plugin uses, among others, the WordPress Nonce system to protect you against several types of attacks including CSRF. Disable this in case you are encountering conflicts with plugins that alters this system.', 'wpcloudplugins'),
    'notice' => esc_html__('Please use this setting with caution! Only disable it when really necessary.', 'wpcloudplugins'),
    'notice_class' => 'warning',
    'key' => 'nonce_validation',
    'default' => true,
]);

// Delete settings on Uninstall
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Delete settings on Uninstall', 'wpcloudplugins'),
    'description' => esc_html__('When you uninstall the plugin, what do you want to do with your settings? You can save them for next time, or wipe them back to factory settings.', 'wpcloudplugins'),
    'notice' => esc_html__('When you reset the settings, the plugin will not longer be linked to your accounts, but their authorization will not be revoked', 'wpcloudplugins').'. '.esc_html__('You can revoke the authorization via the Dasbhoard tab.', 'wpcloudplugins'),
    'notice_class' => 'warning',
    'key' => 'uninstall_reset',
    'default' => true,
]);

AdminLayout::render_close_panel();

?>
                        </div>
                        <!-- End Advanced Panel -->

                        <!-- Integrations Panel -->
                        <div data-nav-panel="wpcp-integrations" class="hidden space-y-6">

                            <?php
  // Social Sharing Buttons
  AdminLayout::render_open_panel([
      'title' => esc_html__('Social Sharing Buttons', 'wpcloudplugins'),
      'description' => esc_html__('Select which sharing buttons should be accessible via the sharing dialogs of the plugin.', 'wpcloudplugins'), 'accordion' => true,
  ]);

AdminLayout::render_share_buttons();

AdminLayout::render_close_panel();

// URL Shortener
AdminLayout::render_open_panel(['title' => 'URL Shortener', 'accordion' => true]);

AdminLayout::render_simple_select([
    'title' => esc_html__('Shortlinks API', 'wpcloudplugins'),
    'description' => esc_html__('Select which Url Shortener Service you want to use for shared links.', 'wpcloudplugins'),
    'options' => [
        'None' => ['title' => esc_html__('None', 'wpcloudplugins'), 'toggle_container' => ''],
        'Tinyurl' => ['title' => 'TinyURL', 'toggle_container' => '#toggle-tinyurl-options'],        
        'Shorte.st' => ['title' => 'Shorte.st', 'toggle_container' => '#toggle-shortest-options'],
        'Rebrandly' => ['title' => 'Rebrandly', 'toggle_container' => '#toggle-rebrandly-options'],
        'Bit.ly' => ['title' => 'Bit.ly', 'toggle_container' => '#toggle-bitly-options'],
    ],
    'key' => 'shortlinks',
    'default' => 'None',
]);

AdminLayout::render_open_toggle_container(['key' => 'toggle-tinyurl-options']);

// TinyURL Options
AdminLayout::render_simple_textbox([
    'title' => 'API token',
    'description' => sprintf(esc_html__('Sign up for %s and %s get your API token%s.', 'wpcloudplugins'), 'TinyURL', "<a href='https://tinyurl.com/app/settings/api' target='_blank' class='wpcp-link-primary'>", '</a>'),
    'placeholder' => '',
    'default' => '',
    'key' => 'tinyurl_apikey',
]);

AdminLayout::render_simple_textbox([
    'title' => ' Domain (optional)',
    'description' => esc_html__('Enter your custom branded domain you want to use.', 'wpcloudplugins'),
    'placeholder' => '',
    'default' => '',
    'key' => 'tinyurl_domain',
]);

AdminLayout::render_close_toggle_container();


AdminLayout::render_open_toggle_container(['key' => 'toggle-shortest-options']);

// Shorte.st Options
AdminLayout::render_simple_textbox([
    'title' => 'API token',
    'description' => sprintf(esc_html__('Sign up for %s and %s get your API token%s.', 'wpcloudplugins'), 'Shorte.st', "<a href='https://shorte.st/tools/api' target='_blank' class='wpcp-link-primary'>", '</a>'),
    'placeholder' => '',
    'default' => '',
    'key' => 'shortest_apikey',
]);

AdminLayout::render_close_toggle_container();

AdminLayout::render_open_toggle_container(['key' => 'toggle-rebrandly-options']);

// Rebrandly Options
AdminLayout::render_simple_textbox([
    'title' => 'API key',
    'description' => sprintf(esc_html__('Sign up for %s and %s get your API token%s.', 'wpcloudplugins'), 'Rebrandly', "<a href='https://app.rebrandly.com/account/api-keys' target='_blank' class='wpcp-link-primary'>", '</a>'),
    'placeholder' => '',
    'default' => '',
    'key' => 'rebrandly_apikey',
]);

AdminLayout::render_simple_textbox([
    'title' => 'Rebrandly Domain (optional)',
    'description' => esc_html__('Enter your custom branded domain you want to use.', 'wpcloudplugins'),
    'placeholder' => '',
    'default' => '',
    'key' => 'rebrandly_domain',
]);

AdminLayout::render_simple_textbox([
    'title' => 'Rebrandly WorkSpace ID (optional)',
    'description' => esc_html__('Add your WorkSpace ID if you want to use URL shortener to interact with your account in the context of your Rebrandly Workspace.', 'wpcloudplugins'),
    'placeholder' => '',
    'default' => '',
    'key' => 'rebrandly_workspace',
]);

AdminLayout::render_close_toggle_container();

AdminLayout::render_open_toggle_container(['key' => 'toggle-bitly-options']);

// Bit.ly API token
AdminLayout::render_simple_textbox([
    'title' => 'Bitly Login',
    'description' => esc_html__('Your Bitly user name.', 'wpcloudplugins'),
    'placeholder' => '',
    'default' => '',
    'key' => 'bitly_login',
]);

AdminLayout::render_simple_textbox([
    'title' => 'Bitly Access Token',
    'description' => sprintf(esc_html__('Sign up for %s and %s get your Access Token%s.', 'wpcloudplugins'), 'Bitly', "<a href='http://bit".''."ly.com/a/your_api_key' target='_blank' class='wpcp-link-primary'>", '</a>'),
    'placeholder' => '',
    'default' => '',
    'key' => 'bitly_apikey',
]);

AdminLayout::render_close_toggle_container();

AdminLayout::render_close_panel();

// ReCaptcha
AdminLayout::render_open_panel([
    'title' => 'ReCaptcha V3',
    'description' => sprintf(esc_html__('reCAPTCHA protects you against spam and other types of automated abuse. With this reCAPTCHA (V3) integration module, you can block abusive downloads of your files by bots. Create your own credentials via your %s.', 'wpcloudplugins'), "<a href='https://www.google.com/recaptcha/admin' target='_blank' class='wpcp-link-primary'>reCaptcha Dashboard</a>"),
    'accordion' => true,
]);

// ReCaptcha Site Key
AdminLayout::render_simple_textbox([
    'title' => 'Site Key',
    'description' => esc_html__('The site key is used to invoke reCAPTCHA service on your site or mobile application.', 'wpcloudplugins'),
    'placeholder' => '',
    'default' => '',
    'key' => 'recaptcha_sitekey',
]);

// ReCaptcha Secret Key
AdminLayout::render_simple_textbox([
    'title' => 'Secret Key',
    'description' => esc_html__('The secret key authorizes communication between your application backend and the reCAPTCHA server to verify the user.', 'wpcloudplugins'),
    'placeholder' => '',
    'default' => '',
    'key' => 'recaptcha_secret',
]);

AdminLayout::render_close_panel();

// Video Ads
AdminLayout::render_open_panel([
    'title' => esc_html__('Video Advertisements (IMA/VAST)', 'wpcloudplugins'),
    'description' => esc_html__('The mediaplayer of the plugin supports VAST XML advertisments to offer monetization options for your videos. You can enable advertisments for the complete site and per Media Player shortcode. Currently, this plugin only supports Linear elements with MP4', 'wpcloudplugins'),
    'accordion' => true,
]);

// VAST XML Tag Url
AdminLayout::render_simple_textbox([
    'title' => 'VAST XML Tag Url',
    'description' => esc_html__('Enter your VAST XML tag url.', 'wpcloudplugins'),
    'placeholder' => esc_html__('Leave empty to disable Ads', 'wpcloudplugins'),
    'default' => '',
    'notice' => esc_html__('If you are unable to see the example VAST url below, please make sure you do not have an ad blocker enabled. VAST url example:', 'wpcloudplugins').' >> [<a href="https://pubads.g.doubleclick.net/gampad/ads?sz=640x480&iu=/124319096/external/single_ad_samples&ciu_szs=300x250&impl=s&gdfp_req=1&env=vp&output=vast&unviewed_position_start=1&cust_params=deployment%3Ddevsite%26sample_ct%3Dskippablelinear&correlator=" rel="no-follow">Example</a>]',
    'notice_class' => 'info',
    'key' => 'mediaplayer_ads_tagurl',
]);

// Enable Skip Button
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Enable Skip Button', 'wpcloudplugins'),
    'description' => esc_html__('Allow user to skip advertisment after after the following amount of seconds have elapsed.', 'wpcloudplugins'),
    'key' => 'mediaplayer_ads_skipable',
    'default' => false,
    'toggle_container' => '#toggle-ads-skipable',
]);

AdminLayout::render_open_toggle_container(['key' => 'toggle-ads-skipable']);

// Skip time
AdminLayout::render_simple_textbox([
    'title' => 'Skip button visible after (seconds)',
    'description' => esc_html__('Allow user to skip advertisment after after the following amount of seconds have elapsed', 'wpcloudplugins'),
    'default' => 5,
    'key' => 'mediaplayer_ads_skipable_after',
]);

AdminLayout::render_close_toggle_container();

AdminLayout::render_close_panel();

?>
                        </div>
                        <!-- End Integrations Panel -->

                        <!-- Notifications Panel -->
                        <div data-nav-panel="wpcp-notifications" class="hidden space-y-6">

                            <?php
  // Email Sender Information
  AdminLayout::render_open_panel([
      'title' => esc_html__('Email Sender Information', 'wpcloudplugins'), 'accordion' => true,
  ]);

// Email From Name
AdminLayout::render_simple_textbox([
    'title' => esc_html__('From Name', 'wpcloudplugins'),
    'description' => esc_html__('Enter the name you would like the notification email sent from, or use one of the available placeholders.', 'wpcloudplugins'),
    'key' => 'notification_from_name',
    'default' => '',
]);

// Email From address
AdminLayout::render_simple_textbox([
    'title' => esc_html__('From email address', 'wpcloudplugins'),
    'description' => esc_html__('Enter an authorized email address you would like the notification email sent from. To avoid deliverability issues, always use your site domain in the from email.', 'wpcloudplugins'),
    'key' => 'notification_from_email',
    'default' => '',
]);

// Email Reply-to address
AdminLayout::render_simple_textbox([
    'title' => esc_html__('Reply-to address', 'wpcloudplugins'),
    'description' => esc_html__('Enter an email address when you want a reply on the notification to go to an email address that is different than the From: address.', 'wpcloudplugins'),
    'key' => 'notification_replyto_email',
    'default' => '',
]);

AdminLayout::render_close_panel();

// Lost Authorization notification
if (false === Processor::instance()->is_network_authorized()) {
    AdminLayout::render_open_panel([
        'title' => esc_html__('Lost Authorization Notification', 'wpcloudplugins'), 'accordion' => true,
    ]);

    // Email From address
    AdminLayout::render_simple_textbox([
        'title' => esc_html__('Notification recipient', 'wpcloudplugins'),
        'description' => esc_html__('If the plugin somehow loses its authorization, a notification email will be send to the following email address.', 'wpcloudplugins'),
        'key' => 'lostauthorization_notification',
        'default' => '',
    ]);

    AdminLayout::render_close_panel();
}

// Download Notifications
AdminLayout::render_open_panel([
    'title' => esc_html__('Download Notifications', 'wpcloudplugins'), 'accordion' => true,
]);

// Download Email Subject
AdminLayout::render_simple_textbox([
    'title' => esc_html__('Email Subject', 'wpcloudplugins'),
    'description' => esc_html__('Subject for this email notification.', 'wpcloudplugins'),
    'key' => 'download_template_subject',
    'default' => '',
]);

// Download ZIP Email Subject
AdminLayout::render_simple_textbox([
    'title' => esc_html__('Email Subject for ZIP downloads', 'wpcloudplugins'),
    'description' => esc_html__('Subject for this email notification.', 'wpcloudplugins'),
    'key' => 'download_template_subject_zip',
    'default' => '',
]);

AdminLayout::render_wpeditor(
    [
        'title' => esc_html__('Email Body (HTML)', 'wpcloudplugins'),
        'description' => '',
        'placeholder' => '',
        'key' => 'download_template',
        'wpeditor' => [
            'teeny' => true,
            'tinymce' => false,
            'textarea_rows' => 12,
            'media_buttons' => false,
        ],
    ]
);

AdminLayout::render_notice(
    esc_html__(
        'Available placeholders:',
        'wpcloudplugins'
    )
                .' <code>%site_name%</code>, 
                <code>%number_of_files%</code>, 
                <code>%user_name%</code>, 
                <code>%user_email%</code>, 
                <code>%user_firstname%</code>, 
                <code>%user_lastname%</code>,                 
                <code>%recipient_name%</code>, 
                <code>%recipient_email%</code>, 
                <code>%recipient_firstname%</code>, 
                <code>%recipient_lastname%</code>,  
                <code>%admin_email%</code>, 
                <code>%file_name%</code>, 
                <code>%file_size%</code>, 
                <code>%file_icon%</code>, 
                <code>%file_relative_path%</code>, 
                <code>%file_absolute_path%</code>, 
                <code>%file_cloud_shortlived_download_url%</code>, 
                <code>%file_cloud_preview_url%</code>, 
                <code>%file_cloud_shared_url%</code>, 
                <code>%file_download_url%</code>,
                <code>%folder_name%</code>,
                <code>%folder_relative_path%</code>,
                <code>%folder_absolute_path%</code>,
                <code>%folder_url%</code>,
                <code>%ip%</code>, 
                <code>%location%</code>',
    'info'
);

AdminLayout::render_close_panel();

// Upload Notifications
AdminLayout::render_open_panel([
    'title' => esc_html__('Upload Notifications', 'wpcloudplugins'), 'accordion' => true,
]);

// Upload Email Subject
AdminLayout::render_simple_textbox([
    'title' => esc_html__('Email Subject', 'wpcloudplugins'),
    'description' => esc_html__('Subject for this email notification.', 'wpcloudplugins'),
    'key' => 'upload_template_subject',
    'default' => '',
]);

AdminLayout::render_wpeditor(
    [
        'title' => esc_html__('Email Body (HTML)', 'wpcloudplugins'),
        'description' => '',
        'placeholder' => '',
        'key' => 'upload_template',
        'wpeditor' => [
            'teeny' => true,
            'tinymce' => false,
            'textarea_rows' => 12,
            'media_buttons' => false,
        ],
    ]
);

AdminLayout::render_notice(
    esc_html__(
        'Available placeholders:',
        'wpcloudplugins'
    )
                .' <code>%site_name%</code>, 
                <code>%number_of_files%</code>, 
                <code>%user_name%</code>, 
                <code>%user_email%</code>, 
                <code>%user_firstname%</code>, 
                <code>%user_lastname%</code>,                 
                <code>%recipient_name%</code>, 
                <code>%recipient_email%</code>, 
                <code>%recipient_firstname%</code>, 
                <code>%recipient_lastname%</code>,  
                <code>%admin_email%</code>, 
                <code>%file_name%</code>, 
                <code>%file_size%</code>, 
                <code>%file_icon%</code>, 
                <code>%file_relative_path%</code>, 
                <code>%file_absolute_path%</code>, 
                <code>%file_cloud_shortlived_download_url%</code>, 
                <code>%file_cloud_preview_url%</code>, 
                <code>%file_cloud_shared_url%</code>, 
                <code>%file_download_url%</code>,
                <code>%folder_name%</code>,
                <code>%folder_relative_path%</code>,
                <code>%folder_absolute_path%</code>,
                <code>%folder_url%</code>,
                <code>%ip%</code>, 
                <code>%location%</code>',
    'info'
);

AdminLayout::render_close_panel();

// Delete Notifications
AdminLayout::render_open_panel([
    'title' => esc_html__('Delete Notifications', 'wpcloudplugins'), 'accordion' => true,
]);

// Upload Email Subject
AdminLayout::render_simple_textbox([
    'title' => esc_html__('Email Subject', 'wpcloudplugins'),
    'description' => esc_html__('Subject for this email notification.', 'wpcloudplugins'),
    'key' => 'delete_template_subject',
    'default' => '',
]);

AdminLayout::render_wpeditor(
    [
        'title' => esc_html__('Email Body (HTML)', 'wpcloudplugins'),
        'description' => '',
        'placeholder' => '',
        'key' => 'delete_template',
        'wpeditor' => [
            'teeny' => true,
            'tinymce' => false,
            'textarea_rows' => 12,
            'media_buttons' => false,
        ],
    ]
);

AdminLayout::render_notice(
    esc_html__(
        'Available placeholders:',
        'wpcloudplugins'
    )
                .' <code>%site_name%</code>, 
                <code>%number_of_files%</code>, 
                <code>%user_name%</code>, 
                <code>%user_email%</code>, 
                <code>%user_firstname%</code>, 
                <code>%user_lastname%</code>,                 
                <code>%recipient_name%</code>, 
                <code>%recipient_email%</code>, 
                <code>%recipient_firstname%</code>, 
                <code>%recipient_lastname%</code>,  
                <code>%admin_email%</code>, 
                <code>%file_name%</code>, 
                <code>%file_size%</code>, 
                <code>%file_icon%</code>, 
                <code>%file_relative_path%</code>, 
                <code>%file_absolute_path%</code>, 
                <code>%file_cloud_shortlived_download_url%</code>, 
                <code>%file_cloud_preview_url%</code>, 
                <code>%file_cloud_shared_url%</code>, 
                <code>%file_download_url%</code>,
                <code>%folder_name%</code>,
                <code>%folder_relative_path%</code>,
                <code>%folder_absolute_path%</code>,
                <code>%folder_url%</code>,
                <code>%ip%</code>, 
                <code>%location%</code>',
    'info'
);

AdminLayout::render_close_panel();

// Template %filelist% Placeholder
AdminLayout::render_open_panel([
    'title' => esc_html__('File item template', 'wpcloudplugins'), 'accordion' => true,
]);

AdminLayout::render_wpeditor(
    [
        'title' => sprintf(esc_html__('Template for %s placeholder', 'wpcloudplugins'), '<code>%filelist%</code>'),
        'description' => esc_html__('Template for each file item in the filelist in the download/upload/delete notification body.', 'wpcloudplugins'),
        'placeholder' => '',
        'key' => 'filelist_template',
        'wpeditor' => [
            'teeny' => true,
            'tinymce' => false,
            'textarea_rows' => 12,
            'media_buttons' => false,
        ],
    ]
);

AdminLayout::render_notice(
    esc_html__(
        'Available placeholders:',
        'wpcloudplugins'
    )
                .' <code>%file_name%</code>, 
                <code>%file_size%</code>, 
                <code>%file_lastedited%</code>,                
                <code>%file_icon%</code>, 
                <code>%file_cloud_shortlived_download_url%</code>, 
                <code>%file_cloud_preview_url%</code>, 
                <code>%file_cloud_shared_url%</code>, 
                <code>%file_download_url%</code>,
                <code>%file_deeplink_url%</code>,                
                <code>%file_relative_path%</code>, 
                <code>%file_absolute_path%</code>, 
                <code>%folder_relative_path%</code>,
                <code>%folder_absolute_path%</code>,
                <code>%folder_url%</code>',
    'info'
);

AdminLayout::render_close_panel();

?>
                        </div>
                        <!-- End Notifications Panel -->

                        <!-- Permissions Panel -->
                        <div data-nav-panel="wpcp-permissions" class="hidden space-y-6">

                            <?php
// Permissions
AdminLayout::render_open_panel([
    'title' => esc_html__('Change Plugin Settings', 'wpcloudplugins'),
    'accordion' => true,
]);

AdminLayout::render_user_selectbox([
    'title' => '',
    'description' => esc_html__('Select which roles or users should be able to access the plugin settings page.', 'wpcloudplugins'),
    'key' => 'permissions_edit_settings',
    'default' => ['administrator'],
]);

AdminLayout::render_close_panel();

AdminLayout::render_open_panel([
    'title' => esc_html__('Link Users to Private Folders', 'wpcloudplugins'),
    'accordion' => true,
]);

AdminLayout::render_user_selectbox([
    'title' => '',
    'description' => esc_html__('Select which roles or users should be able to manually link users to their private folder.', 'wpcloudplugins'),
    'key' => 'permissions_link_users',
    'default' => ['administrator', 'editor'],
]);

AdminLayout::render_close_panel();

AdminLayout::render_open_panel([
    'title' => esc_html__('See Reports', 'wpcloudplugins'),
    'accordion' => true,
]);

AdminLayout::render_user_selectbox([
    'title' => '',
    'description' => esc_html__('Select which roles or users should be able to access event reports and statistics.', 'wpcloudplugins'),
    'key' => 'permissions_see_dashboard',
    'default' => ['administrator', 'editor'],
]);

AdminLayout::render_close_panel();

AdminLayout::render_open_panel([
    'title' => esc_html__('See Back-End Filebrowser', 'wpcloudplugins'),
    'accordion' => true,
]);

AdminLayout::render_user_selectbox([
    'title' => '',
    'description' => esc_html__('Select which roles or users should be able to access file browser in the Admin Dashboard.', 'wpcloudplugins'),
    'key' => 'permissions_see_filebrowser',
    'default' => ['administrator'],
]);

AdminLayout::render_close_panel();

AdminLayout::render_open_panel([
    'title' => esc_html__('Add & Configure Modules', 'wpcloudplugins'),
    'accordion' => true,
]);

AdminLayout::render_user_selectbox([
    'title' => '',
    'description' => esc_html__('Select which roles or users should be able to add and create modules.', 'wpcloudplugins'),
    'key' => 'permissions_add_shortcodes',
    'default' => ['administrator', 'editor', 'author', 'contributor'],
]);

AdminLayout::render_close_panel();

AdminLayout::render_open_panel([
    'title' => esc_html__('Add Direct links', 'wpcloudplugins'),
    'accordion' => true,
]);

AdminLayout::render_user_selectbox([
    'title' => '',
    'description' => esc_html__('Select which roles or users should be able to add shared links to documents on your pages and posts.', 'wpcloudplugins'),
    'key' => 'permissions_add_links',
    'default' => ['administrator', 'editor', 'author', 'contributor'],
]);

AdminLayout::render_close_panel();

AdminLayout::render_open_panel([
    'title' => esc_html__('Embed Documents', 'wpcloudplugins'),
    'accordion' => true,
]);

AdminLayout::render_user_selectbox([
    'title' => '',
    'description' => esc_html__('Select which roles or users should be able to embedded documents on your pages and posts.', 'wpcloudplugins'),
    'key' => 'permissions_add_embedded',
    'default' => ['administrator', 'editor', 'author', 'contributor'],
]);

AdminLayout::render_close_panel();
?>
                        </div>
                        <!-- End Permissions Panel -->

                        <!-- Statistics Panel -->
                        <div data-nav-panel="wpcp-statistics" class="hidden space-y-6">

                            <?php
// Statistics
AdminLayout::render_open_panel([
    'title' => 'Statistics',
]);

// Log Events
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Log Events', 'wpcloudplugins'),
    'description' => esc_html__('Register all plugin events.', 'wpcloudplugins'),
    'key' => 'log_events',
    'default' => false,
    'toggle_container' => '#toggle-event-options',
]);

AdminLayout::render_open_toggle_container(['key' => 'toggle-event-options']);

// Summary Email
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Summary Email', 'wpcloudplugins'),
    'description' => esc_html__('Email a summary of all the events that are logged with the plugin.', 'wpcloudplugins'),
    'key' => 'event_summary',
    'default' => false,
    'toggle_container' => '#toggle-event-summary-options',
]);

AdminLayout::render_open_toggle_container(['key' => 'toggle-event-summary-options']);

// Email Summary Interval
AdminLayout::render_simple_select([
    'title' => esc_html__('Interval', 'wpcloudplugins'),
    'description' => esc_html__('Please select the interval the summary needs to be send.', 'wpcloudplugins'),
    'options' => [
        'daily' => ['title' => esc_html__('Every day', 'wpcloudplugins')],
        'weekly' => ['title' => esc_html__('Weekly', 'wpcloudplugins')],
        'monthly' => ['title' => esc_html__('Monthly', 'wpcloudplugins')],
    ],
    'key' => 'event_summary_period',
    'default' => 'daily',
]);

// Email Summary Recipients
AdminLayout::render_simple_textbox([
    'title' => esc_html__('Recipients', 'wpcloudplugins'),
    'description' => esc_html__('Set to which email address(es) the summary should be send.', 'wpcloudplugins'),
    'placeholder' => get_option('admin_email'),
    'default' => '',
    'key' => 'event_summary_recipients',
]);

AdminLayout::render_close_toggle_container();

// Events WebHook
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Use Webhook', 'wpcloudplugins'),
    'description' => esc_html__('Send automated messages (JSON data) to another application for every event logged by the plugin.', 'wpcloudplugins'),
    'key' => 'webhook_active',
    'default' => false,
    'toggle_container' => '#toggle-event-webhook-options',
]);

AdminLayout::render_open_toggle_container(['key' => 'toggle-event-webhook-options']);

// Webhook Endpoint URL
AdminLayout::render_simple_textbox([
    'title' => esc_html__('Webhook Endpoint URL', 'wpcloudplugins'),
    'description' => esc_html__('The listener URL where the JSON data will be send to.', 'wpcloudplugins'),
    'placeholder' => 'https://example.com/listener.php',
    'default' => '',
    'key' => 'webhook_endpoint_url',
]);

// Webhook Endpoint Secret
AdminLayout::render_simple_textbox([
    'title' => esc_html__('Webhook Secret', 'wpcloudplugins'),
    'description' => esc_html__('The events send to your endpoint will include a signature. You can use this secret to verify that the events were sent by this plugin, not by a third party. See the documentation for more information.', 'wpcloudplugins'),
    'placeholder' => '',
    'default' => '',
    'key' => 'webhook_endpoint_secret',
]);

AdminLayout::render_close_toggle_container();

AdminLayout::render_close_toggle_container();

// Google Analytics
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Use Google Analytics tracker', 'wpcloudplugins'),
    'description' => esc_html__('The plugin will send its events to Google Analytics if your Google tracker has been added to your site.', 'wpcloudplugins'),
    'key' => 'google_analytics',
    'default' => false,
]);

AdminLayout::render_close_panel();
?>
                        </div>
                        <!-- End Statistics Panel -->

                        <!-- Tools Panel -->
                        <div data-nav-panel="wpcp-tools" class="hidden space-y-6">

                            <?php // Tools -> Cache Block
AdminLayout::render_open_panel([
    'title' => esc_html__('Cache', 'wpcloudplugins'),
]);

AdminLayout::render_simple_action_button([
    'title' => esc_html__('Purge Cache', 'wpcloudplugins'),
    'description' => esc_html__('WP Cloud Plugins uses a cache to improve performance. If the plugin somehow is causing issues, try to reset the cache first.', 'wpcloudplugins'),
    'key' => 'wpcp-purge-cache-button',
    'button_text' => esc_html__('Purge', 'wpcloudplugins'),
]);

AdminLayout::render_simple_action_button([
    'title' => esc_html__('Purge Thumbnails', 'wpcloudplugins'),
    'description' => esc_html__('As the API does not provide thumbnails on the fly, the plugin will generate and store them locally. You can purge that folder if needed..', 'wpcloudplugins'),
    'key' => 'wpcp-purge-thumbnails-button',
    'button_text' => esc_html__('Purge', 'wpcloudplugins'),
]);

AdminLayout::render_close_panel();

// Tools -> Export/Import
AdminLayout::render_open_panel([
    'title' => esc_html__('Backup', 'wpcloudplugins'),
]);

AdminLayout::render_simple_select([
    'title' => 'Select backup data',
    'description' => 'Select what kind of information should be stored in the backup.',
    'options' => [
        'all' => ['title' => esc_html__('Everything (settings, event logs & user ⇔ folder links)', 'wpcloudplugins'), 'toggle_container' => ''],
        'settings' => ['title' => esc_html__('Global settings', 'wpcloudplugins'), 'toggle_container' => ''],
        'userfolders' => ['title' => esc_html__('User ⇔ Folder links', 'wpcloudplugins'), 'toggle_container' => ''],
        'events' => ['title' => esc_html__('Event Logs', 'wpcloudplugins'), 'toggle_container' => ''],
    ],
    'key' => 'tools_export_fields',
    'default' => 'all',
]);

AdminLayout::render_simple_action_button([
    'title' => esc_html__('Export', 'wpcloudplugins'),
    'description' => esc_html__('When you click the export button, a (gzipped) JSON file will be generated. You can use the Import action below to restore the backup.', 'wpcloudplugins'),
    'key' => 'wpcp-export-button',
    'button_text' => esc_html__('Export', 'wpcloudplugins'),
]);

AdminLayout::render_file_selector([
    'title' => esc_html__('Import data', 'wpcloudplugins'),
    'description' => esc_html__('Select the export file(.json or .gz) you would like to import. Please note that the import will replace your current data.', 'wpcloudplugins'),
    'key' => 'wpcp-import',
    'accept' => '.json,.gz',
    'button_text' => esc_html__('Import', 'wpcloudplugins'),
]);

AdminLayout::render_close_panel();

// Tools -> Reset Block
AdminLayout::render_open_panel([
    'title' => esc_html__('Reset', 'wpcloudplugins'),
]);

AdminLayout::render_simple_action_button([
    'title' => esc_html__('Reset to Factory Settings', 'wpcloudplugins'),
    'description' => esc_html__('Need to revert back to the default settings? This button will instantly reset your settings to the defaults. When you reset the settings, the plugin will not longer be linked to your accounts, but their authorization will not be revoked. You can revoke the authorization via the Dashboard tab.', 'wpcloudplugins'),
    'key' => 'wpcp-factory-reset-button',
    'button_text' => esc_html__('Reset Plugin', 'wpcloudplugins'),
]);

AdminLayout::render_close_panel();
?>
                        </div>
                        <!-- End Tools Panel -->

                        <!-- System Information Panel -->
                        <div data-nav-panel="wpcp-system-information" class="hidden space-y-6">
                            <?php
    echo $this->get_system_information();
?>
                        </div>
                        <!-- End System Information Panel -->

                        <!-- Support Panel -->
                        <div data-nav-panel="wpcp-support" class="hidden space-y-6">
                            <!-- Start Support Block -->
                            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                                <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                                    <div class="-ml-4 -mt-2 flex items-center justify-between flex-wrap sm:flex-nowrap">
                                        <div class="ml-4 mt-2">
                                            <h3 class="text-2xl font-semibold text-gray-900"><?php esc_html_e('Support & Documentation', 'wpcloudplugins'); ?></h3>
                                            <p class="mt-1 max-w-2xl text-sm text-gray-500"><?php esc_html_e('Check the documentation of the plugin in case you encounter any problems or are looking for support.', 'wpcloudplugins'); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="px-4 py-5 sm:p-6">
                                    <p class="mt-1 max-w-2xl text-sm text-gray-500">

                                    </p>
                                    <div class="mt-5">
                                        <a type="button" href='<?php echo LETSBOX_ROOTPATH; ?>/_documentation/index.html' target="_blank" class="inline-flex items-center px-4 py-2 mr-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-brand-color-900 hover:bg-brand-color-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-color-700">

                                            <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                            </svg>
                                            <?php esc_html_e('Open Documentation', 'wpcloudplugins'); ?>
                                        </a>

                                        <a type="button" href='https://florisdeleeuwnl.zendesk.com/hc/en-us/articles/201845893' target="_blank" class="inline-flex items-center px-4 py-2 mr-2 border border-brand-color-700 shadow-sm text-base font-medium rounded-md text-brand-color-700  hover:bg-gray-200 hover:text-brand-color-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-color-700 sm:text-sm">

                                            <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                            </svg>
                                            <?php esc_html_e('Create support ticket', 'wpcloudplugins'); ?>
                                        </a>

                                    </div>

                                    <div class="mt-5 relative">
                                        <div class="aspect-video"><iframe src='https://vimeo.com/showcase/9015621/embed' allowfullscreen loading="lazy" frameborder='0' style='position:absolute;top:0;left:0;width:100%;height:100%;'></iframe></div>
                                    </div>

                                </div>
                            </div>
                            <!-- End Support Block -->
                        </div>
                        <!-- End Support Panel -->

                    </div>
                </div>
            </main>
        </div>

        <!-- Notification -->
        <div id="wpcp-notification" aria-live="assertive" class="fixed inset-0 flex items-end px-4 py-6 pointer-events-none sm:p-6 sm:items-end" style="display:none;">
            <div class="w-full flex flex-col items-center space-y-4 sm:items-end">
                <div class="max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden">
                    <div class="p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <!-- Heroicon name: outline/check-circle -->
                                <svg class="wpcp-notification-success h-6 w-6 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>

                                <!-- Heroicon name: outline/exclamation-circle -->
                                <svg class="wpcp-notification-failed h-6 w-6 text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3 w-0 flex-1 pt-0.5">
                                <p class="wpcp-notification-success text-sm font-medium text-gray-900"><?php esc_html_e('Successfully saved!', 'wpcloudplugins'); ?></p>
                                <p class="wpcp-notification-failed text-sm font-medium text-red-400"><?php esc_html_e('Setting not saved!', 'wpcloudplugins'); ?></p>
                            </div>
                            <div class="ml-4 flex-shrink-0 flex">
                                <button type="button" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-color-700">
                                    <span class="sr-only"><?php esc_html_e('Close', 'wpcloudplugins'); ?></span>
                                    <!-- Heroicon name: solid/x -->
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Notification -->

        <!-- Modal Privacy Policy -->
        <div id="wpcp-modal-privacy-policy" class="hidden wpcp-dialog">
            <div class="relative z-20" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
                <div class="fixed z-30 inset-0 overflow-y-auto">
                    <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">

                        <div class="relative bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full sm:p-6">
                            <div>
                                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-brand-color-900">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-5">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title"><?php esc_html_e('Requested scopes and justification', 'wpcloudplugins'); ?></h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            <?php echo sprintf(esc_html__('In order to display your content stored on %s, you have to authorize it with your %s account.', 'wpcloudplugins'), 'Box', 'Box'); ?> <?php _e('The authorization will ask you to grant the application the following scopes:', 'wpcloudplugins'); ?>
                                        </p>
                                    </div>
                                    <div class="my-3 p-2 border-2 border-gray-200 rounded-md">
                                        <code>root_readwrite</code>
                                        <p class="text-xs text-gray-500 mt-2">
                                            <?php echo sprintf(esc_html__('Allow the plugin to see, edit, create, and delete all of your %s files and files that are shared with you', 'wpcloudplugins'), 'Box'); ?>.
                                        </p>
                                    </div>
                                    <div class="my-3 p-2 border-2 border-gray-200 rounded-md">

                                        <code>userinfo</code>
                                        <p class="text-xs text-gray-500 mt-2">
                                            <?php esc_html_e('Allow the plugin to see your publicly available personal info, like name and profile picture. Your name and profile picture will be displayed on this page for easy account identification.', 'wpcloudplugins'); ?>
                                        </p>
                                    </div>
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title"><?php esc_html_e('Information about the data', 'wpcloudplugins'); ?></h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            The authorization tokens will be stored, encrypted, on this server and is not accessible by the developer or any third party. When you use the Application, all communications are strictly between your server and the cloud storage service servers. The communication is encrypted and the communication will not go through WP Cloud Plugins servers. We do not collect and do not have access to your personal data.
                                        </p>
                                    </div>

                                </div>
                            </div>
                            <div class="mt-5 sm:mt-6">
                                <button type="button" class="wpcp-button-primary wpcp-dialog-close inline-flex justify-center w-full">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Modal Privacy Policy -->

        <!-- Modal Activation -->
        <div id="wpcp-modal-activation" class="<?php echo License::is_valid() ? 'hidden' : ''; ?> wpcp-dialog">
            <div class="relative z-20" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-90 transition-opacity backdrop-blur-sm"></div>
                <div class="fixed z-30 inset-0 overflow-y-auto">
                    <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">

                        <div class="relative bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full sm:p-6">
                            <div>
                                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-brand-color-900">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-5">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title"><?php esc_html_e('Activate your license', 'wpcloudplugins'); ?></h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            <?php esc_html_e('To start using this plugin, please activate your license.', 'wpcloudplugins'); ?>
                                        </p>
                                    </div>
                                    <div class="my-3 p-2">
                                        <button id='wpcp-activate-button' type="button" class="wpcp-button-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            </svg>
                                            <?php esc_html_e('Activate via Envato Market', 'wpcloudplugins'); ?>
                                        </button>

                                        <a href="https://1.envato.market/M4B53" type="button" class="wpcp-button-secondary" target="_blank">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-3 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                            <?php esc_html_e('Buy License', 'wpcloudplugins'); ?>
                                        </a>
                                    </div>
                                    <div class="mt-6 mb-4 sm:flex items-center justify-center">
                                        <div class="flex-grow flex flex-col space-y-2 max-w-xl">
                                            <div class="text-sm text-gray-700 flex items-center justify-center italic "><?php esc_html_e('Or insert your license code manually:', 'wpcloudplugins'); ?></div>
                                            <div class="mt-1 flex rounded-md shadow-sm">
                                                <div class="relative flex items-stretch flex-grow focus-within:z-10">
                                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <!-- Heroicon name: solid/key -->
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z" clip-rule="evenodd" />
                                                        </svg>
                                                    </div>
                                                    <input type="text" name="license_code" id="license_code" class="text-center block w-full shadow-sm focus:ring-brand-color-700 focus:border-brand-color-700 sm:text-sm border border-gray-300 p-2 rounded-none rounded-l-md" value="" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx">
                                                </div>
                                                <button id="wpcp-license-activate" type="button" class="-ml-px relative inline-flex items-center space-x-2 px-4 py-2 border border-gray-300 text-sm font-medium rounded-r-md text-gray-700 bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-1 focus:ring-brand-color-700 focus:border-brand-color-700 disabled:opacity-75" disabled="disabled">
                                                    <!-- Heroicon name: solid/lock-open -->
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                                    </svg>
                                                    <span><?php esc_html_e('Activate', 'wpcloudplugins'); ?></span>
                                                </button>
                                            </div>
                                            <p class="purchase-input-error purchase-input-error-message hidden mt-2 text-sm text-red-600 "></p>
                                        </div>
                                    </div>
                                    <div class="mt-6">
                                        <a href="https://florisdeleeuwnl.zendesk.com/hc/en-us/articles/360017620619" class="wpcp-link-primary" target="_blank">FAQ: All about Licenses</a> |
                                        <a href="https://codecanyon.net/licenses/terms/regular" class="wpcp-link-primary" target="_blank"><?php esc_html_e('Terms Regular License', 'wpcloudplugins'); ?></a> |
                                        <a href="https://codecanyon.net/licenses/terms/extended" class="wpcp-link-primary" target="_blank"><?php esc_html_e('Terms Extended License', 'wpcloudplugins'); ?></a>
                                    </div>
                                    <div class="flex flex-col items-center mt-6 pt-6 text-gray-500 border-t border-gray-200 space-y-2">
                                        <img src="<?php echo LETSBOX_ROOTPATH; ?>/css/images/envato-market.svg" width="200">
                                        <a href="https://1.envato.market/a4ggZ" target="_blank" class="wpcp-link-primary italic ">Envato Market is the only official distributor of the WP Cloud Plugins.</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Modal Activation -->

    </div>
</div>