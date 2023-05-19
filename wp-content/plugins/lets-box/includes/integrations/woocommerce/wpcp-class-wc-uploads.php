<?php

namespace TheLion\LetsBox\Integrations;

use TheLion\LetsBox\Client;
use TheLion\LetsBox\Processor;

class WooCommerce_Uploads
{
    public function __construct()
    {
        // Add Tabs & Content to Product Edit Page
        add_action('admin_enqueue_scripts', [$this, 'add_scripts']);
        add_filter('product_type_options', [$this, 'add_uploadable_product_option']);
        add_filter('woocommerce_product_data_tabs', [$this, 'add_product_data_tab']);
        add_action('woocommerce_product_data_panels', [$this, 'add_product_data_tab_content']);
        add_action('woocommerce_process_product_meta_simple', [$this, 'save_product_data_fields']);
        add_action('woocommerce_process_product_meta_variable', [$this, 'save_product_data_fields']);
        add_action('woocommerce_ajax_save_product_variations', [$this, 'save_product_data_fields']);
        add_action('woocommerce_process_product_meta_composite', [$this, 'save_product_data_fields']);
        add_action('woocommerce_process_product_meta_booking', [$this, 'save_product_data_fields']);

        // Add Upload button to my Order Table
        add_filter('woocommerce_my_account_my_orders_actions', [$this, 'add_orders_column_actions'], 10, 2);

        // Add Upload Box to Order Page
        add_action('woocommerce_order_item_meta_end', [$this, 'render_upload_field'], 10, 4);

        // Add Upload Box to Admin Order Page
        add_action('woocommerce_admin_order_item_headers', [$this, 'admin_order_item_headers'], 10, 1);
        add_action('woocommerce_admin_order_item_values', [$this, 'admin_order_item_values'], 10, 3);

        // Add link to upload box in the Thank You text
        add_filter('woocommerce_thankyou_order_received_text', [$this, 'change_order_received_text'], 10, 2);

        // Add Order note when uploading files
        add_action('letsbox_upload_post_process', [$this, 'add_order_note'], 10, 1);

        // AJAX calls to load the list of uploaded files
        add_action('letsbox_start_process', [$this, 'get_item_details'], 10, 2);
    }

    public function add_order_note($_uploaded_entries)
    {
        // Grab the Order/Product data from the shortcode
        $order_id = Processor::instance()->get_shortcode_option('wc_order_id');
        $product_id = Processor::instance()->get_shortcode_option('wc_product_id');

        if (empty($order_id) || empty($product_id)) {
            return;
        }

        $order = new \WC_Order($order_id);

        if (empty($order)) {
            return;
        }

        $product = wc_get_product($product_id);

        // Make sure that we are working with an array
        $uploaded_entries = [];
        if (!is_array($_uploaded_entries)) {
            $uploaded_entries[] = $_uploaded_entries;
        } else {
            $uploaded_entries = $_uploaded_entries;
        }

        // Build the Order note
        $order_note = sprintf(esc_html__('%d file(s) uploaded for product', 'wpcloudplugins'), count((array) $uploaded_entries)).' <strong>'.$product->get_title().'</strong>:';
        $order_note .= '<br/><br/><ul>';

        foreach ($uploaded_entries as $cachedentry) {
            $link = 'javascript:void(0);';
            $name = $cachedentry->get_entry()->get_name();
            $size = \TheLion\LetsBox\Helpers::bytes_to_size_1024($cachedentry->get_entry()->get_size());

            $order_note .= '<li><a href="'.urldecode($link).'">'.$name.'</a> ('.$size.')</li>';
        }

        $order_note .= '</ul>';

        // Add the note
        $note = [
            'note' => $order_note,
            'is_customer_note' => false,
            'added_by_user' => false,
        ];

        $note = apply_filters('letsbox_woocommerce_add_order_note', $note, $uploaded_entries, $order, $product, $this);
        $order->add_order_note($note['note'], $note['is_customer_note'], $note['added_by_user']);

        // Save the data
        $order->save();
    }

    /**
     * Add link to upload box in the Thank You text.
     *
     * @param string    $thank_you_text
     * @param \WC_Order $order
     *
     * @return string
     */
    public function change_order_received_text($thank_you_text, $order)
    {
        if (false === $this->requires_order_uploads($order)) {
            return $thank_you_text;
        }

        $order_url = $order->get_view_order_url().'#wpcp-uploads';
        $custom_text = ' '.sprintf(esc_html__('You can now %sstart uploading your documents%s', 'wpcloudplugins'), '<a href="'.$order_url.'">', '</a>').'.';
        $thank_you_text .= apply_filters('letsbox_woocommerce_thank_you_text', $custom_text, $order, $this);

        return $thank_you_text;
    }

