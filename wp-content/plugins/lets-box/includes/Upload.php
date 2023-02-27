<?php

namespace TheLion\LetsBox;

class Upload
{
    /**
     * @var \TheLion\LetsBox\Client
     */
    private $_client;

    /**
     * @var \TheLion\LetsBox\Processor
     */
    private $_processor;

    /**
     * @var WPCP_UploadHandler
     */
    private $upload_handler;

    public function __construct(Processor $_processor = null)
    {
        $this->_client = $_processor->get_client();
        $this->_processor = $_processor;

        wp_using_ext_object_cache(false);
    }

    public function upload_pre_process()
    {
        do_action('letsbox_upload_pre_process', $this->_processor);

        foreach ($_REQUEST['files']  as $hash => $file) {
            if (!empty($file['path'])) {
                $this->create_folder_structure($file['path']);
            }
        }

        $result = ['result' => 1];

        $result = apply_filters('letsbox_upload_pre_process_result', $result, $this->_processor);

        echo json_encode($result);
    }

    public function do_upload()
    {
        // Upload File to server
        if (!class_exists('WPCP_UploadHandler')) {
            require LETSBOX_ROOTDIR.'/vendors/jquery-file-upload/server/UploadHandler.php';
        }

        if ('1' === $this->get_processor()->get_shortcode_option('demo')) {
            // TO DO LOG + FAIL ERROR
            exit(-1);
        }

        $shortcode_max_file_size = $this->get_processor()->get_shortcode_option('maxfilesize');
        $shortcode_min_file_size = $this->get_processor()->get_shortcode_option('minfilesize');

        $accept_file_types = '/.('.$this->get_processor()->get_shortcode_option('upload_ext').')$/i';
        $post_max_size_bytes = min(Helpers::return_bytes(ini_get('post_max_size')), Helpers::return_bytes(ini_get('upload_max_filesize')));
        $max_file_size = ('0' !== $shortcode_max_file_size) ? Helpers::return_bytes($shortcode_max_file_size) : $post_max_size_bytes;
        $min_file_size = (!empty($shortcode_min_file_size)) ? Helpers::return_bytes($shortcode_min_file_size) : -1;

        $options = [
            'access_control_allow_methods' => ['POST', 'PUT'],
            'accept_file_types' => $accept_file_types,
            'inline_file_types' => '/\.____$/i',
            'orient_image' => false,
            'image_versions' => [],
            'max_file_size' => $max_file_size,
            'min_file_size' => $min_file_size,
            'print_response' => false,
        ];

        $error_messages = [
            1 => esc_html__('The uploaded file exceeds the upload_max_filesize directive in php.ini', 'wpcloudplugins'),
            2 => esc_html__('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form', 'wpcloudplugins'),
            3 => esc_html__('The uploaded file was only partially uploaded', 'wpcloudplugins'),
            4 => esc_html__('No file was uploaded', 'wpcloudplugins'),
            6 => esc_html__('Missing a temporary folder', 'wpcloudplugins'),
            7 => esc_html__('Failed to write file to disk', 'wpcloudplugins'),
            8 => esc_html__('A PHP extension stopped the file upload', 'wpcloudplugins'),
            'post_max_size' => esc_html__('The uploaded file exceeds the post_max_size directive in php.ini', 'wpcloudplugins'),
            'max_file_size' => esc_html__('File is too big', 'wpcloudplugins'),
            'min_file_size' => esc_html__('File is too small', 'wpcloudplugins'),
            'accept_file_types' => esc_html__('Filetype not allowed', 'wpcloudplugins'),
            'max_number_of_files' => esc_html__('Maximum number of files exceeded', 'wpcloudplugins'),
            'max_width' => esc_html__('Image exceeds maximum width', 'wpcloudplugins'),
            'min_width' => esc_html__('Image requires a minimum width', 'wpcloudplugins'),
            'max_height' => esc_html__('Image exceeds maximum height', 'wpcloudplugins'),
            'min_height' => esc_html__('Image requires a minimum height', 'wpcloudplugins'),
        ];

        $this->upload_handler = new \WPCP_UploadHandler($options, false, $error_messages);
        $response = @$this->upload_handler->post(false);

        // Upload files to Box
        foreach ($response['files'] as &$file) {
            // Set return Object
            $file->listtoken = $this->get_processor()->get_listtoken();
            $file->name = Helpers::filter_filename(stripslashes(rawurldecode($file->name)), false);

            $file->hash = $_REQUEST['hash'];
            $file->path = $_REQUEST['file_path'];
            $file->description = sanitize_textarea_field(wp_unslash($_REQUEST['file_description']));

            $file->convert = false;

            // Set Progress
            $return = ['file' => $file, 'status' => ['bytes_up_so_far' => 0, 'total_bytes_up_expected' => $file->size, 'percentage' => 0, 'progress' => 'starting']];
            self::set_upload_progress($file->hash, $return);

            if (isset($file->error)) {
                $file->error = esc_html__('Uploading failed', 'wpcloudplugins').': '.$file->error;
                $return['file'] = $file;
                $return['status']['progress'] = 'upload-failed';
                self::set_upload_progress($file->hash, $return);
                echo json_encode($return);

                error_log('[WP Cloud Plugin message]: '.sprintf('Uploading failed: %s', $file->error));

                exit();
            }

            /** Check if the user hasn't reached its usage limit */
            $max_user_folder_size = $this->get_processor()->get_shortcode_option('max_user_folder_size');
            if ('0' !== $this->get_processor()->get_shortcode_option('user_upload_folders') && '-1' !== $max_user_folder_size) {
                $disk_usage_after_upload = $this->get_client()->get_entry()->get_entry()->get_size() + $file->size;
                $max_allowed_bytes = Helpers::return_bytes($max_user_folder_size);
                if ($disk_usage_after_upload > $max_allowed_bytes) {
                    $return['status']['progress'] = 'upload-failed';
                    $file->error = esc_html__('You have reached your usage limit of', 'wpcloudplugins').' '.Helpers::bytes_to_size_1024($max_allowed_bytes);
                    self::set_upload_progress($file->hash, $return);
                    echo json_encode($return);

                    exit();
                }
            }

            // Create Folders if needed
            $upload_folder_id = $this->get_processor()->get_last_folder();
            if (!empty($file->path)) {
                $upload_folder_id = $this->create_folder_structure($file->path);
            }

            // Write file
            $chunkSizeBytes = 20 * 320 * 1000; // Multiple of 320kb, the recommended fragment size is between 5-10 MB.
            $file->name = apply_filters('letsbox_upload_file_name', $file->name, $this->get_processor());

            // Update Mime-type if needed (for IE8 and lower?)
            $fileExtension = pathinfo($file->name, PATHINFO_EXTENSION);
            $file->type = Helpers::get_mimetype($fileExtension);

            // Check for overwriting files
            $overwrite_id = false;
            if ('0' !== $this->get_processor()->get_shortcode_option('overwrite')) {
                $in_cache = $this->get_processor()->get_cache()->get_node_by_name($file->name, $upload_folder_id);

                if ($in_cache) {
                    $overwrite_id = $in_cache->get_id();
                } else {
                    $existing_files = $this->get_app()->get_client()->search($file->name, $upload_folder_id, null, 'file', 'name,id');
                }

                if (!empty($existing_files)) {
                    foreach ($existing_files as $existing_file) {
                        if ($existing_file->getName() === $file->name) {
                            $overwrite_id = $existing_file->getId();
                        }
                    }
                }
            }

            try {
                $uploadfile = $this->get_app()->get_client()->uploadFileToBox($file, $upload_folder_id, $overwrite_id);

                // Set new description, and update the entry
                if (!empty($file->description)) {
                    $updaterequest = ['description' => $file->description];
                    $uploadfile->setDescription($file->description);
                    $this->get_client()->update_entry($uploadfile->getId(), $updaterequest);
                }
            } catch (\Exception $ex) {
                $file->error = esc_html__('Not uploaded to the cloud', 'wpcloudplugins').': '.$ex->getMessage();
                $return['status']['progress'] = 'upload-failed';
                self::set_upload_progress($file->hash, $return);
                echo json_encode($return);
                error_log('[WP Cloud Plugin message]: '.sprintf('Not uploaded to the cloud on line %s: %s', __LINE__, $ex->getMessage()));

                exit();
            }

            if (empty($uploadfile) || 0 === $uploadfile->getSize()) {
                $file->error = esc_html__('Not succesfully uploaded to the cloud', 'wpcloudplugins');
                $return['status']['progress'] = 'upload-failed';

                return;
            }

            $entry = new Entry($uploadfile);
            $cached_entry = $this->get_processor()->get_cache()->add_to_cache($entry);

            // Add new file to our Cache
            $file->name = $entry->get_name();
            $file->completepath = $cached_entry->get_path($this->get_processor()->get_root_folder());
            $file->fileid = $cached_entry->get_id();
            $file->filesize = Helpers::bytes_to_size_1024($file->size);
            $file->link = false;
            $file->folderurl = false;
        }

        $return['file'] = $file;
        $return['status']['progress'] = 'upload-finished';
        $return['status']['percentage'] = '100';
        self::set_upload_progress($file->hash, $return);

        // Create response
        echo json_encode($return);

        exit();
    }

