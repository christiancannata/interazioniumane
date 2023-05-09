<?php

namespace TheLion\LetsBox;

class Processor
{
    public $options = [];
    public $mobile = false;
    protected $listtoken = '';
    protected $_rootFolder;
    protected $_lastFolder;
    protected $_folderPath;
    protected $_requestedEntry;
    protected $_load_scripts = ['general' => false, 'files' => false, 'upload' => false, 'mediaplayer' => false];

    /**
     * @var \TheLion\LetsBox\Main
     */
    private $_main;

    /**
     * @var \TheLion\LetsBox\App
     */
    private $_app;

    /**
     * @var \TheLion\LetsBox\Client
     */
    private $_client;

    /**
     * @var \TheLion\LetsBox\User
     */
    private $_user;

    /**
     * @var \TheLion\LetsBox\UserFolders
     */
    private $_userfolders;

    /**
     * @var \TheLion\LetsBox\Cache
     */
    private $_cache;

    /**
     * @var \TheLion\LetsBox\Shortcodes
     */
    private $_shortcodes;

    /**
     * Construct the plugin object.
     */
    public function __construct(Main $_main)
    {
        $this->_main = $_main;
        register_shutdown_function([$this, 'do_shutdown']);

        $this->settings = get_option('lets_box_settings');

        if ($this->is_network_authorized()) {
            $this->settings = array_merge($this->settings, get_site_option('letsbox_network_settings', []));
        }

        if (isset($_REQUEST['mobile']) && ('true' === $_REQUEST['mobile'])) {
            $this->mobile = true;
        }
        // If the user wants a hard refresh, set this globally
        if (isset($_REQUEST['hardrefresh']) && 'true' === $_REQUEST['hardrefresh'] && (!defined('FORCE_REFRESH'))) {
            define('FORCE_REFRESH', true);
        }
    }