    /**
     * Add new Product Type to the Product Data Meta Box.
     *
     * @param array $product_type_options
     *
     * @return array
     */
    public function add_uploadable_product_option($product_type_options)
    {
        $product_type_options['uploadable'] = [
            'id' => '_uploadable',
            'wrapper_class' => 'show_if_simple show_if_variable show_if_booking',
            'label' => esc_html__('Uploads', 'wpcloudplugins'),
            'description' => esc_html__('Allows your customers to upload files when ordering this product.', 'wpcloudplugins'),
            'default' => 'no',
        ];

        return $product_type_options;
    }

    /**
     * Add new Data Tab to the Product Data Meta Box.
     *
     * @param array $product_data_tabs
     *
     * @return array
     */
    public function add_product_data_tab($product_data_tabs)
    {
        $product_data_tabs['cloud-uploads-box'] = [
            'label' => sprintf(esc_html__('Upload to %s', 'wpcloudplugins'), 'Box'),
            'target' => 'cloud_uploads_data_box',
            'class' => ['show_if_uploadable'],
        ];

        return $product_data_tabs;
    }

    /**
     * Add the content of the new Data Tab.
     */
    public function add_product_data_tab_content()
    {
        global $post;

        $default_shortcode = '[letsbox mode="files" viewrole="all" userfolders="auto" downloadrole="all" upload="1" uploadrole="all" rename="1" renamefilesrole="all" renamefoldersrole="all" editdescription="1" editdescriptionrole="all" delete="1" deleterole="all" deletefoldersrole="all" viewuserfoldersrole="none" search="0" showbreadcrumb="0"]';
        $shortcode = get_post_meta($post->ID, 'letsbox_upload_box_shortcode', true); ?>
<div id='cloud_uploads_data_box' class='panel woocommerce_options_panel' style="display:none">
    <div class="cloud_uploads_data_panel options_group">
        <?php
            woocommerce_wp_checkbox(
                [
                    'id' => 'letsbox_upload_box',
                    'label' => sprintf(esc_html__('Upload to %s', 'wpcloudplugins'), 'Box'),
                ]
            ); ?>
        <div class="show_if_letsbox_upload_box">
            <h4><?php echo 'Box '.esc_html__('Upload Box Settings', 'wpcloudplugins'); ?></h4>
            <?php
                                                                                                                      $default_box_title = esc_html__('Order #', 'woocommerce').' %wc_order_id% | %wc_product_name% -'.esc_html__('Upload documents', 'wpcloudplugins');
        $box_title = get_post_meta($post->ID, 'letsbox_upload_box_title', true);

        woocommerce_wp_text_input(
            [
                'id' => 'letsbox_upload_box_title',
                'label' => esc_html__('Title Upload Box', 'wpcloudplugins'),
                'placeholder' => $default_box_title,
                'desc_tip' => true,
                'description' => ''.esc_html__('Enter the title for the upload box', 'wpcloudplugins').'. '.sprintf(esc_html__('See %s for available placeholders', 'wpcloudplugins'), '<strong><u>'.esc_html__('Upload Folder Name', 'wpcloudplugins').'</u></strong>'),
                'value' => empty($box_title) ? $default_box_title : $box_title,
            ]
        );

        $default_box_description = '';
        $box_description = get_post_meta($post->ID, 'letsbox_upload_box_description', true);

        woocommerce_wp_textarea_input(
            [
                'id' => 'letsbox_upload_box_description',
                'label' => esc_html__('Description Upload Box', 'wpcloudplugins'),
                'placeholder' => $default_box_description,
                'desc_tip' => true,
                'description' => esc_html__('Enter a short description of what the customer needs to upload', 'wpcloudplugins').'. '.sprintf(esc_html__('See %s for available placeholders', 'wpcloudplugins'), '<strong><u>'.esc_html__('Upload Folder Name', 'wpcloudplugins').'</u></strong>').'. '.esc_html__('Shortcodes are supported', 'wpcloudplugins').'.',
                'value' => empty($box_description) ? $default_box_description : $box_description,
            ]
        );

        $default_box_button_text = esc_html__('Upload documents', 'wpcloudplugins');
        $box_button_text = get_post_meta($post->ID, 'letsbox_upload_box_button_text', true);

        woocommerce_wp_text_input(
            [
                'id' => 'letsbox_upload_box_button_text',
                'label' => esc_html__('Upload Button Text', 'wpcloudplugins'),
                'placeholder' => $default_box_button_text,
                'desc_tip' => true,
                'description' => esc_html__('Enter the text for the upload button.', 'wpcloudplugins').' '.sprintf(esc_html__('See %s for available placeholders', 'wpcloudplugins'), '<strong><u>'.esc_html__('Upload Folder Name', 'wpcloudplugins').'</u></strong>').'. '.esc_html__('Shortcodes are supported', 'wpcloudplugins').'.',
                'value' => empty($box_button_text) ? $default_box_button_text : $box_button_text,
            ]
        );

        ?>

            <p class="form-field letsbox_upload_folder ">
                <label for="letsbox_upload_folder">Upload Box</label>
                <a href="#TB_inline?height=600&width=1024&amp;inlineId=lb-embedded" class="button wpcp-insert-box-shortcode LetsBox-shortcodegenerator" style="float:none"><?php esc_html_e('Build your Upload Box', 'wpcloudplugins'); ?></a>
                <a href="javascript:void(0)" role="link" class="" style="float:none" onclick="jQuery('#letsbox_upload_box_shortcode').fadeToggle()"><?php esc_html_e('Edit Shortcode Manually', 'wpcloudplugins'); ?></a>
                <br /><br />
                <textarea class="long" style="display:none" name="letsbox_upload_box_shortcode" id="letsbox_upload_box_shortcode" placeholder="<?php echo $default_shortcode; ?>" rows="3" cols="20"><?php echo (empty($shortcode)) ? $default_shortcode : $shortcode; ?></textarea>
            </p>

            <?php
              $default_folder_template = '%wc_order_id% (%user_email%)/%wc_product_name%';
        $folder_template = get_post_meta($post->ID, 'letsbox_upload_box_folder_template', true);

        woocommerce_wp_text_input(
            [
                'id' => 'letsbox_upload_box_folder_template',
                'label' => esc_html__('Upload Folder Name', 'wpcloudplugins'),
                'description' => '<br><br>'.esc_html__('Unique folder name where the uploads should be stored. Make sure that Private Folder feature is enabled in the shortcode', 'wpcloudplugins').'. '.sprintf(esc_html__('Available placeholders: %s', 'wpcloudplugins'), '<code>%wc_order_id%</code>, <code>%wc_order_date_created%</code>, <code>%wc_order_quantity%</code>, <code>%wc_product_id%</code>, <code>%wc_product_sku%</code>, <code>%wc_product_name%</code>, <code>%wc_item_id%</code>, <code>%wc_item_quantity%</code>, <code>%user_login%</code>, <code>%user_email%</code>, <code>%display_name%</code>, <code>%ID%</code>, <code>%user_role%</code>, <code>%usermeta_{key}%</code>, <code>%date_{date_format}%</code>, <code>%yyyy-mm-dd%</code>, <code>%directory_separator%</code>'),
                'desc_tip' => false,
                'placeholder' => $default_folder_template,
                'value' => empty($folder_template) ? $default_folder_template : $folder_template,
            ]
        );

        $letsbox_upload_box_active_on_status = get_post_meta($post->ID, 'letsbox_upload_box_active_on_status', true);
        if (empty($letsbox_upload_box_active_on_status)) {
            $letsbox_upload_box_active_on_status = ['wc-pending', 'wc-processing'];
        }

        $this->woocommerce_wp_multi_checkbox([
            'id' => 'letsbox_upload_box_active_on_status',
            'name' => 'letsbox_upload_box_active_on_status[]',
            'label' => esc_html__(''
                    .'Show when Order is', 'woocommerce'),
            'options' => wc_get_order_statuses(),
            'value' => $letsbox_upload_box_active_on_status,
        ]); ?>
        </div>
    </div>
</div><?php
    }