    public function do_upload_direct()
    {
        // NOT IMPLEMENTED
    }

    public static function get_upload_progress($file_hash)
    {
        wp_using_ext_object_cache(false);

        return get_transient('letsbox_upload_'.substr($file_hash, 0, 40));
    }

    public static function set_upload_progress($file_hash, $status)
    {
        wp_using_ext_object_cache(false);
        // Update progress
        return set_transient('letsbox_upload_'.substr($file_hash, 0, 40), $status, HOUR_IN_SECONDS);
    }

    public function get_upload_status()
    {
        $hash = $_REQUEST['hash'];

        // Try to get the upload status of the file
        for ($_try = 1; $_try < 6; ++$_try) {
            $result = self::get_upload_progress($hash);

            if (false !== $result) {
                if ('upload-failed' === $result['status']['progress'] || 'upload-finished' === $result['status']['progress']) {
                    delete_transient('letsbox_upload_'.substr($hash, 0, 40));
                }

                break;
            }

            // Wait a moment, perhaps the upload still needs to start
            usleep(500000 * $_try);
        }

        if (false === $result) {
            $result = ['file' => false, 'status' => ['bytes_up_so_far' => 0, 'total_bytes_up_expected' => 0, 'percentage' => 0, 'progress' => 'upload-failed']];
        }

        echo json_encode($result);

        exit();
    }