    public function start_process()
    {
        if (!isset($_REQUEST['action'])) {
            error_log('[WP Cloud Plugin message]: '." Function startProcess() requires an 'action' request");

            exit();
        }
        do_action('letsbox_before_start_process', $_REQUEST['action'], $this);

        if (('letsbox-revoke' === $_REQUEST['action'])) {
            $is_authorized = check_ajax_referer('letsbox-admin-action', false, false);
            if ($is_authorized && Helpers::check_user_role($this->settings['permissions_edit_settings'])) {
                $app = new \TheLion\LetsBox\App($this);
                $app->revoke_token();
            }

            exit(1);
        }

        $authorized = $this->_is_action_authorized();

        if ('letsbox-factory-reset' === $_REQUEST['action']) {
            if (Helpers::check_user_role($this->settings['permissions_edit_settings'])) {
                $this->get_main()->do_factory_reset();
            }

            exit(1);
        }

        if ('letsbox-reset-cache' === $_REQUEST['action']) {
            if (Helpers::check_user_role($this->settings['permissions_edit_settings'])) {
                $this->reset_complete_cache();
            }

            exit(1);
        }

        if ('letsbox-reset-statistics' === $_REQUEST['action']) {
            if (Helpers::check_user_role($this->settings['permissions_edit_settings'])) {
                Events::truncate_database();
            }

            exit(1);
        }

        if ((!isset($_REQUEST['listtoken']))) {
            $url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '"url unknown"';
            $request = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
            error_log('[WP Cloud Plugin message]: '." Function start_process() requires a 'listtoken' on {$url} requested via {$request}");
            error_log(var_export($_REQUEST, true));

            exit();
        }

        $this->listtoken = $_REQUEST['listtoken'];
        $this->options = $this->get_shortcodes()->get_shortcode_by_id($this->listtoken);

        if (false === $this->options) {
            $url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            error_log('[WP Cloud Plugin message]: '.' Function start_process('.$_REQUEST['action'].") hasn't received a valid listtoken (".$this->listtoken.") on: {$url} \n");

            exit();
        }

        if (false === $this->get_user()->can_view()) {
            $url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            $request = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
            error_log('[WP Cloud Plugin message]: '." Function start_process() discovered that an user didn't have the permission to view the plugin on {$url} requested via {$request}");

            exit();
        }

        if (is_wp_error($authorized)) {
            error_log('[WP Cloud Plugin message]: '." Function startProcess() isn't authorized");

            if ('1' === $this->options['debug']) {
                exit($authorized->get_error_message());
            }

            exit();
        }

        // Remove all cache files for current shortcode when refreshing, otherwise check for new changes
        if (defined('FORCE_REFRESH')) {
            CacheRequest::clear_request_cache();
            $this->get_cache()->reset_cache();
        } else {
            // Pull for changes if needed
            $this->get_cache()->pull_for_changes();
        }

        // Set rootFolder
        if ('manual' === $this->options['user_upload_folders']) {
            $userfolder = $this->get_user_folders()->get_manually_linked_folder_for_user();
            if (is_wp_error($userfolder) || false === $userfolder) {
                error_log('[WP Cloud Plugin message]: '.'Cannot find a manually linked folder for user');

                exit('-1');
            }
            $this->_rootFolder = $userfolder->get_id();
        } elseif (('auto' === $this->options['user_upload_folders']) && !Helpers::check_user_role($this->options['view_user_folders_role'])) {
            $userfolder = $this->get_user_folders()->get_auto_linked_folder_for_user();

            if (is_wp_error($userfolder) || false === $userfolder) {
                error_log('[WP Cloud Plugin message]: '.'Cannot find a auto linked folder for user');

                exit('-1');
            }
            $this->_rootFolder = $userfolder->get_id();
        } else {
            $this->_rootFolder = $this->options['root'];
        }

        // Open Sub Folder if needed
        if (!empty($this->options['subfolder']) && '/' !== $this->options['subfolder']) {
            $sub_folder_path = apply_filters('letsbox_set_subfolder_path', Helpers::apply_placeholders($this->options['subfolder'], $this), $this->options, $this);
            $subfolder = $this->get_client()->get_sub_folder_by_path($this->_rootFolder, $sub_folder_path, true);

            if (is_wp_error($subfolder) || false === $subfolder) {
                error_log('[WP Cloud Plugin message]: '.'Cannot find or create the subfolder');

                exit('-1');
            }
            $this->_rootFolder = $subfolder->get_id();
        }

        $this->_lastFolder = $this->_rootFolder;
        if (isset($_REQUEST['lastFolder']) && '' !== $_REQUEST['lastFolder']) {
            $this->_lastFolder = $_REQUEST['lastFolder'];
        }

        $this->_requestedEntry = $this->_lastFolder;
        if (isset($_REQUEST['id']) && '' !== $_REQUEST['id']) {
            $this->_requestedEntry = $_REQUEST['id'];
        }

        if (!empty($_REQUEST['folderPath'])) {
            $this->_folderPath = json_decode(base64_decode($_REQUEST['folderPath']));

            if (false === $this->_folderPath || null === $this->_folderPath || !is_array($this->_folderPath)) {
                $this->_folderPath = [$this->_rootFolder];
            }

            $key = array_search($this->_requestedEntry, $this->_folderPath);
            if (false !== $key) {
                array_splice($this->_folderPath, $key);
                if (0 === count($this->_folderPath)) {
                    $this->_folderPath = [$this->_rootFolder];
                }
            }
        } else {
            $this->_folderPath = [$this->_rootFolder];
        }

        // Check if the request is cached
        if (in_array($_REQUEST['action'], ['letsbox-get-filelist', 'letsbox-get-gallery', 'letsbox-get-playlist'])) {
            // And Set GZIP compression if possible
            $this->_set_gzip_compression();

            if (!defined('FORCE_REFRESH')) {
                $cached_request = new CacheRequest($this);
                if ($cached_request->is_cached()) {
                    echo $cached_request->get_cached_response();

                    exit();
                }
            }
        }

        do_action('letsbox_start_process', $_REQUEST['action'], $this);

        switch ($_REQUEST['action']) {
            case 'letsbox-get-filelist':
                $filebrowser = new Filebrowser($this);

                if (isset($_REQUEST['query']) && !empty($_REQUEST['query']) && '1' === $this->options['search']) { // Search files
                    $filelist = $filebrowser->searchFiles();
                } else {
                    $filelist = $filebrowser->getFilesList(); // Read folder
                }

                break;

            case 'letsbox-preview':
                $file = $this->get_client()->preview_entry();

                break;

            case 'letsbox-edit':
                if (false === $this->get_user()->can_edit()) {
                    exit();
                }

                $file = $this->get_client()->edit_entry();

                break;

            case 'letsbox-thumbnail':
                $file = $this->get_client()->build_thumbnail();

                break;

            case 'letsbox-download':
                if (false === $this->get_user()->can_download()) {
                    exit();
                }
                $this->get_client()->download_entry();

                break;

            case 'letsbox-stream':
                $this->get_client()->stream_entry();

                break;

            case 'letsbox-shorten-url':
                if (false === $this->get_user()->can_deeplink()) {
                    exit();
                }

                $cached_node = $this->get_client()->get_entry();
                $url = esc_url_raw($_REQUEST['url']);

                $shortened_url = $this->get_client()->shorten_url($cached_node, $url);

                $data = [
                    'id' => $cached_node->get_id(),
                    'name' => $cached_node->get_name(),
                    'url' => $shortened_url,
                ];

                echo json_encode($data);

                exit();

            case 'letsbox-create-zip':
                if (false === $this->get_user()->can_download()) {
                    exit();
                }

                switch ($_REQUEST['type']) {
                    case 'do-zip':
                        $zip = new Zip($this);
                        $zip->do_zip();

                        exit();

                        break;
                }

                break;

            case 'letsbox-create-link':
            case 'letsbox-embedded':
                if (isset($_REQUEST['entries'])) {
                    foreach ($_REQUEST['entries'] as $entry_id) {
                        $link['links'][] = $this->get_client()->get_shared_link_for_output($entry_id);
                    }
                } else {
                    $link = $this->get_client()->get_shared_link_for_output();
                }
                echo json_encode($link);

                break;

            case 'letsbox-get-gallery':
                $gallery = new Gallery($this);

                if (isset($_REQUEST['query']) && !empty($_REQUEST['query']) && '1' === $this->options['search']) { // Search files
                    $imagelist = $gallery->searchImageFiles();
                } else {
                    $imagelist = $gallery->getImagesList(); // Read folder
                }

                break;

            case 'letsbox-upload-file':
                $user_can_upload = $this->get_user()->can_upload();

                if (false === $user_can_upload) {
                    exit();
                }

                $upload_processor = new Upload($this);

                switch ($_REQUEST['type']) {
                    case 'upload-preprocess':
                        $status = $upload_processor->upload_pre_process();

                        break;

                    case 'do-upload':
                        $upload = $upload_processor->do_upload();

                        break;

                    case 'get-status':
                        $status = $upload_processor->get_upload_status();

                        break;

                    case 'get-direct-url':
                        $status = $upload_processor->do_upload_direct();

                        break;

                    case 'upload-convert':
                        $status = $upload_processor->upload_convert();

                        break;

                    case 'upload-postprocess':
                        $status = $upload_processor->upload_post_process();

                        break;
                }

                exit();

                break;

            case 'letsbox-delete-entries':
// Check if user is allowed to delete entry
                $user_can_delete = $this->get_user()->can_delete_files() || $this->get_user()->can_delete_folders();

                if (false === $user_can_delete) {
                    echo json_encode(['result' => '-1', 'msg' => esc_html__('Failed to delete entry', 'wpcloudplugins')]);

                    exit();
                }

                $entries_to_delete = [];
                foreach ($_REQUEST['entries'] as $requested_id) {
                    $entries_to_delete[] = $requested_id;
                }

                $entries = $this->get_client()->delete_entries($entries_to_delete);

                foreach ($entries as $entry) {
                    if (false === $entry) {
                        echo json_encode(['result' => '-1', 'msg' => esc_html__('Not all entries could be deleted', 'wpcloudplugins')]);

                        exit();
                    }
                }
                echo json_encode(['result' => '1', 'msg' => esc_html__('Entry was deleted', 'wpcloudplugins')]);

                exit();

                break;

            case 'letsbox-copy-entry':
                // Check if user is allowed to rename entry
                $user_can_copy = $this->get_user()->can_copy_files() || $this->get_user()->can_copy_folders();

                if (false === $user_can_copy) {
                    echo json_encode(['result' => '-1', 'msg' => esc_html__('Failed to copy entry', 'wpcloudplugins')]);

                    exit();
                }

                // Strip unsafe characters
                $newname = rawurldecode($_REQUEST['newname']);
                $new_filename = Helpers::filter_filename($newname, false);

                $file = $this->get_client()->copy_entry(null, null, $new_filename);

                if (is_wp_error($file)) {
                    echo json_encode(['result' => '-1', 'msg' => $file->get_error_message()]);
                } else {
                    echo json_encode(['result' => '1', 'msg' => esc_html__('Entry was copied', 'wpcloudplugins')]);
                }

                exit();

                break;

            case 'letsbox-rename-entry':
                // Check if user is allowed to rename entry
                $user_can_rename = $this->get_user()->can_rename_files() || $this->get_user()->can_rename_folders();

                if (false === $user_can_rename) {
                    echo json_encode(['result' => '-1', 'msg' => esc_html__('Failed to rename entry', 'wpcloudplugins')]);

                    exit();
                }

                // Strip unsafe characters
                $newname = rawurldecode($_REQUEST['newname']);
                $new_filename = Helpers::filter_filename($newname, false);

                $file = $this->get_client()->rename_entry($new_filename);

                if (is_wp_error($file)) {
                    echo json_encode(['result' => '-1', 'msg' => $file->get_error_message()]);
                } else {
                    echo json_encode(['result' => '1', 'msg' => esc_html__('Entry was renamed', 'wpcloudplugins')]);
                }

                exit();

                break;

            case 'letsbox-move-entries':
                // Check if user is allowed to move entry
                $user_can_move = $this->get_user()->can_move_files() || $this->get_user()->can_move_folders();

                if (false === $user_can_move) {
                    echo json_encode(['result' => '-1', 'msg' => esc_html__('Failed to move', 'wpcloudplugins')]);

                    exit();
                }

                $entries_to_move = [];
                foreach ($_REQUEST['entries'] as $requested_id) {
                    $entries_to_move[] = $requested_id;
                }

                $entries = $this->get_client()->move_entries($entries_to_move, $_REQUEST['target']);

                foreach ($entries as $entry) {
                    if (is_wp_error($entry) || empty($entry)) {
                        echo json_encode(['result' => '-1', 'msg' => esc_html__('Not all entries could be moved', 'wpcloudplugins')]);

                        exit();
                    }
                }
                echo json_encode(['result' => '1', 'msg' => esc_html__('Successfully moved to new location', 'wpcloudplugins')]);

                exit();

                break;

            case 'letsbox-edit-description-entry':
                // Check if user is allowed to rename entry
                $user_can_editdescription = $this->get_user()->can_edit_description();

                if (false === $user_can_editdescription) {
                    echo json_encode(['result' => '-1', 'msg' => esc_html__('Failed to edit description', 'wpcloudplugins')]);

                    exit();
                }

                $newdescription = sanitize_textarea_field(wp_unslash($_REQUEST['newdescription']));
                $result = $this->get_client()->update_description($newdescription);

                if (is_wp_error($result)) {
                    echo json_encode(['result' => '-1', 'msg' => $result->get_error_message()]);
                } else {
                    echo json_encode(['result' => '1', 'msg' => esc_html__('Description was edited', 'wpcloudplugins'), 'description' => $result]);
                }

                exit();

                break;

            case 'letsbox-create-entry':
// Strip unsafe characters
                $_name = rawurldecode($_REQUEST['name']);
                $new_name = Helpers::filter_filename($_name, false);
                $mimetype = $_REQUEST['mimetype'];

                // Check if user is allowed
                $user_can_create_entry = $this->get_user()->can_add_folders();

                if (false === $user_can_create_entry) {
                    echo json_encode(['result' => '-1', 'msg' => esc_html__('Failed to add entry', 'wpcloudplugins')]);

                    exit();
                }

                $file = $this->get_client()->add_folder($new_name);

                if (is_wp_error($file)) {
                    echo json_encode(['result' => '-1', 'msg' => $file->get_error_message()]);
                } else {
                    echo json_encode(['result' => '1', 'msg' => $new_name.' '.esc_html__('was added', 'wpcloudplugins'), 'lastFolder' => $file->get_id()]);
                }

                exit();

                break;

            case 'letsbox-get-playlist':
                $mediaplayer = new Mediaplayer($this);
                $playlist = $mediaplayer->getMediaList();

                break;

            case 'letsbox-event-log':
                return;

            case 'letsbox-getads':
                $ads_url = ('' !== $this->get_shortcode_option('ads_tag_url') ? htmlspecialchars_decode($this->get_shortcode_option('ads_tag_url')) : $this->get_setting('mediaplayer_ads_tagurl'));
                $response = wp_remote_get($ads_url);
                header('Content-Type: text/xml; charset=UTF-8');
                echo wp_remote_retrieve_body($response);

                exit();

            default:
                error_log('[WP Cloud Plugin message]: '.sprintf('No valid AJAX request: %s', $_REQUEST['action']));

                exit('Lets-Box: '.esc_html__('No valid AJAX request', 'wpcloudplugins'));
        }

        exit();
    }

