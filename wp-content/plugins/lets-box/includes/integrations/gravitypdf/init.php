<?php

namespace TheLion\LetsBox\Integrations;

use TheLion\LetsBox\Accounts;
use TheLion\LetsBox\API;
use TheLion\LetsBox\App;
use TheLion\LetsBox\Client;
use TheLion\LetsBox\Processor;

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

        $main_account = Accounts::instance()->get_primary_account();

        $account_id = '';
        if (!empty($main_account)) {
            $account_id = $main_account->get_id();
        }

        $fields['letsbox_save_to_account_id'] = [
            'id' => 'letsbox_save_to_account_id',
            'name' => '[BOX] Account ID',
            'desc' => 'Account ID where the PDFs need to be stored. E.g. <code>'.$account_id.'</code>. Or use <code>%upload_account_id%</code> for the Account ID for the upload location of the plugin Upload Box field.',
            'type' => 'text',
            'std' => $account_id,
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
            'name' => $filename,
            'size' => filesize($pdf_path),
        ];

        if (!isset($settings['letsbox_save_to_account_id'])) {
            // Fall back for older PDF configurations
            $settings['letsbox_save_to_account_id'] = Accounts::instance()->get_primary_account()->get_id();
        }

        // Placeholders
        list($upload_account_id, $upload_folder_id) = $this->get_upload_location($entry, $form);

        if (false !== strpos($settings['letsbox_save_to_account_id'], '%upload_account_id%')) {
            $settings['letsbox_save_to_account_id'] = $upload_account_id;
        }

        if (false !== strpos($settings['letsbox_save_to_box_id'], '%upload_folder_id%')) {
            $settings['letsbox_save_to_box_id'] = $upload_folder_id;
        }

        $account_id = apply_filters('letsbox_gravitypdf_set_account_id', $settings['letsbox_save_to_account_id'], $settings, $entry, $form, Processor::instance());
        $folder_id = apply_filters('letsbox_gravitypdf_set_folder_id', $settings['letsbox_save_to_box_id'], $settings, $entry, $form, Processor::instance());

        $requested_account = Accounts::instance()->get_account_by_id($account_id);
        if (null !== $requested_account) {
            App::set_current_account($requested_account);
        } else {
            error_log(sprintf("[WP Cloud Plugin message]: Box account (ID: %s) as it isn't linked with the plugin", $account_id));

            exit;
        }

        try {
            $uploaded_entry = API::upload_file($file, $folder_id);
        } catch (\Exception $ex) {
            return false;
        }

        // Add url to PDF file in cloud
        $pdfs = \GPDFAPI::get_entry_pdfs($entry['id']);

        foreach ($pdfs as $pid => $pdf) {
            if ('Yes' === $pdf['letsbox_save_to_box']) {
                $pdf['letsbox_pdf_url'] = 'https://app.box.com/'.($uploaded_entry->get_entry()->is_dir() ? 'folder' : 'file').'/'.$uploaded_entry->get_id();
                \GPDFAPI::update_pdf($form['id'], $pid, $pdf);
            }
        }
    }

    public function get_upload_location($entry, $form)
    {
        $account_id = '';
        $folder_id = '';

        if (!is_array($form['fields'])) {
            return [$account_id, $folder_id];
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

                $account_id = $first_entry->account_id;
                $requested_account = Accounts::instance()->get_account_by_id($account_id);
                App::set_current_account($requested_account);

                $cached_entry = Client::instance()->get_entry($first_entry->hash, false);
                $parents = $cached_entry->get_parents();
                $folder_id = reset($parents)->get_id();
            }
        }

        return [$account_id, $folder_id];
    }
}

new GravityPDF();