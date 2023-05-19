<?php

namespace TheLion\LetsBox\Integrations;

use TheLion\LetsBox\Processor;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class GF_WPCP_AddOn extends \GFAddOn
{
    protected $_version = '2.0';
    protected $_min_gravityforms_version = '2.5';
    protected $_slug = 'wpcp-letsbox';
    protected $_path = 'lets-box/includes/integrations/gravityforms/init.php';
    protected $_full_path = __FILE__;
    protected $_title = 'Gravity Forms Lets-Box Add-On';
    protected $_short_title = 'Lets-Box Add-On';

    public function init()
    {
        parent::init();

        if (!$this->is_gravityforms_supported($this->_min_gravityforms_version)) {
            return;
        }

        // Add default value for field
        add_action('gform_editor_js_set_default_values', [$this, 'field_defaults']);

        // Add a custom setting to the field
        add_action('gform_field_standard_settings', [$this, 'custom_field_settings'], 10, 2);

        // Filter to add the tooltip for the field
        add_filter('gform_tooltips', [$this, 'add_tooltip']);

        // Add support for wpDataTables <> Gravity Form integration
        if (class_exists('WPDataTable')) {
            add_action('wpdatatables_before_get_table_metadata', [$this, 'render_wpdatatables_field'], 10, 1);
        }

        // Custom Private Folder names
        add_filter('letsbox_private_folder_name', [$this, 'new_private_folder_name'], 10, 2);
        add_filter('letsbox_private_folder_name_guests', [$this, 'rename_private_folder_names_for_guests'], 10, 2);
        add_action('gform_user_registered', [$this, 'create_private_folder'], 10, 4);

        // Deprecated hooks, but still in use by e.g. GravityView + GravityFlow?
        add_filter('gform_entry_field_value', [$this, 'filter_entry_field_value'], 10, 4);
    }

    public function scripts()
    {
        if (\GFForms::is_gravity_page()) {
            \TheLion\LetsBox\Core::instance()->load_scripts();
            \TheLion\LetsBox\Core::instance()->load_styles();

            add_thickbox();
        }

        $scripts = [
            [
                'handle' => $this->_slug.'-gravityforms',
                'src' => plugins_url('script.js', __FILE__),
                'version' => LETSBOX_VERSION,
                'deps' => ['jquery'],
                'in_footer' => true,
                'enqueue' => [
                    [
                        'admin_page' => ['form_editor', 'entry_edit'],
                    ],
                ],
            ],
        ];

        return array_merge(parent::scripts(), $scripts);
    }

    public function styles()
    {
        wp_enqueue_style('LetsBox');

        $styles = [
            [
                'handle' => $this->_slug.'-gravityforms',
                'src' => plugins_url('style.css', __FILE__),
                'version' => LETSBOX_VERSION,
                'deps' => ['LetsBox'],
                'enqueue' => [
                    [
                        'admin_page' => ['form_editor', 'entry_edit'],
                    ],
                ],
            ],
        ];

        return array_merge(parent::styles(), $styles);
    }

    public function custom_field_settings($position, $form_id)
    {
        if (1430 == $position) {
            ?>
<li class="letsbox_setting field_setting">
    <label for="field_wpcp_letsbox">Shortcode <?php echo gform_tooltip('form_field_'.$this->_slug); ?></label>
    <textarea id="field_wpcp_letsbox" class="large fieldwidth-3 fieldheight-2" onchange="SetFieldProperty('LetsBoxShortcode', this.value)"></textarea>
    </br>
    <button class='button gform-button primary wpcp-shortcodegenerator letsbox'><?php esc_html_e('Build your shortcode', 'wpcloudplugins'); ?></button>
</li>
<?php
        }
    }

    public function add_tooltip($tooltips)
    {
        $tooltips['form_field_'.$this->_slug] = '<h6>Shortcode</h6>'.esc_html__('Create the module configuration via the Shortcode Builder or copy+paste the raw shortcode in this field', 'wpcloudplugins');

        return $tooltips;
    }

    public function field_defaults()
    {
        ?>
case 'letsbox':
field.label = <?php echo json_encode(esc_html__('Attach your documents', 'wpcloudplugins')); ?>;
break;
<?php
    }

    public function create_private_folder($user_id, $feed, $entry, $password)
    {
        if ('Yes' === \TheLion\LetsBox\Core::instance()->get_setting('userfolder_oncreation')) {
            \TheLion\LetsBox\Core::instance()->user_folder_create($user_id);
        }
    }

    /**
     * Function to change the Private Folder Name.
     *
     * @param string                     $private_folder_name
     * @param \TheLion\LetsBox\Processor $processor
     *
     * @return string
     */
    public function new_private_folder_name($private_folder_name, $processor)
    {
        if (!isset($_COOKIE['WPCP-FORM-NAME-'.$processor->get_listtoken()])) {
            return $private_folder_name;
        }

        if ('gf_upload_box' !== Processor::instance()->get_shortcode_option('class')) {
            return $private_folder_name;
        }

        $raw_name = sanitize_text_field($_COOKIE['WPCP-FORM-NAME-'.$processor->get_listtoken()]);
        $name = str_replace(['|', '/'], ' ', $raw_name);
        $filtered_name = \TheLion\LetsBox\Helpers::filter_filename(stripslashes($name), false);

        return trim($filtered_name);
    }

    /**
     * Function to change the Private Folder Name for Guest users.
     *
     * @param string                     $private_folder_name_guest
     * @param \TheLion\LetsBox\Processor $processor
     *
     * @return string
     */
    public function rename_private_folder_names_for_guests($private_folder_name_guest, $processor)
    {
        if ('gf_upload_box' !== Processor::instance()->get_shortcode_option('class')) {
            return $private_folder_name_guest;
        }

        $prefix = Processor::instance()->get_setting('userfolder_name_guest_prefix');

        return str_replace($prefix, '', $private_folder_name_guest);
    }

    public function render_wpdatatables_field($tableId)
    {
        add_filter('gform_get_input_value', [$this, 'entry_field_value'], 10, 4);
    }

    public function filter_entry_field_value($value, $field, $entry, $form)
    {
        return $this->entry_field_value($value, $entry, $field, null);
    }

    public function entry_field_value($value, $entry, $field, $input_id)
    {
        if ('letsbox' !== $field->type) {
            return $value;
        }

        return apply_filters('letsbox_render_formfield_data', html_entity_decode($value), true, $this);
    }
}