    /**
     * New Multi Checkbox field for woocommerce backend.
     *
     * @param mixed $field
     */
    public function woocommerce_wp_multi_checkbox($field)
    {
        global $thepostid, $post;

        $thepostid = empty($thepostid) ? $post->ID : $thepostid;
        $field['class'] = $field['class'] ?? 'select short';
        $field['style'] = $field['style'] ?? '';
        $field['wrapper_class'] = $field['wrapper_class'] ?? '';
        $field['value'] = $field['value'] ?? get_post_meta($thepostid, $field['id'], true);
        $field['cbvalue'] = $field['cbvalue'] ?? 'yes';
        $field['name'] = $field['name'] ?? $field['id'];
        $field['desc_tip'] = $field['desc_tip'] ?? false;

        echo '<fieldset class="form-field '.esc_attr($field['id']).'_field '.esc_attr($field['wrapper_class']).'">
    <legend>'.wp_kses_post($field['label']).'</legend>';

        if (!empty($field['description']) && false !== $field['desc_tip']) {
            echo wc_help_tip($field['description']);
        }

        echo '<ul class="wc-radios">';

        foreach ($field['options'] as $key => $value) {
            echo '<li><label><input type="checkbox" class="'.esc_attr($field['class']).'" style="'.esc_attr($field['style']).'" name="'.esc_attr($field['name']).'" id="'.esc_attr($field['id']).'" value="'.esc_attr($key).'" '.(in_array($key, $field['value']) ? 'checked="checked"' : '').' /> '.esc_html($value).'</label></li>';
        }
        echo '</ul>';

        if (!empty($field['description']) && false === $field['desc_tip']) {
            echo '<span class="description">'.wp_kses_post($field['description']).'</span>';
        }

        echo '</fieldset>';
    }

