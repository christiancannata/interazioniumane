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
    !Helpers::check_user_role(Core::get_setting('permissions_add_shortcodes'))
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
                        <iframe class='w-full aspect-video bg-gray-100 py-2 rounded' src='<?php echo LETSBOX_ADMIN_URL; ?>?action=letsbox-getpopup&type=shortcodebuilder&standalone' tabindex='-1' frameborder='0'></iframe>
                    </div>

                </div>
            </main>
        </div>
    </div>
</div>