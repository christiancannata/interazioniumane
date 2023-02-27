<?php

namespace TheLion\LetsBox\Integrations;

// exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class ACF_LetsBox_Field extends \acf_field
{
    /*
    *  __construct
    *
    *  This function will setup the field type data
    *
    *  @type	function
    *  @date	5/03/2014
    *  @since	5.0.0
    *
    *  @param	n/a
    *  @return	n/a
    */

    public function __construct($settings)
    {
        // name (string) Single word, no spaces. Underscores allowed

        $this->name = 'LetsBox_Field';

        // label (string) Multiple words, can include spaces, visible when selecting a field type

        $this->label = 'Box items';

        // category (string) basic | content | choice | relational | jquery | layout | CUSTOM GROUP NAME

        $this->category = 'WP Cloudplugins';

        // defaults (array) Array of default settings which are merged into the field object. These are used later in settings

        $this->defaults = [
        ];

        /*
        *  l10n (array) Array of strings that are used in JavaScript. This allows JS strings to be translated in PHP and loaded via:
        *  var message = acf._e('FIELD_NAME', 'error');
        */

        $this->l10n = [
        ];

        // settings (array) Store plugin settings (url, path, version) as a reference for later use with assets

        $this->settings = $settings;

        $this->_load_hooks();
        // do not delete!
        parent::__construct();
    }
    /*
    *  render_field_settings()
    *
    *  Create extra settings for your field. These are visible when editing a field
    *
    *  @type	action
    *  @since	3.6
    *  @date	23/01/13
    *
    *  @param	$field (array) the $field being edited
    *  @return	n/a
    */

    public function render_field_settings($field)
    {
        /*
        *  acf_render_field_setting
        *
        *  This function will create a setting for your field. Simply pass the $field parameter and an array of field settings.
        *  The array of settings does not require a `value` or `prefix`; These settings are found from the $field array.
        *
        *  More than one setting can be added by copy/paste the above code.
        *  Please note that you must also have a matching $defaults value for the field name (font_size)
        */

        acf_render_field_setting(
            $field,
            [
                'label' => 'Returned data',
                'instructions' => 'What information should be available for the files objects besides the default <code>name</code>, <code>size</code>, <code>icon_url</code>, <code>direct_url</code> and <code>download_url</code>',
                'type' => 'checkbox',
                'layout' => 'horizontal',
                'name' => 'return_data',
                'choices' => [
                    'shortlived_download_url' => 'Temporarily download URL (<code>shortlived_download_url</code>) | A direct, short-lived, download URL to the file in the cloud.',
                    'shared_url' => 'Public URL (<code>shared_url</code>) | A shared URL to the file is created, accessible by anyone with the link.',
                    'embed_url' => 'Embed URL (<code>embed_url</code>) | A shared URL for embedding the file in an iFrame. Only available for supported formats.',
                    'thumbnail_url' => 'Thumbnail URL (<code>thumbnail_url</code>) | An URL to a thumbnail of the file. If no thumbnail is available an icon will be shown.',
                ],
            ]
        );
    }

    /*
    *  render_field()
    *
    *  Create the HTML interface for your field
    *
    *  @param	$field (array) the $field being rendered
    *
    *  @type	action
    *  @since	3.6
    *  @date	23/01/13
    *
    *  @param	$field (array) the $field being edited
    *  @return	n/a
    */

    public function render_field($field)
    {
        acf_hidden_input(
            [
                'name' => $field['name'],
                'value' => empty($field['value']) ? '{}' : json_encode($field['value']),
                'data-name' => 'id',
            ]
        ); ?>
        <table class="wpcp-acf-items-table wp-list-table widefat striped">
            <thead>
                <th style="width: 18px;"></th>
                <th><?php esc_html_e('Name', 'wpcloudplugins'); ?></th>
                <th><?php esc_html_e('Entry ID', 'wpcloudplugins'); ?></th>
                <th style="width: 175px;"></th>
            </thead>
            <tbody>
            </tbody>
        </table>
        <br/>
        <a href="#" class="button button-primary button-large wpcp-acf-add-item"><?php printf(esc_html__('Choose from %s', 'wpcloudplugins'), 'Box'); ?></a>

	<?php
            $this->render_file_selector();
    }

    /**
     * Render the File Browser to allow the user to add files to the Product.
     *
     * @param \WP_Post $post
     *
     * @return string
     */
    public function render_file_selector()
    {
        ?>
        <div id='lb-embedded' style='clear:both;display:none'>
          <?php

          $atts = ['mode' => 'files',
              'filelayout' => 'list',
              'filesize' => '0',
              'filedate' => '0',
              'addfolder' => '0',
              'showbreadcrumb' => '0',
              'downloadrole' => 'none',
              'candownloadzip' => '0',
              'showsharelink' => '0',
              'search' => '1',
              'mcepopup' => 'woocommerce', ];

        $user_folder_backend = apply_filters('letsbox_use_user_folder_backend', $this->get_processor()->get_setting('userfolder_backend'));

        if ('No' !== $user_folder_backend) {
            $atts['userfolders'] = $user_folder_backend;

            $private_root_folder = $this->get_processor()->get_setting('userfolder_backend_auto_root');
            if ('auto' === $user_folder_backend && !empty($private_root_folder) && isset($private_root_folder['id'])) {
                $atts['dir'] = $private_root_folder['id'];

                if (!isset($private_root_folder['view_roles'])) {
                    $private_root_folder['view_roles'] = ['none'];
                }
                $atts['viewuserfoldersrole'] = implode('|', $private_root_folder['view_roles']);
            }
        }

        echo $this->get_processor()->create_from_shortcode($atts); ?>
        </div>
        <?php
    }

    /*
    *  input_admin_enqueue_scripts()
    *
    *  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
    *  Use this action to add CSS + JavaScript to assist your render_field() action.
    *
    *  @type	action (admin_enqueue_scripts)
    *  @since	3.6
    *  @date	23/01/13
    *
    *  @param	n/a
    *  @return	n/a
    */

    public function input_admin_enqueue_scripts()
    {
        // vars
        $url = $this->settings['url'];
        $version = $this->settings['version'];

        // register & include JS
        add_thickbox();

        $this->get_main()->load_scripts();
        $this->get_main()->load_styles();

        wp_enqueue_style('LetsBox.ShortcodeBuilder');

        wp_register_script('WPCP_ACF_'.$this->name, "{$url}assets/js/input.js", ['acf-input'], $version);
        wp_enqueue_script('WPCP_ACF_'.$this->name);
    }

    /*
    *  load_value()
    *
    *  This filter is applied to the $value after it is loaded from the db
    *
    *  @type	filter
    *  @since	3.6
    *  @date	23/01/13
    *
    *  @param	$value (mixed) the value found in the database
    *  @param	$post_id (mixed) the $post_id from which the value was loaded
    *  @param	$field (array) the field array holding all the field options
    *  @return	$value
    */

    public function load_value($value, $post_id, $field)
    {
        if (empty($value)) {
            return [];
        }

        return json_decode($value, true);
    }

    /*
    *  update_value()
    *
    *  This filter is applied to the $value before it is saved in the db
    *
    *  @type	filter
    *  @since	3.6
    *  @date	23/01/13
    *
    *  @param	$value (mixed) the value found in the database
    *  @param	$post_id (mixed) the $post_id from which the value was loaded
    *  @param	$field (array) the field array holding all the field options
    *  @return	$value
    */

    public function update_value($value, $post_id, $field)
    {
        if (!is_array($value)) {
            $entries = json_decode(wp_unslash($value), true);
        } else {
            $entries = $value;
        }

        if (empty($entries)) {
            return [];
        }

        foreach ($entries as $entry_id => $entry) {
            if (!empty($entries[$entry_id]['embed_url'])) {
                continue; // Don't get all data again if it is already present
            }

            $cached_entry = $this->get_processor()->get_client()->get_entry($entry_id, false);

            // Name
            $entries[$entry_id]['name'] = $cached_entry->get_name();

            // Size
            $size = $cached_entry->get_entry()->get_size();
            $entries[$entry_id]['size'] = ($size > 0) ? \TheLion\LetsBox\Helpers::bytes_to_size_1024($size) : '';

            // Direct URL
            $entries[$entry_id]['direct_url'] = 'https://app.box.com/'.($cached_entry->get_entry()->is_dir() ? 'folder' : 'file').'/'.$entry_id;

            // Download link
            $entries[$entry_id]['download_url'] = LETSBOX_ADMIN_URL."?action=letsbox-acf-download&pid={$post_id}&fid={$field['key']}&id={$entry_id}";

            // Icon URL
            $entries[$entry_id]['icon_url'] = $cached_entry->get_entry()->get_icon();
        }

        return json_encode($entries);
    }

    /*
    *  format_value()
    *
    *  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
    *
    *  @type	filter
    *  @since	3.6
    *  @date	23/01/13
    *
    *  @param	$value (mixed) the value which was loaded from the database
    *  @param	$post_id (mixed) the $post_id from which the value was loaded
    *  @param	$field (array) the field array holding all the field options
    *
    *  @return	$value (mixed) the modified value
    */

    public function format_value($entries, $post_id, $field)
    {
        // bail early if no value
        if (empty($entries)) {
            return [];
        }

        foreach ($entries as $entry_id => $entry) {
            $cached_entry = $this->get_processor()->get_client()->get_entry($entry_id, false);

            // Thumbnail
            if (in_array('thumbnail_url', $field['return_data'], true)) {
                if (empty($entries[$entry_id]['thumbnail_url'])) {
                    $entries[$entry_id]['thumbnail_url'] = $cached_entry->get_entry()->get_thumbnail_large();
                }
            } else {
                $entries[$entry_id]['thumbnail_url'] = null;
            }

            // Embed URL
            if (in_array('embed_url', $field['return_data'], true)) {
                if (empty($entries[$entry_id]['embed_url'])) {
                    $entries[$entry_id]['embed_url'] = $this->get_processor()->get_client()->get_embedded_link($cached_entry);

                    // Update this information
                    update_field($field['key'], $entries, $post_id);
                }
            } else {
                $entries[$entry_id]['embed_url'] = null;
            }

            // Shared (public) URL
            if (in_array('shared_url', $field['return_data'], true)) {
                if (empty($entries[$entry_id]['shared_url'])) {
                    $entries[$entry_id]['shared_url'] = $this->get_processor()->get_client()->get_shared_link($cached_entry);

                    // Update this information
                    update_field($field['key'], $entries, $post_id);
                }
            } else {
                $entries[$entry_id]['shared_url'] = null;
            }

            // Short-lived direct download URL
            if (in_array('shortlived_download_url', $field['return_data'], true)) {
                if (empty($entries[$entry_id]['shortlived_download_url'])) {
                    $entries[$entry_id]['shortlived_download_url'] = $this->get_processor()->get_client()->get_temporarily_link($cached_entry);
                }
            } else {
                $entries[$entry_id]['shortlived_download_url'] = null;
            }
        }

        // return
        return $entries;
    }

    /**
     * Start the download for entry with $id.
     *
     * @param string $id
     * @param mixed  $entry_id
     * @param mixed  $account_id
     */
    public function start_download()
    {
        if (!isset($_REQUEST['pid']) || !isset($_REQUEST['fid']) || !isset($_REQUEST['id'])) {
            http_response_code(400);

            exit();
        }

        $entries = get_field(sanitize_key($_REQUEST['fid']), sanitize_key($_REQUEST['pid']), false);

        if (empty($entries) || !isset($entries[$_REQUEST['id']])) {
            http_response_code(401);

            exit();
        }

        $cached_entry = $this->get_processor()->get_client()->get_entry($_REQUEST['id'], false);

        if (empty($cached_entry)) {
            http_response_code(404);
        }

        if ($cached_entry->get_entry()->is_dir()) {
            $this->get_processor()->set_requested_entry($_REQUEST['id']);
            $zip = new \TheLion\LetsBox\Zip($this->get_processor(), sanitize_key($_REQUEST['fid']));
            $zip->do_zip();

            exit();
        }

        $temporarily_link = $this->get_processor()->get_client()->get_temporarily_link($cached_entry);

        // Download Hook
        do_action('letsbox_download', $cached_entry, $temporarily_link);
        do_action('letsbox_log_event', 'letsbox_downloaded_entry', $cached_entry);

        header('Location: '.$temporarily_link);

        exit();
    }

    /**
     * @return \TheLion\LetsBox\Processor
     */
    public function get_processor()
    {
        return $this->get_main()->get_processor();
    }

    /**
     * @return \TheLion\LetsBox\Main
     */
    public function get_main()
    {
        global $LetsBox;

        return $LetsBox;
    }

    private function _load_hooks()
    {
        add_action('wp_ajax_nopriv_letsbox-acf-download', [$this, 'start_download']);
        add_action('wp_ajax_letsbox-acf-download', [$this, 'start_download']);
    }
}

// initialize
new \TheLion\LetsBox\Integrations\ACF_LetsBox_Field($this->settings);

?>