    /**
     * Add the scripts and styles required for the new Data Tab.
     */
    public function add_scripts()
    {
        $current_screen = get_current_screen();

        if (!in_array($current_screen->id, ['product', 'shop_order'])) {
            return;
        }

        wp_register_style('letsbox-woocommerce', plugins_url('backend.css', __FILE__), LETSBOX_VERSION);
        wp_register_script('letsbox-woocommerce', plugins_url('backend.js', __FILE__), ['jquery'], LETSBOX_VERSION);

        // register translations
        $translation_array = [
            'choose_from' => sprintf(esc_html__('Add File', 'wpcloudplugins'), 'Box'),
            'download_url' => '?action=letsbox-wc-direct-download&id=',
            'file_browser_url' => LETSBOX_ADMIN_URL.'?action=letsbox-getwoocommercepopup',
            'wcpd_url' => LETSBOX_ADMIN_URL.'?action=letsbox-wcpd-direct-download&id=',
        ];

        wp_localize_script('letsbox-woocommerce', 'letsbox_woocommerce_translation', $translation_array);
    }

    /**
     * Save the new added input fields properly.
     *
     * @param int $post_id
     */
    public function save_product_data_fields($post_id)
    {
        $is_uploadable = isset($_POST['_uploadable']) ? 'yes' : 'no';
        update_post_meta($post_id, '_uploadable', $is_uploadable);

        $letsbox_upload_box = isset($_POST['letsbox_upload_box']) ? 'yes' : 'no';
        update_post_meta($post_id, 'letsbox_upload_box', $letsbox_upload_box);

        if (isset($_POST['letsbox_upload_box_title'])) {
            update_post_meta($post_id, 'letsbox_upload_box_title', $_POST['letsbox_upload_box_title']);
        }

        if (isset($_POST['letsbox_upload_box_description'])) {
            update_post_meta($post_id, 'letsbox_upload_box_description', $_POST['letsbox_upload_box_description']);
        }

        if (isset($_POST['letsbox_upload_box_button_text'])) {
            update_post_meta($post_id, 'letsbox_upload_box_button_text', $_POST['letsbox_upload_box_button_text']);
        }

        if (isset($_POST['letsbox_upload_box_shortcode'])) {
            update_post_meta($post_id, 'letsbox_upload_box_shortcode', $_POST['letsbox_upload_box_shortcode']);
        }

        if (isset($_POST['letsbox_upload_box_folder_template'])) {
            update_post_meta($post_id, 'letsbox_upload_box_folder_template', $_POST['letsbox_upload_box_folder_template']);
        }

        if (isset($_POST['letsbox_upload_box_active_on_status'])) {
            $post_data = $_POST['letsbox_upload_box_active_on_status'];
            // Data sanitization
            $sanitize_data = [];
            if (is_array($post_data) && sizeof($post_data) > 0) {
                foreach ($post_data as $value) {
                    $sanitize_data[] = esc_attr($value);
                }
            }
            update_post_meta($post_id, 'letsbox_upload_box_active_on_status', $sanitize_data);
        } else {
            update_post_meta($post_id, 'letsbox_upload_box_active_on_status', ['wc-pending', 'wc-processing']);
        }
    }