class GF_WPCP_Field extends \GF_Field
{
    public $type = 'letsbox';
    public $defaultValue = '[letsbox mode="upload" upload="1" uploadrole="all" upload_auto_start="0" userfolders="auto" viewuserfoldersrole="none"]';

    public function get_form_editor_field_title()
    {
        return 'Box';
    }

    public function add_button($field_groups)
    {
        $field_groups = $this->maybe_add_field_group($field_groups);

        return parent::add_button($field_groups);
    }

    public function maybe_add_field_group($field_groups)
    {
        foreach ($field_groups as $field_group) {
            if ('wpcp_group' == $field_group['name']) {
                return $field_groups;
            }
        }

        $field_groups[] = [
            'name' => 'wpcp_group',
            'label' => 'WP Cloud Plugins Fields',
            'fields' => [],
        ];

        return $field_groups;
    }

    public function get_form_editor_button()
    {
        return [
            'group' => 'wpcp_group',
            'text' => $this->get_form_editor_field_title(),
        ];
    }

    public function get_form_editor_field_icon()
    {
        return 'gform-icon--upload';
    }

    public function get_form_editor_field_description()
    {
        return esc_attr__('Let users attach files to this form. The files will be stored in the cloud', 'wpcloudplugins');
    }

    public function get_form_editor_field_settings()
    {
        return [
            'conditional_logic_field_setting',
            'error_message_setting',
            'label_setting',
            'label_placement_setting',
            'admin_label_setting',
            'rules_setting',
            'visibility_setting',
            'duplicate_setting',
            'description_setting',
            'css_class_setting',
            'letsbox_setting',
        ];
    }

    public function get_value_default()
    {
        return $this->is_form_editor() ? $this->defaultValue : \GFCommon::replace_variables_prepopulate($this->defaultValue);
    }