    public function create_from_shortcode($atts)
    {
        $atts = (is_string($atts)) ? [] : $atts;
        $atts = $this->remove_deprecated_options($atts);

        $defaults = [
            'dir' => false,
            'subfolder' => false,
            'class' => '',
            'startid' => false,
            'mode' => 'files',
            'userfolders' => '0',
            'usertemplatedir' => '',
            'viewuserfoldersrole' => 'administrator',
            'userfoldernametemplate' => '',
            'maxuserfoldersize' => '-1',
            'showfiles' => '1',
            'maxfiles' => '-1',
            'showfolders' => '1',
            'filesize' => '1',
            'filedate' => '1',
            'hoverthumbs' => '1',
            'filedescription' => '0',
            'filelayout' => 'grid',
            'showext' => '1',
            'sortfield' => 'name',
            'sortorder' => 'asc',
            'showbreadcrumb' => '1',
            'candownloadzip' => '0',
            'lightboxnavigation' => '1',
            'showsharelink' => '0',
            'showrefreshbutton' => '1',
            'roottext' => esc_html__('Start', 'wpcloudplugins'),
            'search' => '1',
            'searchfrom' => 'parent',
            'searchterm' => '',
            'searchcontents' => '1',
            'include' => '*',
            'includeext' => '*',
            'exclude' => '*',
            'excludeext' => '*',
            'maxwidth' => '100%',
            'maxheight' => '',
            'viewrole' => 'administrator|editor|author|contributor|subscriber|guest',
            'allowpreview' => '1',
            'previewrole' => 'all',
            'downloadrole' => 'administrator|editor|author|contributor|subscriber|guest',
            'sharerole' => 'all',
            'edit' => '0',
            'editrole' => 'administrator|editor|author',
            'previewinline' => '1',
            'quality' => '90',
            'slideshow' => '0',
            'pausetime' => '5000',
            'showfilenames' => '0',
            'showdescriptionsontop' => '0',
            'targetheight' => '300',
            'folderthumbs' => '0',
            'mediaskin' => '',
            'mediabuttons' => 'prevtrack|playpause|nexttrack|volume|current|duration|fullscreen',
            'autoplay' => '0',
            'hideplaylist' => '0',
            'showplaylistonstart' => '1',
            'playlistinline' => '0',
            'playlistthumbnails' => '1',
            'linktomedia' => '0',
            'linktoshop' => '',
            'ads' => '0',
            'ads_tag_url' => '',
            'ads_skipable' => '1',
            'ads_skipable_after' => '',
            'notificationupload' => '0',
            'notificationdownload' => '0',
            'notificationdeletion' => '0',
            'notificationemail' => '%admin_email%',
            'notification_skipemailcurrentuser' => '0',
            'notification_from_name' => '',
            'notification_from_email' => '',
            'upload' => '0',
            'upload_folder' => '1',
            'upload_auto_start' => '1',
            'upload_button_text' => '',
            'upload_button_text_plural' => '',
            'overwrite' => '0',
            'uploadext' => '.',
            'uploadrole' => 'administrator|editor|author|contributor|subscriber',
            'minfilesize' => '0',
            'maxfilesize' => '0',
            'maxnumberofuploads' => '-1',
            'delete' => '0',
            'deleterole' => 'administrator|editor',
            'rename' => '0',
            'renamerole' => 'administrator|editor',
            'move' => '0',
            'moverole' => 'administrator|editor',
            'copy' => '0',
            'copyfilesrole' => 'administrator|editor',
            'copyfoldersrole' => 'administrator|editor',
            'editdescription' => '0',
            'editdescriptionrole' => 'administrator|editor',
            'addfolder' => '0',
            'addfolderrole' => 'administrator|editor',
            'createdocument' => '0',
            'createdocumentrole' => 'administrator|editor',
            'deeplink' => '0',
            'deeplinkrole' => 'all',
            'mcepopup' => '0',
            'post_id' => get_the_ID(),
            'debug' => '0',
            'demo' => '0',
        ];

        // Read shortcode & Create a unique identifier
        $shortcode = shortcode_atts($defaults, $atts, 'letsbox');
        $this->listtoken = md5(serialize($defaults).serialize($shortcode));
        extract($shortcode);

        $cached_shortcode = $this->get_shortcodes()->get_shortcode_by_id($this->listtoken);

        if (false === $cached_shortcode) {
            $authorized = $this->_is_action_authorized();

            if (is_wp_error($authorized)) {
                if ('1' === $debug) {
                    return "<div id='message' class='error'><p>".$autorized->get_error_message().'</p></div>';
                }

                return '&#9888; <strong>'.esc_html__('Module cannot be rendered due to an authorization issue. Contact the administrator to get access.', 'wpcloudplugins').'</strong>';
            }

            switch ($mode) {
                case 'gallery':
                    $includeext = ('*' == $includeext) ? 'gif|jpg|jpeg|png|bmp|tif|tiff|webp|heic' : $includeext;
                    $uploadext = ('.' == $uploadext) ? 'gif|jpg|jpeg|png|bmp|tif|tiff|webp|heic' : $uploadext;
                    // no break
                case 'search':
                    $searchfrom = 'root';
                    // no break
                default:
                    break;
            }

            $rootfolder = $this->get_client()->get_root_folder();

            if (empty($rootfolder)) {
                error_log('[WP Cloud Plugin message]: Module cannot be rendered as the requested folder is not (longer) accessible');

                return '&#9888; <strong>'.esc_html__('Module cannot be rendered as the requested content is not (longer) accessible. Contact the administrator to get access.', 'wpcloudplugins').'</strong>';
            }

            $rootfolderid = $rootfolder->get_entry()->get_id();

            if (false === $dir) {
                $dir = $rootfolderid;
            }

            if (false !== $subfolder) {
                $subfolder = Helpers::clean_folder_path('/'.rtrim($subfolder, '/'));
            }

            // Explode roles
            $viewrole = explode('|', $viewrole);
            $previewrole = explode('|', $previewrole);
            $downloadrole = explode('|', $downloadrole);
            $sharerole = explode('|', $sharerole);
            $editrole = explode('|', $editrole);
            $uploadrole = explode('|', $uploadrole);
            $deleterole = explode('|', $deleterole);
            $renamerole = explode('|', $renamerole);
            $moverole = explode('|', $moverole);
            $copyfilesrole = explode('|', $copyfilesrole);
            $copyfoldersrole = explode('|', $copyfoldersrole);
            $editdescriptionrole = explode('|', $editdescriptionrole);
            $addfolderrole = explode('|', $addfolderrole);
            $createdocumentrole = explode('|', $createdocumentrole);

            $viewuserfoldersrole = explode('|', $viewuserfoldersrole);
            $deeplinkrole = explode('|', $deeplinkrole);
            $mediabuttons = explode('|', $mediabuttons);

            $this->options = [
                'root' => $dir,
                'subfolder' => $subfolder,
                'class' => $class,
                'base' => $rootfolderid,
                'startid' => $startid,
                'mode' => $mode,
                'user_upload_folders' => $userfolders,
                'user_template_dir' => $usertemplatedir,
                'view_user_folders_role' => $viewuserfoldersrole,
                'user_folder_name_template' => $userfoldernametemplate,
                'max_user_folder_size' => $maxuserfoldersize,
                'mediaskin' => $mediaskin,
                'mediabuttons' => $mediabuttons,
                'autoplay' => $autoplay,
                'hideplaylist' => $hideplaylist,
                'showplaylistonstart' => $showplaylistonstart,
                'playlistinline' => $playlistinline,
                'playlistthumbnails' => $playlistthumbnails,
                'linktomedia' => $linktomedia,
                'linktoshop' => $linktoshop,
                'ads' => $ads,
                'ads_tag_url' => $ads_tag_url,
                'ads_skipable' => $ads_skipable,
                'ads_skipable_after' => $ads_skipable_after,
                'show_files' => $showfiles,
                'show_folders' => $showfolders,
                'show_filesize' => $filesize,
                'show_filedate' => $filedate,
                'hover_thumbs' => $hoverthumbs,
                'show_description' => $filedescription,
                'max_files' => $maxfiles,
                'filelayout' => $filelayout,
                'show_ext' => $showext,
                'sort_field' => $sortfield,
                'sort_order' => $sortorder,
                'show_breadcrumb' => $showbreadcrumb,
                'can_download_zip' => $candownloadzip,
                'lightbox_navigation' => $lightboxnavigation,
                'show_sharelink' => $showsharelink,
                'show_refreshbutton' => $showrefreshbutton,
                'root_text' => $roottext,
                'search' => $search,
                'searchfrom' => $searchfrom,
                'searchterm' => $searchterm,
                'searchcontents' => $searchcontents,
                'include' => explode('|', htmlspecialchars_decode($include)),
                'include_ext' => explode('|', strtolower($includeext)),
                'exclude' => explode('|', htmlspecialchars_decode($exclude)),
                'exclude_ext' => explode('|', strtolower($excludeext)),
                'maxwidth' => $maxwidth,
                'maxheight' => $maxheight,
                'view_role' => $viewrole,
                'allow_preview' => $allowpreview,
                'preview_role' => $previewrole,
                'download_role' => $downloadrole,
                'share_role' => $sharerole,
                'edit' => $edit,
                'edit_role' => $editrole,
                'previewinline' => ('0' === $allowpreview) ? '0' : $previewinline,
                'notificationupload' => $notificationupload,
                'notificationdownload' => $notificationdownload,
                'notificationdeletion' => $notificationdeletion,
                'notificationemail' => $notificationemail,
                'notification_skip_email_currentuser' => $notification_skipemailcurrentuser,
                'notification_from_name' => $notification_from_name,
                'notification_from_email' => $notification_from_email,
                'upload' => $upload,
                'upload_folder' => $upload_folder,
                'upload_auto_start' => $upload_auto_start,
                'upload_button_text' => $upload_button_text,
                'upload_button_text_plural' => $upload_button_text_plural,
                'overwrite' => $overwrite,
                'upload_ext' => strtolower($uploadext),
                'upload_role' => $uploadrole,
                'minfilesize' => $minfilesize,
                'maxfilesize' => Helpers::return_bytes($maxfilesize),
                'maxnumberofuploads' => $maxnumberofuploads,
                'delete' => $delete,
                'delete_role' => $deleterole,
                'rename' => $rename,
                'rename_role' => $renamerole,
                'move' => $move,
                'move_role' => $moverole,
                'copy' => $copy,
                'copy_files_role' => $copyfilesrole,
                'copy_folders_role' => $copyfoldersrole,
                'editdescription' => $editdescription,
                'editdescription_role' => $editdescriptionrole,
                'addfolder' => $addfolder,
                'addfolder_role' => $addfolderrole,
                'create_document' => $createdocument,
                'create_document_role' => $createdocumentrole,
                'deeplink' => $deeplink,
                'deeplink_role' => $deeplinkrole,
                'quality' => $quality,
                'show_filenames' => $showfilenames,
                'show_descriptions_on_top' => $showdescriptionsontop,
                'targetheight' => $targetheight,
                'folderthumbs' => $folderthumbs,
                'slideshow' => $slideshow,
                'pausetime' => $pausetime,
                'mcepopup' => $mcepopup,
                'post_id' => $post_id,
                'debug' => $debug,
                'demo' => $demo,
                'expire' => strtotime('+1 weeks'),
                'listtoken' => $this->listtoken, ];

            $this->options = apply_filters('letsbox_shortcode_add_options', $this->options, $this, $atts);

            $this->save_shortcodes();

            $this->options = apply_filters('letsbox_shortcode_set_options', $this->options, $this, $atts);

            // Create userfolders if needed

            if (('auto' === $this->options['user_upload_folders'])) {
                if ('Yes' === $this->settings['userfolder_onfirstvisit']) {
                    $allusers = [];
                    $roles = array_diff($this->options['view_role'], $this->options['view_user_folders_role']);

                    foreach ($roles as $role) {
                        $users_query = new \WP_User_Query([
                            'fields' => 'all_with_meta',
                            'role' => $role,
                            'orderby' => 'display_name',
                        ]);
                        $results = $users_query->get_results();
                        if ($results) {
                            $allusers = array_merge($allusers, $results);
                        }
                    }

                    $userfolder = $this->get_user_folders()->create_user_folders($allusers);
                }
            }
        } else {
            $this->options = apply_filters('letsbox_shortcode_set_options', $cached_shortcode, $this, $atts);
        }

        ob_start();
        $this->render_template();

        return ob_get_clean();
    }