    /**
     * Add an 'Upload' Action to the Order Table.
     *
     * @param array $actions
     *
     * @return array
     */
    public function add_orders_column_actions($actions, \WC_Order $order)
    {
        $box_button_text = esc_html__('Upload documents', 'wpcloudplugins');

        if ($this->requires_order_uploads($order)) {
            foreach ($order->get_items() as $order_item) {
                $product = $this->get_product($order_item);

                $requires_upload = $this->requires_product_uploads($product, $order);

                if ($requires_upload) {
                    if ($this->is_product_variation($product)) {
                        $product = wc_get_product($product->get_parent_id());
                    }

                    $box_button_text = get_post_meta($product->get_id(), 'letsbox_upload_box_button_text', true);

                    break;
                }
            }

            $actions['upload'] = [
                'url' => $order->get_view_order_url().'#wpcp-uploads',
                'name' => $box_button_text,
            ];
        }

        return $actions;
    }

    /**
     * Add a custom column on the Admin Order Page.
     *
     * @param mixed $order
     */
    public function admin_order_item_headers($order)
    {
        if (false === $this->requires_order_uploads($order)) {
            return false;
        }

        // set the column name
        $column_name = esc_html__('Uploaded documents', 'wpcloudplugins');

        // display the column name
        echo '<th>'.$column_name.'</th>';
    }

    /**
     * Add the value for the custom column on the Admin Order Page.
     *
     * @param mixed      $_product
     * @param mixed      $item
     * @param null|mixed $item_id
     */
    public function admin_order_item_values($_product, $item, $item_id = null)
    {
        if (false === $this->requires_order_uploads($item->get_order())) {
            return false;
        }

        if (false === $this->requires_product_uploads($_product, $item->get_order())) {
            echo '<td></td>';

            return;
        }

        echo '<td>';
        echo $this->render_upload_field($item->get_id(), $item, $item->get_order(), null);
        echo '</td>';
    }

    /*
     * Render the Upload Box on the Order View.
     *
     * @param mixed $item_id
     * @param mixed $item
     * @param mixed $order
     * @param bool  $plain_text
     */