    public function is_conditional_logic_supported()
    {
        return false;
    }

    public function get_field_input($form, $value = '', $entry = null)
    {
        $form_id = $form['id'];
        $is_entry_detail = $this->is_entry_detail();
        $id = (int) $this->id;

        if ($is_entry_detail) {
            $input = "<input type='hidden' id='input_{$id}' name='input_{$id}' value='{$value}' />";
            $input .= $this->renderUploadedFiles(html_entity_decode($value), true);

            return $input.'<br/><em>('.esc_html__('This field is not editable', 'wpcloudplugins').')</em>';
        }

        if ($this->is_form_editor()) {
            return $this->get_placeholder();
        }

        $prefill = '';
        if (isset($_REQUEST['input_'.$id])) {
            $prefill = stripslashes($_REQUEST['input_'.$id]);
        } elseif (isset($entry[$id])) {
            $prefill = $entry[$id];
        }

        $input = do_shortcode($this->LetsBoxShortcode);
        $input .= "<input type='hidden' name='input_".$id."' id='input_".$form_id.'_'.$id."'  class='fileupload-filelist fileupload-input-filelist' value='{$prefill}'>";

        return $input;
    }

    public function get_placeholder()
    {
        if (!empty($this->LetsBoxShortcode)) {
            return do_shortcode($this->LetsBoxShortcode);
        }

        ob_start(); ?>
<div id="LetsBox" class="light upload">
    <div class="LetsBox upload" style="width: 100%;">
        <div class="fileupload-box -is-formfield -is-required -has-files -placeholder" style="width:100%;max-width:100%;"">
                    <!-- FORM ELEMENTS -->
                    <div class=" fileupload-form">
            <!-- END FORM ELEMENTS -->

            <!-- UPLOAD BOX HEADER -->
            <div class="fileupload-header">
                <div class="fileupload-header-title">
                    <div class="fileupload-empty">
                        <div class="fileupload-header-text-title upload-add-file"><?php esc_html_e('Add your file', 'wpcloudplugins'); ?></div>
                        <div class="fileupload-header-text-subtitle upload-add-folder"><a><?php esc_html_e('Or select a folder', 'wpcloudplugins'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END UPLOAD BOX HEADER -->

        </div>
    </div>
</div>
<?php
        return ob_get_clean();
    }

    public function validate($value, $form)
    {
        if (false === $this->isRequired) {
            return;
        }

        // Get information uploaded files from hidden input
        $attached_files = json_decode($value);

        if (empty($attached_files)) {
            $this->failed_validation = true;

            if (!empty($this->errorMessage)) {
                $this->validation_message = $this->errorMessage;
            } else {
                $this->validation_message = esc_html__('This field is required. Please upload your files.', 'gravityforms');
            }
        }
    }

    public function get_value_merge_tag($value, $input_id, $entry, $form, $modifier, $raw_value, $url_encode, $esc_html, $format, $nl2br)
    {
        return $this->renderUploadedFiles(html_entity_decode($value), 'html' === $format);
    }

    public function get_value_entry_detail($value, $currency = '', $use_text = false, $format = 'html', $media = 'screen')
    {
        return $this->renderUploadedFiles(html_entity_decode($value), 'html' === $format);
    }

    public function get_value_entry_list($value, $entry, $field_id, $columns, $form)
    {
        if (!empty($value)) {
            return $this->renderUploadedFiles(html_entity_decode($value));
        }
    }

    public function get_value_export($entry, $input_id = '', $use_text = false, $is_csv = false)
    {
        $value = rgar($entry, $input_id);

        return $this->renderUploadedFiles(html_entity_decode($value), false);
    }

    public function renderUploadedFiles($data, $ashtml = true)
    {
        return apply_filters('letsbox_render_formfield_data', $data, $ashtml, $this);
    }

    public function get_field_container_tag($form)
    {
        if (\GFCommon::is_legacy_markup_enabled($form)) {
            return parent::get_field_container_tag($form);
        }

        return 'fieldset';
    }
}

\GFForms::include_addon_framework();
$GF_WPCP_AddOn = new GF_WPCP_AddOn();

\GF_Fields::register(new GF_WPCP_Field());
