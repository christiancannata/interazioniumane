<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\LetsBox;

class Zip
{
    /**
     * Name of the zip file.
     */
    public $zip_name;

    /**
     * Files that need to be added to ZIP.
     *
     * @var \TheLion\LetsBox\CacheNode[]
     */
    public $entries = [];

    public function do_zip()
    {
        $this->initialize();
        $this->index();
        $this->create();

        exit;
    }

    public function initialize()
    {
        // Check if file/folder is cached and still valid
        $cachedfolder = Client::instance()->get_folder();

        if (false === $cachedfolder || false === $cachedfolder['folder']) {
            return new \WP_Error('broke', esc_html__("Requested directory isn't allowed", 'wpcloudplugins'));
        }

        $folder = $cachedfolder['folder']->get_entry();

        // Check if entry is allowed
        if (!Processor::instance()->_is_entry_authorized($cachedfolder['folder'])) {
            return new \WP_Error('broke', esc_html__("Requested directory isn't allowed", 'wpcloudplugins'));
        }

        $this->zip_name = basename($folder->get_name()).'_'.time();

        if (isset($_REQUEST['files']) && 1 === count($_REQUEST['files'])) {
            $single_entry = Client::instance()->get_entry($_REQUEST['files'][0]);
            $this->zip_name = basename($single_entry->get_name()).'_'.time().'.zip';
        }
    }

    public function create()
    {
        // Prepare list of items that need to be ZIP according for API call
        $items = [];

        foreach ($this->entries as $cached_entry) {
            $items[] = [
                'type' => $cached_entry->get_entry()->is_dir() ? 'folder' : 'file',
                'id' => $cached_entry->get_id(),
            ];
        }

        // Request Zip download
        $zip_request = App::instance()->get_sdk_client()->downloadZip($items, $this->zip_name);

        if (null === $zip_request->getDownloadUrl()) {
            exit;
        }
        // Send email if needed
        if ('1' === Processor::instance()->get_shortcode_option('notificationdownload')) {
            Processor::instance()->send_notification_email('download', $this->entries);
        }

        // Download Zip Hook
        do_action('letsbox_download_zip', $this->entries);

        // Redirect to ZIP download
        header('Location: '.$zip_request->getDownloadUrl());

        exit;
    }

    public function index()
    {
        $requested_ids = [Processor::instance()->get_requested_entry()];

        if (isset($_REQUEST['files'])) {
            $requested_ids = $_REQUEST['files'];
        }

        $is_shortcode_filtering = Processor::instance()->is_filtering_entries();

        foreach ($requested_ids as $fileid) {
            $cached_entry = Client::instance()->get_entry($fileid);

            if (false === $cached_entry) {
                continue;
            }

            $entry = $cached_entry->get_entry();

            do_action('letsbox_log_event', 'letsbox_downloaded_entry', $cached_entry, ['as_zip' => true]);

            if ($is_shortcode_filtering && $entry->is_dir()) {
                $entries_in_dir = Client::instance()->get_folder_recursive_filtered($cached_entry);
                $this->entries = array_merge($this->entries, $entries_in_dir);
            } else {
                $this->entries[] = $cached_entry;
            }
        }
    }
}