    public function render_upload_field($item_id, $item, $order, $plain_text = false)
    {
        $originial_product = $this->get_product($item);
        if (false === $this->requires_product_uploads($originial_product, $order)) {
            return;
        }

        wp_register_style('letsbox-woocommerce-frontend-css', plugins_url('frontend.css', __FILE__), [], LETSBOX_VERSION);
        wp_enqueue_style('letsbox-woocommerce-frontend-css');

        wp_register_script('letsbox-woocommerce-frontend', plugins_url('frontend.js', __FILE__), ['jquery'], LETSBOX_VERSION);
        wp_enqueue_script('letsbox-woocommerce-frontend');

        /** Select the product that contains the information * */
        $meta_product = $originial_product;
        if ($this->is_product_variation($originial_product)) {
            $meta_product = wc_get_product($originial_product->get_parent_id());
        }

        $box_title = apply_filters('letsbox_woocommerce_upload_box_title', get_post_meta($meta_product->get_id(), 'letsbox_upload_box_title', true), $order, $item, $this);
        $box_description = get_post_meta($meta_product->get_id(), 'letsbox_upload_box_description', true);
        $box_button_text = get_post_meta($meta_product->get_id(), 'letsbox_upload_box_button_text', true);
        $shortcode = get_post_meta($meta_product->get_id(), 'letsbox_upload_box_shortcode', true);
        $folder_template = get_post_meta($meta_product->get_id(), 'letsbox_upload_box_folder_template', true);
        $upload_active_on = get_post_meta($meta_product->get_id(), 'letsbox_upload_box_active_on_status', true);
        if (empty($upload_active_on)) {
            $upload_active_on = ['wc-pending', 'wc-processing'];
        }
        $upload_active = in_array('wc-'.$order->get_status(), $upload_active_on);

        if (empty($box_button_text)) {
            $box_button_text = esc_html__('Upload documents', 'wpcloudplugins');
        }

        // Don't include upload box in email notifications
        $is_sending_mail = doing_action('woocommerce_email_order_details');

        if ($is_sending_mail || (!is_wc_endpoint_url() && !is_admin())) {
            $order_url = $order->get_view_order_url()."#wpcp-letsbox-uploads-{$item_id}";
            echo '<br/><small>'.sprintf(esc_html__('You can uploading your documents on the %sorder page%s', 'wpcloudplugins'), '<a href="'.$order_url.'">', '</a>').'.</small>';

            return;
        }

        $shortcode_params = shortcode_parse_atts($shortcode);
        $shortcode_params['userfoldernametemplate'] = $this->set_placeholders($folder_template, $item, $order, $originial_product);
        $shortcode_params['wc_order_id'] = $order->get_id();
        $shortcode_params['wc_product_id'] = $originial_product->get_id();
        $shortcode_params['wc_item_id'] = $item->get_id();
        $shortcode_params['maxheight'] = '300px';

        // When Upload box isn't active, change it to a view only file browser
        if (false === $upload_active) {
            $shortcode_params['mode'] = 'files';
            $shortcode_params['upload'] = '0';
            $shortcode_params['delete'] = '0';
            $shortcode_params['rename'] = '0';
            $shortcode_params['candownloadzip'] = '1';
            $shortcode_params['editdescription'] = '0';
        }

        $show_box = apply_filters('letsbox_woocommerce_show_upload_field', true, $order, $originial_product, $this);

        $is_admin_page = is_admin();
        if ($is_admin_page) {
            // Always show the File Browser mode in the Dashboard

            $shortcode_params['showbreadcrumb'] = '1';
            $shortcode_params['mode'] = 'files';
            $shortcode_params['candownloadzip'] = '1';
            $shortcode_params['viewuserfoldersrole'] = 'none';

            // Meta Box is located inside Form tag, so force the plugin to start the update
            $shortcode_params['class'] = (isset($shortcode_params['class']) ? $shortcode_params['class'].' auto_upload' : 'auto_upload');

            $show_box = true;
        }

        if ($show_box) {
            echo "<div id='wpcp-letsbox-uploads-{$item_id}' class='wpcp-letsbox wpcp-upload-container' data-item-id='{$item_id}'>";

            // Upload button
            echo "<a class='woocommerce-button button wpcp-wc-open-box'><i class='eva eva-attach-2 eva-lg'></i> ".(($is_admin_page) ? esc_html__('View documents', 'wpcloudplugins') : $box_button_text).'</a>';

            echo '<div class="woocommerce-order-upload-box" style="display:none;">';

            do_action('letsbox_woocommerce_before_render_upload_field', $order, $originial_product, $this);

            echo '<h2 id="uploads">'.$this->set_placeholders($box_title, $item, $order, $originial_product).'</h2>';

            if (!empty($box_description)) {
                echo do_shortcode('<p>'.$this->set_placeholders($box_description, $item, $order, $originial_product).'</p>');
            }

            // Don't show the upload box when there isn't select a root folder
            if (empty($shortcode_params['dir']) && (!empty($shortcode_params['userfolders']) && 'auto' === $shortcode_params['userfolders'])) {
                esc_html_e('Please configure the upload location for this product.', 'wpcloudplugins');
                echo '</div>';

                return;
            }

            echo Processor::instance()->create_from_shortcode($shortcode_params);

            do_action('letsbox_woocommerce_after_render_upload_field', $order, $originial_product, $this);
            echo '</div>';

            // Placeholder for list of uploaded files in folder. Content is loaded dynamically via AJAX
            echo "<ul class='wpcp-uploads-list ".($is_admin_page ? '' : 'wpcp-uploads-list-small')."'></ul>";

            echo '</div>';
        }
    }