    public function render_template()
    {
        // Reload User Object for this new shortcode
        $user = $this->get_user('reload');

        if (false === $this->get_user()->can_view()) {
            do_action('letsbox_shortcode_no_view_permission', $this);

            return;
        }

        // Update Unique ID
        update_option('lets_box_uniqueID', get_option('lets_box_uniqueID', 0) + 1);

        // Render the  template
        $dataid = '';

        $colors = $this->get_setting('colors');

        if ('manual' === $this->options['user_upload_folders']) {
            $userfolder = get_user_option('lets_box_linkedto');
            if (is_array($userfolder) && isset($userfolder['folderid'])) {
                // $dataid = $userfolder['folderid'];
            } else {
                $defaultuserfolder = get_site_option('lets_box_guestlinkedto');
                if (is_array($defaultuserfolder) && isset($defaultuserfolder['folderid'])) {
                    // $dataid = $defaultuserfolder['folderid'];
                } else {
                    echo "<div id='LetsBox' class='{$colors['style']}'>";
                    $this->load_scripts('general');

                    include sprintf('%s/templates/frontend/noaccess.php', LETSBOX_ROOTDIR);
                    echo '</div>';

                    return;
                }
            }
        }

        $dataorgid = $dataid;
        $dataid = (false !== $this->options['startid']) ? $this->options['startid'] : $dataid;

        $shortcode_class = ('shortcode' === $this->options['mcepopup']) ? 'initiate' : '';

        do_action('letsbox_before_shortcode', $this);

        echo "<div id='LetsBox' class='{$colors['style']} {$this->options['class']} {$this->options['mode']} {$shortcode_class}' style='display:none'>";
        echo "<noscript><div class='LetsBox-nojsmessage'>".esc_html__('To view this content, you need to have JavaScript enabled in your browser', 'wpcloudplugins').'.<br/>';
        echo "<a href='http://www.enable-javascript.com/' target='_blank'>".esc_html__('To do so, please follow these instructions', 'wpcloudplugins').'</a>.</div></noscript>';

        switch ($this->options['mode']) {
            case 'files':
                $this->load_scripts('files');

                echo "<div id='LetsBox-{$this->listtoken}' class='LetsBox files jsdisabled' data-list='files' data-token='{$this->listtoken}' data-id='".$dataid."' data-path='".base64_encode(json_encode($this->_folderPath))."' data-sort='".$this->options['sort_field'].':'.$this->options['sort_order']."' data-org-id='".$dataorgid."' data-org-path='".base64_encode(json_encode($this->_folderPath))."' data-source='".md5($this->options['root'].$this->options['mode'])."' data-layout='".$this->options['filelayout']."' data-lightboxnav='".$this->options['lightbox_navigation']."' data-query='{$this->options['searchterm']}' data-action='{$this->options['mcepopup']}'>";

                if ('shortcode' === $this->options['mcepopup']) {
                    echo "<div class='selected-folder'><strong>".esc_html__('Selected folder', 'wpcloudplugins').": </strong><span class='current-folder-raw'></span></div>";
                }

                if ('linkto' === $this->get_shortcode_option('mcepopup') || 'linktobackendglobal' === $this->get_shortcode_option('mcepopup')) {
                    $rootfolder = $this->get_client()->get_root_folder();
                    $button_text = esc_html__('Use the Root Folder of your Account', 'wpcloudplugins');
                    echo '<div data-id="'.$rootfolder->get_id().'" data-name="'.$rootfolder->get_name().'">';
                    echo '<div class="entry_linkto entry_linkto_root">';
                    echo '<span><input class="button-secondary" type="submit" title="'.$button_text.'" value="'.$button_text.'"></span>';
                    echo '</div>';
                    echo '</div>';
                }

                include sprintf('%s/templates/frontend/file_browser.php', LETSBOX_ROOTDIR);
                $this->render_uploadform();

                echo '</div>';

                break;

            case 'upload':
                echo "<div id='LetsBox-{$this->listtoken}' class='LetsBox upload jsdisabled'  data-token='{$this->listtoken}' data-id='".$dataid."' data-path='".base64_encode(json_encode($this->_folderPath))."' >";
                $this->render_uploadform();
                echo '</div>';

                break;

            case 'gallery':
                $this->load_scripts('files');

                echo "<div id='LetsBox-{$this->listtoken}' class='LetsBox wpcp-gallery jsdisabled' data-list='gallery' data-token='{$this->listtoken}' data-id='".$dataid."' data-path='".base64_encode(json_encode($this->_folderPath))."' data-sort='".$this->options['sort_field'].':'.$this->options['sort_order']."' data-org-id='".$dataid."' data-org-path='".base64_encode(json_encode($this->_folderPath))."' data-source='".md5($this->options['root'].$this->options['mode'])."' data-targetheight='".$this->options['targetheight']."' data-slideshow='".$this->options['slideshow']."' data-pausetime='".$this->options['pausetime']."' data-lightboxnav='".$this->options['lightbox_navigation']."' data-query='{$this->options['searchterm']}'>";

                include sprintf('%s/templates/frontend/gallery.php', LETSBOX_ROOTDIR);
                $this->render_uploadform();
                echo '</div>';

                break;

            case 'search':
                echo "<div id='LetsBox-{$this->listtoken}' class='LetsBox files searchlist jsdisabled' data-list='search' data-token='{$this->listtoken}' data-id='".$dataid."' data-path='".base64_encode(json_encode($this->_folderPath))."' data-sort='".$this->options['sort_field'].':'.$this->options['sort_order']."' data-org-id='".$dataorgid."' data-org-path='".base64_encode(json_encode($this->_folderPath))."' data-source='".md5($this->options['root'].$this->options['mode'])."' data-layout='".$this->options['filelayout']."' data-lightboxnav='".$this->options['lightbox_navigation']."' data-query='{$this->options['searchterm']}'>";
                $this->load_scripts('files');

                include sprintf('%s/templates/frontend/search.php', LETSBOX_ROOTDIR);
                echo '</div>';

                break;

            case 'video':
            case 'audio':
                $mediaplayer = $this->load_mediaplayer($this->options['mediaskin']);

                echo "<div id='LetsBox-{$this->listtoken}' class='LetsBox media ".$this->options['mode']." jsdisabled' data-list='media' data-token='{$this->listtoken}' data-id='".$dataid."' data-sort='".$this->options['sort_field'].':'.$this->options['sort_order']."'>";
                $mediaplayer->load_player();
                echo '</div>';
                $this->load_scripts('mediaplayer');

                break;
        }
        echo '</div>';

        // Render module when it becomes available (e.g. when loading dynamically via AJAX)
        echo "<script type='text/javascript'>if (typeof(jQuery) !== 'undefined' && typeof(jQuery.cp) !== 'undefined' && typeof(jQuery.cp.LetsBox) === 'function') { jQuery('#LetsBox-{$this->listtoken}').LetsBox(LetsBox_vars); };</script>";

        do_action('letsbox_after_shortcode', $this);

        $this->load_scripts('general');
    }

