<?php

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

    /**
     * @var \TheLion\LetsBox\Client
     */
    private $_client;

    /**
     * @var \TheLion\LetsBox\Processor
     */
    private $_processor;

    public function __construct(Processor $_processor = null)
    {
        $this->_client = $_processor->get_client();
        $this->_processor = $_processor;
    }

    public function do_zip()
    {
        $this->initialize();
        $this->index();
        $this->create();

        exit();
    }

    public function initialize()
    {
        // Check if file/folder is cached and still valid
        $cachedfolder = $this->get_client()->get_folder();

        if (false === $cachedfolder || false === $cachedfolder['folder']) {
            return new \WP_Error('broke', esc_html__("Requested directory isn't allowed", 'wpcloudplugins'));
        }

        $folder = $cachedfolder['folder']->get_entry();

        // Check if entry is allowed
        if (!$this->get_processor()->_is_entry_authorized($cachedfolder['folder'])) {
            return new \WP_Error('broke', esc_html__("Requested directory isn't allowed", 'wpcloudplugins'));
        }

        $this->zip_name = basename($folder->get_name()).'_'.time();

        if (isset($_REQUEST['files']) && 1 === count($_REQUEST['files'])) {
            $single_entry = $this->get_client()->get_entry($_REQUEST['files'][0]);
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
        $zip_request = $this->get_app()->get_client()->downloadZip($items, $this->zip_name);

        if (null === $zip_request->getDownloadUrl()) {
            exit();
        }
        // Send email if needed
        if ('1' === $this->get_processor()->get_shortcode_option('notificationdownload')) {
            $this->get_processor()->send_notification_email('download', $this->entries);
        }

        // Download Zip Hook
        do_action('letsbox_download_zip', $this->entries);

        // Redirect to ZIP download
        header('Location: '.$zip_request->getDownloadUrl());

        exit();
    }

    public function index()
    {
        $requested_ids = [$this->get_processor()->get_requested_entry()];

        if (isset($_REQUEST['files'])) {
            $requested_ids = $_REQUEST['files'];
        }

        $is_shortcode_filtering = $this->get_processor()->is_filtering_entries();

        foreach ($requested_ids as $fileid) {
            $cached_entry = $this->get_client()->get_entry($fileid);

            if (false === $cached_entry) {
                continue;
            }

            $entry = $cached_entry->get_entry();

            do_action('letsbox_log_event', 'letsbox_downloaded_entry', $cached_entry, ['as_zip' => true]);

            if ($is_shortcode_filtering && $entry->is_dir()) {
                $entries_in_dir = $this->get_client()->_get_folder_recursive($cached_entry);
                $this->entries = array_merge($this->entries, $entries_in_dir);
            } else {
                $this->entries[] = $cached_entry;
            }
        }
    }

    /**
     * @return \TheLion\LetsBox\Processor
     */
    public function get_processor()
    {
        return $this->_processor;
    }

    /**
     * @return \TheLion\LetsBox\Client
     */
    public function get_client()
    {
        return $this->_client;
    }

    /**
     * @return \TheLion\LetsBox\App
     */
    public function get_app()
    {
        return $this->get_processor()->get_app();
    }
}
