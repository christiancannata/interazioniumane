<?php

namespace TheLion\LetsBox\Integrations;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class GravityPDF
{
    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        if (false === get_option('gfpdf_current_version') && false === class_exists('GFPDF_Core')) {
            return;
        }

        add_action('gfpdf_post_save_pdf', [$this, 'letsbox_post_save_pdf'], 10, 5);
        add_filter('gfpdf_form_settings_advanced', [$this, 'letsbox_add_pdf_setting'], 10, 1);
    }

    /*
     * GravityPDF
     * Basic configuration in Form Settings -> PDF:
     *
     * Always Save PDF = YES
     * [BOX] Export PDF = YES
     * [BOX] Path = ID where the PDFs need to be stored
     */

    public function letsbox_add_pdf_setting($fields)
    {
        $fields['letsbox_save_to_box'] = [
            'id' => 'letsbox_save_to_box',
            'name' => '[BOX] Export PDF',
            'desc' => 'Save the created PDF to Box',
            'type' => 'radio',
            'options' => [
                'Yes' => esc_html__('Yes'),
                'No' => esc_html__('No'),
            ],
            'std' => esc_html__('No'),
        ];
        $fields['letsbox_save_to_box_id'] = [
            'id' => 'letsbox_save_to_box_id',
            'name' => '[BOX] Folder ID',
            'desc' => 'Folder ID where the PDFs need to be stored. E.g. <code>1644002612</code>. Or use <code>%upload_folder_id%</code> for the Account ID for the upload location of the plugin Upload Box field.',
            'type' => 'text',
            'std' => '',
        ];

        return $fields;
    }

    public function letsbox_post_save_pdf($pdf_path, $filename, $settings, $entry, $form)
    {
        if (!isset($settings['letsbox_save_to_box']) || 'No' === $settings['letsbox_save_to_box']) {
            return false;
        }

        $file = (object) [
            'tmp_path' => $pdf_path,
            'type' => mime_content_type($pdf_path),
            'name' => $entry['id'].'-'.$filename,
        ];

        // Placeholders
        $upload_folder_id = $this->get_upload_location($entry, $form);

        if ((false !== strpos($settings['letsbox_save_to_box_id'], '%upload_folder_id%'))) {
            $settings['letsbox_save_to_box_id'] = $upload_folder_id;
        }

        $folder_id = apply_filters('letsbox_gravitypdf_set_folder_id', $settings['letsbox_save_to_box_id'], $settings, $entry, $form, $this->get_processor());

        try {
            return $this->get_processor()->get_app()->get_client()->uploadFileToBox($file, $folder_id);
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));

            return false;
        }
    }

    public function get_upload_location($entry, $form)
    {
        $folder_id = '';

        if (!is_array($form['fields'])) {
            return $folder_id;
        }

        foreach ($form['fields'] as $field) {
            if ('letsbox' !== $field->type) {
                continue;
            }

            if (!isset($entry[$field->id])) {
                continue;
            }

            $uploadedfiles = json_decode($entry[$field->id]);

            if ((null !== $uploadedfiles) && (count((array) $uploadedfiles) > 0)) {
                $first_entry = reset($uploadedfiles);
                $cached_entry = $this->get_processor()->get_client()->get_entry($first_entry->hash, false);
                $parents = $cached_entry->get_parents();
                $folder_id = reset($parents)->get_id();
            }
        }

        return $folder_id;
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
        if (empty($this->_main)) {
            global $LetsBox;
            $this->_main = $LetsBox;
        }

        return $this->_main;
    }
}

 new GravityPDF();