    public function render_uploadform()
    {
        $user_can_upload = $this->get_user()->can_upload();

        if (false === $user_can_upload) {
            return;
        }

        $post_max_size_bytes = min(Helpers::return_bytes(ini_get('post_max_size')), Helpers::return_bytes(ini_get('upload_max_filesize')));
        $max_file_size = ('0' !== $this->options['maxfilesize']) ? Helpers::return_bytes($this->options['maxfilesize']) : $post_max_size_bytes;
        $min_file_size = (!empty($this->options['minfilesize'])) ? Helpers::return_bytes($this->options['minfilesize']) : '0';

        $post_max_size_str = Helpers::bytes_to_size_1024($max_file_size);
        $min_file_size_str = Helpers::bytes_to_size_1024($min_file_size);

        $acceptfiletypes = '.('.$this->options['upload_ext'].')$';
        $max_number_of_uploads = $this->options['maxnumberofuploads'];

        $this->load_scripts('upload');

        include sprintf('%s/templates/frontend/upload_box.php', LETSBOX_ROOTDIR);
    }

    public function get_last_folder()
    {
        return $this->_lastFolder;
    }

    public function get_last_path()
    {
        return $this->_lastPath;
    }

    public function get_root_folder()
    {
        return $this->_rootFolder;
    }