    /**
     * Checks if the order uses this upload functionality.
     *
     * @param \WC_Order $order
     *
     * @return bool
     */
    public function requires_order_uploads($order)
    {
        if (false === ($order instanceof \WC_Order)) {
            return false;
        }

        foreach ($order->get_items() as $order_item) {
            $product = $this->get_product($order_item);
            $requires_upload = $this->requires_product_uploads($product, $order);

            if ($requires_upload) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the product uses this upload functionality.
     *
     * @param \WC_Product $product
     * @param null|mixed  $order
     *
     * @return bool
     */
    public function requires_product_uploads($product = null, $order = null)
    {
        if (empty($product) || !($product instanceof \WC_Product)) {
            return false;
        }

        if ($this->is_product_variation($product)) {
            $product = wc_get_product($product->get_parent_id());
        }

        $_uploadable = get_post_meta($product->get_id(), '_uploadable', true);
        $_letsbox_upload_box = get_post_meta($product->get_id(), 'letsbox_upload_box', true);

        $upload_active_on = get_post_meta($product->get_id(), 'letsbox_upload_box_active_on_status', true);
        if (empty($upload_active_on)) {
            $upload_active_on = ['wc-pending', 'wc-processing'];
        }
        $upload_active = in_array('wc-'.$order->get_status(), $upload_active_on);

        if (\is_admin()) {
            $current_screen = \get_current_screen();
            if (!empty($current_screen) && in_array($current_screen->post_type, ['shop_order'])) {
                $upload_active = true;
            } elseif (isset($_REQUEST['type']) || 'wc-item-details' !== $_REQUEST['type']) {
                $upload_active = true;
            }
        }

        $show_upload_box = apply_filters('letsbox_woocommerce_show_upload_field', $upload_active, $order, $product, $this);

        if ('yes' === $_uploadable && 'yes' === $_letsbox_upload_box && $show_upload_box) {
            return true;
        }

        return false;
    }

    /**
     * Loads the product or its parent product in case of a variation.
     *
     * @param type $order_item
     *
     * @return \WC_Product
     */
    public function get_product($order_item)
    {
        $product = $order_item->get_product();

        if (empty($product) || !($product instanceof \WC_Product)) {
            return false;
        }

        return $product;
    }

    /**
     * Check if product is a variation
     * Upload meta data is currently only stored on the parent product.
     *
     * @param mixed $product
     *
     * @return bool
     */
    public function is_product_variation($product)
    {
        $product_type = $product->get_type();

        return 'variation' === $product_type;
    }

    /**
     * Fill the placeholders with the User/Product/Order information.
     *
     * @param string $template
     *
     * @return string
     */
    public function set_placeholders($template, \WC_Order_Item_Product $item, \WC_Order $order, \WC_Product $product)
    {
        $user = $order->get_user();

        // Guest User
        if (false === $user) {
            $user_id = $order->get_order_key();
            $user = new \stdClass();
            $user->user_login = $order->get_billing_first_name().' '.$order->get_billing_last_name();
            $user->display_name = $order->get_billing_first_name().' '.$order->get_billing_last_name();
            $user->user_firstname = $order->get_billing_first_name();
            $user->user_lastname = $order->get_billing_last_name();
            $user->user_email = $order->get_billing_email();
            $user->ID = $user_id;
            $user->user_role = esc_html__('Anonymous user', 'wpcloudplugins');
        }

        $output = \TheLion\LetsBox\Helpers::apply_placeholders(
            $template,
            Processor::instance(),
            [
                'user_data' => $user,
                'wc_order' => $order,
                'wc_product' => $product,
                'wc_item' => $item,
            ]
        );

        return apply_filters('letsbox_woocommerce_set_placeholders', $output, $template, $order, $product, $item);
    }

    public function get_item_details($action, $processor)
    {
        if ('letsbox-get-filelist' !== $action || !isset($_REQUEST['type']) || 'wc-item-details' !== $_REQUEST['type']) {
            return;
        }

        // Check if item indeed requires uploads
        $order_item_id = \sanitize_key($_REQUEST['item_id']);
        $item = new \WC_Order_Item_Product($order_item_id);

        if (false === $this->requires_order_uploads($item->get_order())) {
            echo json_encode([]);

            exit;
        }

        if (false === $this->requires_product_uploads($item->get_product(), $item->get_order())) {
            echo json_encode([]);

            exit;
        }

        // List the uploads
        try {
            $folder = Client::instance()->get_folder();
            $children = Client::instance()->get_folder_recursive_filtered($folder['folder']);
            $data = [];

            foreach ($children as $cached_node) {
                if ($cached_node->get_entry()->is_dir()) {
                    continue;
                }

                $data[$cached_node->get_id()] = trim($cached_node->get_path($processor->get_root_folder()), '/');
            }
        } catch (\Exception $ex) {
            $data = [];
        }

        $data = \apply_filters('letsbox_woocommerce_uploaded_filelist', $data, $children, $item);

        header('Content-Type: application/json; charset=utf-8');

        echo \json_encode($data);

        exit;
    }
}

new WooCommerce_Uploads();
