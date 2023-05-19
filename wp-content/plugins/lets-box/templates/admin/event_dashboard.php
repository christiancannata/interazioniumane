<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2022, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\LetsBox;

if (!defined('ABSPATH')) {
    exit;
}

// Exit if no permission
if (
    !Helpers::check_user_role(Core::get_setting('permissions_see_dashboard'))
) {
    exit;
}

?>
<div id="wpcp" class="wpcp-app wpcp-reports hidden">
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
                    <!-- Main 3 column grid -->
                    <div class="grid grid-cols-1 gap-4 items-start lg:grid-cols-5 lg:gap-8">
                        <!-- Left column -->
                        <div class="grid grid-cols-1 gap-4 lg:col-span-3">
                            <!-- Welcome panel -->
                            <section aria-labelledby="profile-overview-title">
                                <div class="rounded-lg bg-white overflow-hidden shadow">
                                    <div class="bg-white p-6">
                                        <div class="sm:flex sm:items-center sm:justify-between">
                                            <?php $current_user = wp_get_current_user(); ?>
                                            <div class="sm:flex sm:space-x-5">
                                                <div class="flex-shrink-0">
                                                    <img class="mx-auto h-20 w-20 rounded-full" src="<?php echo \get_avatar_url($current_user->ID); ?>" alt="">
                                                </div>
                                                <div class="mt-4 text-center sm:mt-0 sm:pt-1 sm:text-left">
                                                    <p class="text-sm font-medium text-gray-600"><?php esc_html_e('Welcome back', 'wpcloudplugins'); ?>,</p>
                                                    <p class="text-xl font-bold text-gray-900 sm:text-2xl"><?php echo $current_user->display_name; ?></p>
                                                    <p class="text-sm font-medium text-gray-600"><?php echo $current_user->user_email; ?></p>
                                                </div>
                                            </div>
                                            <div class="mt-5 flex justify-center sm:mt-0 space-x-2">
                                                <a href="#full-log" class="wpcp-button-secondary"><?php esc_html_e('Event Log', 'wpcloudplugins'); ?></a>
                                                <button id="clear_statistics" class="wpcp-button-secondary"> <?php esc_html_e('Reset Log', 'wpcloudplugins'); ?></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="wpcp-counter-totals" class="border-t border-gray-200 bg-gray-50 grid grid-cols-1 divide-y divide-gray-200 sm:grid-cols-4 sm:divide-y-0 sm:divide-x">
                                        <div class="px-6 py-5 text-sm font-medium text-center flex items-center justify-center space-x-2">
                                            <div class="text-gray-900 wpcp-counter rounded-full p-2" data-type="letsbox_previewed_entry">-</div>
                                            <div class="text-gray-600"><?php esc_html_e('Previews', 'wpcloudplugins'); ?></div>
                                        </div>

                                        <div class="px-6 py-5 text-sm font-medium text-center flex items-center justify-center space-x-2">
                                            <div class="text-gray-900 wpcp-counter rounded-full p-2" data-type="letsbox_downloaded_entry">-</div>
                                            <div class="text-gray-600"><?php esc_html_e('Downloads', 'wpcloudplugins'); ?></div>
                                        </div>

                                        <div class="px-6 py-5 text-sm font-medium text-center flex items-center justify-center space-x-2">
                                            <div class="text-gray-900 wpcp-counter rounded-full p-2" data-type="letsbox_created_link_to_entry">-</div>
                                            <div class="text-gray-600"><?php esc_html_e('Items Shared', 'wpcloudplugins'); ?></div>
                                        </div>

                                        <div class="px-6 py-5 text-sm font-medium text-center flex items-center justify-center space-x-2">
                                            <div class="text-gray-900 wpcp-counter rounded-full p-2" data-type="letsbox_uploaded_entry">-</div>
                                            <div class="text-gray-600"><?php esc_html_e('Uploads', 'wpcloudplugins'); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- Select Period -->
                            <section>
                                <div class="rounded-lg bg-white overflow-hidden shadow divide-y divide-gray-200 sm:divide-y-0 flex items-center justify-between">
                                    <div class="p-4 sm:px-6  flex items-center justify-start">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <h3 class="text-lg font-medium text-gray-900 ml-2 "><?php esc_html_e('Select Period', 'wpcloudplugins'); ?></h3>
                                    </div>
                                    <div class="p-4 sm:px-6">
                                        <input type="text" class="date_range_selector wpcp-input-textbox bg-white font-medium text-center w-64 max-w-xl flex-1 block shadow-sm focus:ring-brand-color-700 focus:border-brand-color-700 sm:text-sm border border-gray-300 rounded-md" name="date_range_selector">
                                    </div>
                                </div>
                            </section>

                            <!-- Events per Day -->
                            <section>
                                <div class="rounded-lg bg-white overflow-hidden shadow divide-y divide-gray-200 sm:divide-y-0 sm:grid sm:grid-cols-1 sm:gap-px">
                                    <div class="bg-white px-4 py-5 border-b border-gray-200 sm:px-6 flex items-center justify-start">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                        </svg>
                                        <h3 class="text-lg leading-6 font-medium  ml-2"><?php esc_html_e('Events per Day', 'wpcloudplugins'); ?></h3>
                                    </div>
                                    <div class="wpcp-events-chart-container w-full px-4 py-5 aspect-video">
                                        <div class="loading">
                                            <div class='loader-beat'></div>
                                        </div>
                                        <canvas id="wpcp-events-chart"></canvas>
                                    </div>
                                </div>
                            </section>

                            <!-- All Events -->
                            <section>
                                <div class="rounded-lg bg-white overflow-hidden shadow divide-y divide-gray-200 sm:divide-y-0 sm:grid sm:grid-cols-1 sm:gap-px">
                                    <div class="bg-white px-4 py-5 border-b border-gray-200 sm:px-6 flex items-center justify-start">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                        </svg>
                                        <h3 class="text-lg leading-6 font-medium text-gray-900 ml-2"><?php esc_html_e('All Events', 'wpcloudplugins'); ?></h3>
                                    </div>
                                    <div class="w-full px-4 py-5">
                                        <table id="full-log">
                                            <thead>
                                                <tr>
                                                    <th></th>
                                                    <th class="all"><?php esc_html_e('Description', 'wpcloudplugins'); ?></th>
                                                    <th><?php esc_html_e('Date', 'wpcloudplugins'); ?></th>
                                                    <th><?php esc_html_e('Event', 'wpcloudplugins'); ?></th>
                                                    <th><?php esc_html_e('User', 'wpcloudplugins'); ?></th>
                                                    <th><?php esc_html_e('Name', 'wpcloudplugins'); ?></th>
                                                    <th><?php esc_html_e('Location', 'wpcloudplugins'); ?></th>
                                                    <th><?php esc_html_e('Page', 'wpcloudplugins'); ?></th>
                                                    <th><?php esc_html_e('Extra', 'wpcloudplugins'); ?></th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </section>
                        </div>

                        <!-- Right column -->
                        <div class="grid grid-cols-1 gap-4 lg:col-span-2">
                            <!-- Top 25 Downloads -->
                            <section id="top-25-downloads">
                                <div class="rounded-lg bg-white overflow-hidden shadow">
                                    <div class="p-6">
                                        <div class="bg-white pb-5 border-b border-gray-200 flex items-center justify-start">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                            <h3 class="text-lg leading-6 font-medium  ml-2"><?php esc_html_e('Top 25 Downloads', 'wpcloudplugins'); ?></h3>
                                        </div>
                                        <div class="flow-root mt-6">
                                            <table id="top-downloads" class="stripe hover order-column mt-2">
                                                <thead>
                                                    <tr>
                                                        <th></th>
                                                        <th><?php esc_html_e('Document', 'wpcloudplugins'); ?></th>
                                                        <th><?php esc_html_e('Total', 'wpcloudplugins'); ?></th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- Top 25 Downloads by User -->
                            <section id="top-25-user-downloads">
                                <div class="rounded-lg bg-white overflow-hidden shadow">
                                    <div class="p-6">
                                        <div class="bg-white pb-5 border-b border-gray-200 flex items-center justify-start">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            <h3 class="text-lg leading-6 font-medium  ml-2"><?php esc_html_e('Top 25 Users with most Downloads', 'wpcloudplugins'); ?></h3>
                                        </div>

                                        <div class="flow-root mt-6">
                                            <table id="top-users" class="display" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th></th>
                                                        <th><?php esc_html_e('User', 'wpcloudplugins'); ?></th>
                                                        <!-- <th><?php esc_html_e('Username'); ?></th> -->
                                                        <th><?php esc_html_e('Downloads', 'wpcloudplugins'); ?></th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- Latest 25 Uploads -->
                            <section id="latest-25-uploads">
                                <div class="rounded-lg bg-white overflow-hidden shadow">
                                    <div class="p-6">
                                        <div class="bg-white pb-5 border-b border-gray-200 flex items-center justify-start">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                            </svg>
                                            <h3 class="text-lg leading-6 font-medium  ml-2"><?php esc_html_e('Latest 25 Uploads', 'wpcloudplugins'); ?></h3>
                                        </div>

                                        <div class="flow-root mt-6">
                                            <table id="latest-uploads" class="stripe hover order-column" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th></th>
                                                        <th><?php esc_html_e('Document', 'wpcloudplugins'); ?></th>
                                                        <th><?php esc_html_e('Date', 'wpcloudplugins'); ?></th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- Top 25 Users with most Uploads -->
                            <section id="top-25-user-uploads">
                                <div class="rounded-lg bg-white overflow-hidden shadow">
                                    <div class="p-6">
                                        <div class="bg-white pb-5 border-b border-gray-200 flex items-center justify-start">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            <h3 class="text-lg leading-6 font-medium  ml-2"><?php esc_html_e('Top 25 Users with most Uploads', 'wpcloudplugins'); ?></h3>
                                        </div>

                                        <div class="flow-root mt-6">
                                            <table id="top-uploads" class="display" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th></th>
                                                        <th><?php esc_html_e('User', 'wpcloudplugins'); ?></th>
                                                        <!-- <th><?php esc_html_e('Username'); ?></th> -->
                                                        <th><?php esc_html_e('Uploads', 'wpcloudplugins'); ?></th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <!-- Modal Details -->
        <div id="wpcp-modal-details-template" class="wpcp-dialog hidden">
            <div class="relative z-20" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-90 transition-opacity backdrop-blur-sm"></div>
                <div class="fixed z-30 inset-0 overflow-y-auto">
                    <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">

                        <div class="relative bg-gray-100 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-4xl sm:w-full sm:p-6">

                            <div class="grid grid-cols-1 gap-4 lg:col-span-2 opacity-0 transition duration-600 ease-in">
                                <!-- Welcome panel -->
                                <section aria-labelledby="profile-overview-title" class="wpcp-event-details">
                                    <div class="rounded-lg bg-white overflow-hidden shadow">
                                        <div class="bg-white p-4">
                                            <div class="sm:flex sm:items-center sm:justify-between">
                                                <div class="sm:flex sm:space-x-5">
                                                    <div class="flex-shrink-0">
                                                        <img class="wpcp-event-details-entry-img mx-auto h-16 w-16 object-cover shadow-sm" alt="" />
                                                    </div>
                                                    <div class="mt-4 flex flex-col justify-center items-start sm:mt-0 sm:pt-1">
                                                        <p class="wpcp-event-details-name text-xl font-bold text-gray-900 sm:text-2xl"></p>
                                                        <p class="wpcp-event-details-description text-sm font-medium text-gray-600 line-clamp-3"></p>
                                                    </div>
                                                </div>
                                                <div class="ml-5 flex justify-center items-center space-x-2">
                                                    <a type="button" class="wpcp-button-primary wpcp-event-download-entry inline-flex justify-center w-full" download><?php esc_html_e('Download', 'wpcloudplugins'); ?></a>
                                                    <button type="button" class="wpcp-button-primary wpcp-dialog-destroy inline-flex justify-center w-full"><?php esc_html_e('Close', 'wpcloudplugins'); ?></button>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="wpcp-event-details-totals" class="border-t border-gray-200 bg-gray-50 grid grid-cols-1 divide-y divide-gray-200 sm:grid-cols-4 sm:divide-y-0 sm:divide-x">
                                            <div class="px-6 py-2 text-sm font-medium text-center flex items-center justify-center space-x-2">
                                                <div class="text-gray-900 wpcp-counter rounded-full p-2" data-type="letsbox_previewed_entry">-</div>
                                                <div class="text-gray-600"><?php esc_html_e('Previews', 'wpcloudplugins'); ?></div>
                                            </div>

                                            <div class="px-6 py-2 text-sm font-medium text-center flex items-center justify-center space-x-2">
                                                <div class="text-gray-900 wpcp-counter rounded-full p-2" data-type="letsbox_downloaded_entry">-</div>
                                                <div class="text-gray-600"><?php esc_html_e('Downloads', 'wpcloudplugins'); ?></div>
                                            </div>

                                            <div class="px-6 py-2 text-sm font-medium text-center flex items-center justify-center space-x-2">
                                                <div class="text-gray-900 wpcp-counter rounded-full p-2" data-type="letsbox_created_link_to_entry">-</div>
                                                <div class="text-gray-600"><?php esc_html_e('Items Shared', 'wpcloudplugins'); ?></div>
                                            </div>

                                            <div class="px-6 py-2 text-sm font-medium text-center flex items-center justify-center space-x-2">
                                                <div class="text-gray-900 wpcp-counter rounded-full p-2" data-type="letsbox_uploaded_entry">-</div>
                                                <div class="text-gray-600"><?php esc_html_e('Uploads', 'wpcloudplugins'); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <!-- All Events -->
                                <section>
                                    <div class="rounded-lg bg-white overflow-hidden shadow divide-y divide-gray-200 sm:divide-y-0 sm:grid sm:grid-cols-1 sm:gap-px">
                                        <div class="bg-white px-4 py-5 border-b border-gray-200">
                                            <h3 class="text-lg leading-6 font-medium text-gray-900"><?php esc_html_e('All Events', 'wpcloudplugins'); ?></h3>
                                        </div>
                                        <div class="w-full px-4 py-5">
                                            <table id="wpcp-full-detail-log">
                                                <thead>
                                                    <tr>
                                                        <th></th>
                                                        <th class="all"><?php esc_html_e('Description', 'wpcloudplugins'); ?></th>
                                                        <th><?php esc_html_e('Date', 'wpcloudplugins'); ?></th>
                                                        <th><?php esc_html_e('Event', 'wpcloudplugins'); ?></th>
                                                        <th><?php esc_html_e('User', 'wpcloudplugins'); ?></th>
                                                        <th><?php esc_html_e('Name', 'wpcloudplugins'); ?></th>
                                                        <th><?php esc_html_e('Location', 'wpcloudplugins'); ?></th>
                                                        <th><?php esc_html_e('Page', 'wpcloudplugins'); ?></th>
                                                        <th><?php esc_html_e('Extra', 'wpcloudplugins'); ?></th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </section>
                            </div>
                            <div class="wpcp-event-details-loader absolute inset-0 flex items-center justify-center">
                                <div class="wpcp-loading-beat"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Modal Details -->

    </div>
</div>