    public function get_folder_path()
    {
        return $this->_folderPath;
    }

    public function get_listtoken()
    {
        return $this->listtoken;
    }

    public function load_mediaplayer($mediaplayer)
    {
        if (empty($mediaplayer)) {
            $mediaplayer = $this->get_setting('mediaplayer_skin');
        }

        if (file_exists(LETSBOX_ROOTDIR.'/skins/'.$mediaplayer.'/Player.php')) {
            require_once LETSBOX_ROOTDIR.'/skins/'.$mediaplayer.'/Player.php';
        } else {
            error_log('[WP Cloud Plugin message]: '.sprintf('Media Player Skin %s is missing', $mediaplayer));

            return $this->load_mediaplayer(null);
        }

        try {
            $class = '\TheLion\LetsBox\MediaPlayers\\'.$mediaplayer;

            return new $class($this);
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('Media Player Skin %s is invalid', $mediaplayer));

            return false;
        }
    }

    public function sort_filelist($foldercontents)
    {
        $sort_field = 'name';
        $sort_order = SORT_ASC;

        if (count($foldercontents) > 0) {
            // Sort Filelist, folders first
            $sort = [];

            if (isset($_REQUEST['sort'])) {
                $sort_options = explode(':', $_REQUEST['sort']);

                if ('shuffle' === $sort_options[0]) {
                    shuffle($foldercontents);

                    return $foldercontents;
                }

                if (2 === count($sort_options)) {
                    switch ($sort_options[0]) {
                        case 'name':
                            $sort_field = 'name';

                            break;

                        case 'size':
                            $sort_field = 'size';

                            break;

                        case 'modified':
                            $sort_field = 'last_edited';

                            break;

                        case 'datetaken':
                            $sort_field = 'date_taken';

                            break;
                    }

                    switch ($sort_options[1]) {
                        case 'asc':
                            $sort_order = SORT_ASC;

                            break;

                        case 'desc':
                            $sort_order = SORT_DESC;

                            break;
                    }
                }
            }

            list($sort_field, $sort_order) = apply_filters('letsbox_sort_filelist_settings', [$sort_field, $sort_order], $foldercontents, $this);

            foreach ($foldercontents as $k => $v) {
                if ($v instanceof Entry) {
                    $sort['is_dir'][$k] = $v->is_dir();
                    $sort['sort'][$k] = strtolower($v->{'get_'.$sort_field}());
                } else {
                    $sort['is_dir'][$k] = $v['is_dir'];
                    $sort['sort'][$k] = $v[$sort_field];
                }
            }

            // Sort by dir desc and then by name asc
            array_multisort($sort['is_dir'], SORT_DESC, SORT_REGULAR, $sort['sort'], $sort_order, SORT_NATURAL | SORT_FLAG_CASE, $foldercontents, SORT_ASC);
        }

        $foldercontents = apply_filters('letsbox_sort_filelist', $foldercontents, $sort_field, $sort_order, $this);

        return $foldercontents;
    }

    public function send_notification_email($notification_type, $entries)
    {
        $notification = new Notification($this, $notification_type, $entries);
        $notification->send_notification();
    }

    // Check if $entry is allowed

    public function _is_entry_authorized(CacheNode $cachedentry)
    {
        $entry = $cachedentry->get_entry();

        // Return in case a direct call is being made, and no shortcode is involved
        if (empty($this->options)) {
            return true;
        }

        // Action for custom filters
        $is_authorized_hook = apply_filters('letsbox_is_entry_authorized', true, $cachedentry, $this);
        if (false === $is_authorized_hook) {
            return false;
        }

        // Skip entry if its a file, and we dont want to show files
        if (($entry->is_file()) && ('0' === $this->get_shortcode_option('show_files'))) {
            return false;
        }
        // Skip entry if its a folder, and we dont want to show folders
        if (($entry->is_dir()) && ('0' === $this->get_shortcode_option('show_folders')) && ($entry->get_id() !== $this->get_requested_entry())) {
            return false;
        }

        // Only add allowed files to array
        $extension = $entry->get_extension();
        $allowed_extensions = $this->get_shortcode_option('include_ext');
        if (($entry->is_file()) && (!in_array(strtolower($extension), $allowed_extensions)) && '*' != $allowed_extensions[0]) {
            return false;
        }

        // Hide files with extensions
        $hide_extensions = $this->get_shortcode_option('exclude_ext');
        if (($entry->is_file()) && !empty($extension) && (in_array(strtolower($extension), $hide_extensions)) && '*' != $hide_extensions[0]) {
            return false;
        }

        // skip excluded folders and files
        $hide_entries = $this->get_shortcode_option('exclude');
        if ('*' != $hide_entries[0]) {
            $match = false;
            foreach ($hide_entries as $hide_entry) {
                if (fnmatch($hide_entry, $entry->get_name())) {
                    $match = true;

                    break; // Entry matches by expression (wildcards * , ?)
                }
                if ($hide_entry === $entry->get_id()) {
                    $match = true;

                    break; // Entry matches by ID
                }

                if (fnmatch($hide_entry, $entry->get_mimetype())) {
                    $match = true;

                    break; // Entry matches by Mimetype
                }
            }

            if (true === $match) {
                return false;
            }
        }

        // only allow included folders and files
        $include_entries = $this->get_shortcode_option('include');
        if ('*' != $include_entries[0]) {
            if ($entry->is_dir() && ($entry->get_id() === $this->get_requested_entry())) {
            } else {
                $match = false;
                foreach ($include_entries as $include_entry) {
                    if (fnmatch($include_entry, $entry->get_name())) {
                        $match = true;

                        break; // Entry matches by expression (wildcards * , ?)
                    }
                    if ($include_entry === $entry->get_id()) {
                        $match = true;

                        break; // Entry matches by ID
                    }

                    if (fnmatch($include_entry, $entry->get_mimetype())) {
                        $match = true;

                        break; // Entry matches by Mimetype
                    }
                }

                if (false === $match) {
                    return false;
                }
            }
        }

        // Make sure that files and folders from hidden folders are not allowed
        if ('*' != $hide_entries[0]) {
            foreach ($hide_entries as $hidden_entry) {
                $cached_hidden_entry = $this->get_cache()->get_node_by_name($hidden_entry);

                if (false === $cached_hidden_entry) {
                    $cached_hidden_entry = $this->get_cache()->get_node_by_id($hidden_entry);
                }

                if (false !== $cached_hidden_entry && $cached_hidden_entry->get_entry()->is_dir()) {
                    if ($cachedentry->is_in_folder($cached_hidden_entry->get_id())) {
                        return false;
                    }
                }
            }
        }

        // If only showing Shared Files
        /* if (1) {
          if ($entry->is_file()) {
          if (!$entry->getShared() && $entry->getOwnedByMe()) {
          return false;
          }
          }
          } */

        // Is file in the selected root Folder?
        if (!$cachedentry->is_in_folder($this->get_root_folder())) {
            return false;
        }

        return true;
    }

    public function is_filtering_entries()
    {
        if ('0' === $this->get_shortcode_option('show_files')) {
            return true;
        }

        if ('0' === $this->get_shortcode_option('show_folders')) {
            return true;
        }

        $allowed_extensions = $this->get_shortcode_option('include_ext');
        if ('*' !== $allowed_extensions[0]) {
            return true;
        }

        $hide_extensions = $this->get_shortcode_option('exclude_ext');
        if ('*' !== $hide_extensions[0]) {
            return true;
        }

        $hide_entries = $this->get_shortcode_option('exclude');
        if ('*' !== $hide_entries[0]) {
            return true;
        }
        $include_entries = $this->get_shortcode_option('include');
        if ('*' !== $include_entries[0]) {
            return true;
        }

        return false;
    }

    public function embed_image($entryid)
    {
        $cachedentry = $this->get_client()->get_entry($entryid, false);

        if (false === $cachedentry) {
            return false;
        }

        if (in_array($cachedentry->get_entry()->get_extension(), ['jpg', 'jpeg', 'gif', 'png', 'heic'])) {
            $download = $this->get_client()->download_content($cachedentry);
        }

        return true;
    }

    public function set_requested_entry($entry_id)
    {
        return $this->_requestedEntry = $entry_id;
    }

    public function get_requested_entry()
    {
        return $this->_requestedEntry;
    }

    public function is_mobile()
    {
        return $this->mobile;
    }

    public function get_setting($key)
    {
        if (!isset($this->settings[$key])) {
            return null;
        }

        return $this->settings[$key];
    }

    public function set_setting($key, $value)
    {
        $this->settings[$key] = $value;
        $success = update_option('lets_box_settings', $this->settings);
        $this->settings = get_option('lets_box_settings');

        return $success;
    }

    public function get_shortcode()
    {
        return $this->options;
    }

    public function get_shortcode_option($key)
    {
        if (!isset($this->options[$key])) {
            return null;
        }

        return $this->options[$key];
    }

    public function set_shortcode($listtoken)
    {
        $cached_shortcode = $this->get_shortcodes()->get_shortcode_by_id($listtoken);

        if ($cached_shortcode) {
            $this->options = $cached_shortcode;
            $this->listtoken = $listtoken;
        }

        return $this->options;
    }

    public function _set_gzip_compression()
    {
        // Compress file list if possible
        if ('Yes' === $this->settings['gzipcompression']) {
            $zlib = ('' == ini_get('zlib.output_compression') || !ini_get('zlib.output_compression')) && ('ob_gzhandler' != ini_get('output_handler'));
            if (true === $zlib) {
                if (extension_loaded('zlib')) {
                    if (!in_array('ob_gzhandler', ob_list_handlers())) {
                        ob_start('ob_gzhandler');
                    }
                }
            }
        }
    }

    public function is_network_authorized()
    {
        if (!function_exists('is_plugin_active_for_network')) {
            require_once ABSPATH.'/wp-admin/includes/plugin.php';
        }

        $network_settings = get_site_option('letsbox_network_settings', []);

        return isset($network_settings['network_wide']) && is_plugin_active_for_network(LETSBOX_SLUG) && ('Yes' === $network_settings['network_wide']);
    }

    /**
     * @return \TheLion\LetsBox\Main
     */
    public function get_main()
    {
        return $this->_main;
    }

    /**
     * @return \TheLion\LetsBox\App
     */
    public function get_app()
    {
        if (empty($this->_app)) {
            $this->_app = new \TheLion\LetsBox\App($this);
            $this->_app->start_client();
        }

        return $this->_app;
    }

    /**
     * @return \TheLion\LetsBox\Client
     */
    public function get_client()
    {
        if (empty($this->_client)) {
            $this->_client = new \TheLion\LetsBox\Client($this->get_app(), $this);
        }

        return $this->_client;
    }

    /**
     * @return \TheLion\LetsBox\Cache
     */
    public function get_cache()
    {
        if (empty($this->_cache)) {
            $this->_cache = new \TheLion\LetsBox\Cache($this);
        }

        return $this->_cache;
    }

    /**
     * @return \TheLion\LetsBox\Shortcodes
     */
    public function get_shortcodes()
    {
        if (empty($this->_shortcodes)) {
            $this->_shortcodes = new \TheLion\LetsBox\Shortcodes($this);
        }

        return $this->_shortcodes;
    }

    /**
     * @param mixed $force_reload
     *
     * @return \TheLion\LetsBox\User
     */
    public function get_user($force_reload = false)
    {
        if (empty($this->_user) || $force_reload) {
            $this->_user = new \TheLion\LetsBox\User($this);
        }

        return $this->_user;
    }

    /**
     * @return \TheLion\LetsBox\UserFolders
     */
    public function get_user_folders()
    {
        if (empty($this->_userfolders)) {
            $this->_userfolders = new \TheLion\LetsBox\UserFolders($this);
        }

        return $this->_userfolders;
    }

    public function reset_complete_cache()
    {
        if (!file_exists(LETSBOX_CACHEDIR)) {
            return false;
        }

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(LETSBOX_CACHEDIR, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
            if ($path->isDir()) {
                continue;
            }
            if ('.htaccess' === $path->getFilename()) {
                continue;
            }

            if ('access_token' === $path->getExtension()) {
                continue;
            }

            if ('css' === $path->getExtension()) {
                continue;
            }

            if ('log' === $path->getExtension()) {
                continue;
            }

            if (false !== strpos($path->getPathname(), 'thumbnails')) {
                continue;
            }

            try {
                @unlink($path->getPathname());
            } catch (\Exception $ex) {
                continue;
            }
        }

        return true;
    }

    public function do_shutdown()
    {
        $error = error_get_last();

        if (null === $error) {
            return;
        }

        if (E_ERROR !== $error['type']) {
            return;
        }

        if (isset($error['file']) && false !== strpos($error['file'], LETSBOX_ROOTDIR)) {
            $url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '-unknown-';
            error_log('[WP Cloud Plugin message]: Complete reset; URL: '.$url.';Reason: '.var_export($error, true));

            // fatal error has occured
            $this->get_cache()->reset_cache();
        }
    }

    protected function set_last_path($path)
    {
        $this->_lastPath = $path;
        if ('' === $this->_lastPath) {
            $this->_lastPath = null;
        }

        return $this->_lastPath;
    }

    protected function load_scripts($template)
    {
        if (true === $this->_load_scripts[$template]) {
            return false;
        }

        switch ($template) {
            case 'general':
                wp_enqueue_style('Eva-Icons');
                wp_enqueue_style('LetsBox.CustomStyle');
                wp_enqueue_script('LetsBox');
                wp_enqueue_script('google-recaptcha');

                break;

            case 'files':
                if ($this->get_user()->can_move_files() || $this->get_user()->can_move_folders()) {
                    wp_enqueue_script('jquery-ui-droppable');
                    wp_enqueue_script('jquery-ui-draggable');
                }

                wp_enqueue_script('jquery-effects-core');
                wp_enqueue_script('jquery-effects-fade');
                wp_enqueue_style('ilightbox');
                wp_enqueue_style('ilightbox-skin-letsbox');

                break;

            case 'mediaplayer':
                break;

            case 'upload':
                wp_enqueue_script('jquery-ui-droppable');
                wp_enqueue_script('LetsBox.UploadBox');

                Helpers::append_dependency('LetsBox', 'LetsBox.UploadBox');

                break;
        }

        $this->_load_scripts[$template] = true;
    }

    protected function remove_deprecated_options($options = [])
    {
        // Deprecated options

        // Changed 'ext' to 'include_ext' v1.1
        if (isset($options['ext'])) {
            $options['include_ext'] = $options['ext'];
            unset($options['ext']);
        }

        // Changed 'covers' to 'playlistthumbnails'
        if (isset($options['covers'])) {
            $options['playlistthumbnails'] = $options['covers'];
            unset($options['covers']);
        }

        return $options;
    }

    protected function save_shortcodes()
    {
        $this->get_shortcodes()->set_shortcode($this->listtoken, $this->options);
        $this->get_shortcodes()->update_cache();
    }

    private function _is_action_authorized($hook = false)
    {
        $nonce_verification = ('Yes' === $this->get_setting('nonce_validation'));
        $allow_nonce_verification = apply_filters('lets_box_allow_nonce_verification', $nonce_verification);

        if ($allow_nonce_verification && isset($_REQUEST['action']) && (false === $hook) && is_user_logged_in()) {
            $is_authorized = false;

            switch ($_REQUEST['action']) {
                case 'letsbox-upload-file':
                case 'letsbox-get-filelist':
                case 'letsbox-get-gallery':
                case 'letsbox-get-playlist':
                case 'letsbox-rename-entry':
                case 'letsbox-copy-entry':
                case 'letsbox-move-entries':
                case 'letsbox-edit-description-entry':
                case 'letsbox-create-entry':
                case 'letsbox-create-zip':
                case 'letsbox-delete-entries':
                case 'letsbox-event-log':
                case 'letsbox-shorten-url':
                    $is_authorized = check_ajax_referer($_REQUEST['action'], false, false);

                    break;

                case 'letsbox-create-link':
                case 'letsbox-embedded':
                    $is_authorized = check_ajax_referer('letsbox-create-link', false, false);

                    break;

                case 'letsbox-reset-cache':
                case 'letsbox-factory-reset':
                case 'letsbox-reset-statistics':
                    $is_authorized = check_ajax_referer('letsbox-admin-action', false, false);

                    break;

                case 'letsbox-revoke':
                    $is_authorized = (false !== check_ajax_referer('letsbox-admin-action', false, false));

                    break;

                case 'letsbox-download':
                case 'letsbox-stream':
                case 'letsbox-preview':
                case 'letsbox-thumbnail':
                case 'letsbox-edit':
                case 'letsbox-getpopup':
                case 'letsbox-getads':
                    $is_authorized = true;

                    break;

                case 'edit': // Required for WooCommerce integration
                    $is_authorized = true;

                    break;

                case 'editpost': // Required for Yoast SEO Link Watcher trying to build the shortcode
                case 'elementor':
                case 'elementor_ajax':
                case 'wpseo_filter_shortcodes':
                    return false;

                default:
                    error_log('[WP Cloud Plugin message]: '." Function _is_action_authorized() didn't receive a valid action: ".$_REQUEST['action']);

                    exit();
            }

            if (false === $is_authorized) {
                error_log('[WP Cloud Plugin message]: '." Function _is_action_authorized() didn't receive a valid nonce");

                exit();
            }
        }

        if (!$this->get_app()->has_access_token()) {
            error_log('[WP Cloud Plugin message]: '." Function _is_action_authorized() discovered that the plugin doesn't have an access token");

            return new \WP_Error('broke', '<strong>'.sprintf(esc_html__('%s needs your help!', 'wpcloudplugins'), 'Lets-Box').'</strong> '.esc_html__('Authorize the plugin!', 'wpcloudplugins').'.');
        }

        $this->get_client();

        return true;
    }
}