    public function upload_convert()
    {
        // NOT IMPLEMENTED
    }

    public function upload_post_process()
    {
        if ((!isset($_REQUEST['files'])) || 0 === count($_REQUEST['files'])) {
            echo json_encode(['result' => 0]);

            exit();
        }

        // Update the cache to process all changes
        $this->get_processor()->get_cache()->pull_for_changes(true);

        $uploaded_files = $_REQUEST['files'];
        $_uploaded_entries = [];
        $_email_entries = [];

        foreach ($uploaded_files as $file_id) {
            try {
                $api_entry = $this->get_app()->get_client()->getFileFromBox($file_id);
                $entry = new Entry($api_entry);
                $cachedentry = $this->get_processor()->get_cache()->add_to_cache($entry);
            } catch (\Exception $ex) {
                error_log('[WP Cloud Plugin message]: '.sprintf('API Error, Not able to receive just uploaded file: %s', $ex->getMessage()));

                continue;
            }

            if (false === $cachedentry) {
                continue;
            }

            // Upload Hook
            if (false === get_transient('letsbox_upload_'.$file_id)) {
                $cachedentry = apply_filters('letsbox_upload', $cachedentry, $this->_processor);
                do_action('letsbox_log_event', 'letsbox_uploaded_entry', $cachedentry);

                $_email_entries[] = $cachedentry;
            }

            $_uploaded_entries[] = $cachedentry;
        }

        // Send email if needed

        if (count($_email_entries) > 0) {
            if ('1' === $this->get_processor()->get_shortcode_option('notificationupload')) {
                $this->get_processor()->send_notification_email('upload', $_email_entries);
            }
        }

        // Return information of the files
        $files = [];
        foreach ($_uploaded_entries as $cachedentry) {
            $file = [];
            $file['name'] = $cachedentry->get_entry()->get_name();
            $file['type'] = $cachedentry->get_entry()->get_mimetype();
            $file['description'] = $cachedentry->get_entry()->get_description();
            $file['completepath'] = $cachedentry->get_path($this->get_processor()->get_root_folder());
            $file['fileid'] = $cachedentry->get_id();
            $file['filesize'] = Helpers::bytes_to_size_1024($cachedentry->get_entry()->get_size());
            $file['link'] = 'https://app.box.com/'.($cachedentry->get_entry()->is_dir() ? 'folder' : 'file').'/'.$cachedentry->get_id();
            $file['folderurl'] = false;

            $files[$file['fileid']] = apply_filters('letsbox_upload_entry_information', $file, $cachedentry, $this->_processor);

            set_transient('letsbox_upload_'.$cachedentry->get_id(), true, HOUR_IN_SECONDS);
        }

        do_action('letsbox_upload_post_process', $_uploaded_entries, $this->_processor);

        // Clear Cached Requests
        CacheRequest::clear_request_cache();

        echo json_encode(['result' => 1, 'files' => $files]);
    }

    public function create_folder_structure($path)
    {
        $folders = explode('/', $path);
        $current_folder_id = $this->get_processor()->get_last_folder();

        foreach ($folders as $key => $name) {
            if (empty($name)) {
                continue;
            }

            $cached_entry = $this->get_processor()->get_cache()->get_node_by_name($name, $current_folder_id);

            if ($cached_entry) {
                $current_folder_id = $cached_entry->get_id();

                continue;
            }
            $existing_folders = $this->get_app()->get_client()->search($name, $current_folder_id, null, 'folder', 'name,id');

            if (!empty($existing_folders)) {
                foreach ($existing_folders as $existing_folder) {
                    if ($existing_folder->getName() === $name) {
                        $current_folder_id = $existing_folder->getId();

                        continue 2;
                    }
                }
            }

            try {
                $api_entry = $this->get_app()->get_client()->createNewBoxFolder($name, $current_folder_id);
                // Add new file to our Cache
                $newentry = new Entry($api_entry);
                $cached_entry = $this->get_processor()->get_cache()->add_to_cache($newentry);
                $this->get_client()->get_cache()->update_cache();
                $current_folder_id = $cached_entry->get_id();

                do_action('letsbox_log_event', 'letsbox_created_entry', $cached_entry);
            } catch (\Exception $ex) {
                error_log('[WP Cloud Plugin message]: '.sprintf('API Error on line %s: %s', __LINE__, $ex->getMessage()));
            }
        }

        return $current_folder_id;
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
