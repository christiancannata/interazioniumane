<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2022, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\LetsBox;

class Processor
{
    public $options = [];
    public $mobile = false;
    public $settings = [];

    /**
     * The single instance of the class.
     *
     * @var Processor
     */
    protected static $_instance;
    protected $listtoken = '';
    protected $_rootFolder;
    protected $_lastFolder;
    protected $_folderPath;
    protected $_requestedEntry;
    protected $_load_scripts = ['general' => false, 'files' => false, 'upload' => false, 'mediaplayer' => false, 'carousel' => false];

    /**
     * Construct the plugin object.
     */
    public function __construct()
    {
        register_shutdown_function([static::class, 'do_shutdown']);

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

    /**
     * Processor Instance.
     *
     * Ensures only one instance is loaded or can be loaded.
     *
     * @return Processor - Processor instance
     *
     * @static
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function start_process()
    {
        if (!isset($_REQUEST['action'])) {
            error_log('[WP Cloud Plugin message]: '." Function startProcess() requires an 'action' request");

            exit;
        }

        if (isset($_REQUEST['account_id'])) {
            $requested_account = Accounts::instance()->get_account_by_id($_REQUEST['account_id']);
            if (null !== $requested_account) {
                App::set_current_account($requested_account);
            } else {
                error_log(sprintf('[WP Cloud Plugin message]: '." Function start_process() cannot use the requested account (ID: %s) as it isn't linked with the plugin", $_REQUEST['account_id']));

                exit;
            }
        }

        do_action('letsbox_before_start_process', $_REQUEST['action'], $this);

        $authorized = $this->_is_action_authorized();

        if ((true === $authorized) && ('letsbox-revoke' === $_REQUEST['action'])) {
            $data = ['account_id' => App::get_current_account()->get_id(), 'action' => 'revoke', 'success' => false];
            if (Helpers::check_user_role($this->settings['permissions_edit_settings'])) {
                if (null === App::get_current_account()) {
                    echo json_encode($data);

                    exit;
                }

                if ('true' === $_REQUEST['force']) {
                    Accounts::instance()->remove_account(App::get_current_account()->get_id());
                } else {
                    App::instance()->revoke_token(App::get_current_account());
                }

                $data['success'] = true;
            }

            echo json_encode($data);

            exit;
        }

        if (!isset($_REQUEST['listtoken'])) {
            $url = Helpers::get_page_url();
            $request = $_SERVER['REQUEST_URI'] ?? '';
            error_log('[WP Cloud Plugin message]: '." Function start_process() requires a 'listtoken' on {$url} requested via {$request}");
            error_log(var_export($_REQUEST, true));

            exit;
        }

        $this->listtoken = $_REQUEST['listtoken'];
        $this->options = Shortcodes::instance()->get_shortcode_by_id($this->listtoken);

        if (false === $this->options) {
            $url = Helpers::get_page_url();
            error_log('[WP Cloud Plugin message]:  Function start_process('.$_REQUEST['action'].") hasn't received a valid listtoken (".$this->listtoken.") on: {$url} \n");

            exit;
        }

        if (false === User::can_view()) {
            $url = Helpers::get_page_url();
            $request = $_SERVER['REQUEST_URI'] ?? '';
            error_log('[WP Cloud Plugin message]: '." Function start_process() discovered that an user didn't have the permission to view the plugin on {$url} requested via {$request}");

            exit;
        }

        if (null === App::get_current_account() || false === App::get_current_account()->get_authorization()->has_access_token()) {
            error_log('[WP Cloud Plugin message]: '." Function _is_action_authorized() discovered that the plugin doesn't have an access token");

            return new \WP_Error('broke', '<strong>'.sprintf(esc_html__('%s needs your help!', 'wpcloudplugins'), 'Lets-Box').'</strong> '.esc_html__('Authorize the plugin!', 'wpcloudplugins').'.');
        }

        Client::instance();

        // Remove all cache files for current shortcode when refreshing, otherwise check for new changes
        if (defined('FORCE_REFRESH')) {
            CacheRequest::clear_request_cache();
            Cache::instance()->reset_cache();
        } else {
            // Pull for changes if needed
            Cache::instance()->pull_for_changes();
        }

        // Set rootFolder
        if ('manual' === $this->options['user_upload_folders']) {
            $userfolder = UserFolders::instance()->get_manually_linked_folder_for_user();
            if (is_wp_error($userfolder) || false === $userfolder) {
                error_log('[WP Cloud Plugin message]: Cannot find a manually linked folder for user');

                exit('-1');
            }
            $this->_rootFolder = $userfolder->get_id();
        } elseif (('auto' === $this->options['user_upload_folders']) && !Helpers::check_user_role($this->options['view_user_folders_role'])) {
            $userfolder = UserFolders::instance()->get_auto_linked_folder_for_user();

            if (is_wp_error($userfolder) || false === $userfolder) {
                error_log('[WP Cloud Plugin message]: Cannot find a auto linked folder for user');

                exit('-1');
            }
            $this->_rootFolder = $userfolder->get_id();
        } else {
            $this->_rootFolder = $this->options['root'];
        }

        // Open Sub Folder if needed
        if (!empty($this->options['subfolder']) && '/' !== $this->options['subfolder']) {
            $sub_folder_path = apply_filters('letsbox_set_subfolder_path', Helpers::apply_placeholders($this->options['subfolder'], $this), $this->options, $this);
            $subfolder = API::get_sub_folder_by_path($this->_rootFolder, $sub_folder_path, true);

            if (is_wp_error($subfolder) || false === $subfolder) {
                error_log('[WP Cloud Plugin message]: Cannot find or create the subfolder');

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
                $cached_request = new CacheRequest();
                if ($cached_request->is_cached()) {
                    echo $cached_request->get_cached_response();

                    exit;
                }
            }
        }

        do_action('letsbox_start_process', $_REQUEST['action'], $this);

        switch ($_REQUEST['action']) {
            case 'letsbox-get-filelist':
                $filebrowser = new Filebrowser();

                if (isset($_REQUEST['query']) && !empty($_REQUEST['query']) && '1' === $this->options['search']) { // Search files
                    $filelist = $filebrowser->searchFiles();
                } else {
                    $filelist = $filebrowser->getFilesList(); // Read folder
                }

                break;

            case 'letsbox-preview':
                $file = Client::instance()->preview_entry();

                break;

            case 'letsbox-edit':
                if (false === User::can_edit()) {
                    exit;
                }

                $file = Client::instance()->edit_entry();

                break;

            case 'letsbox-thumbnail':
                $file = Client::instance()->build_thumbnail();

                break;

            case 'letsbox-download':
                if (false === User::can_download()) {
                    exit;
                }
                Client::instance()->download_entry();

                break;

            case 'letsbox-stream':
                Client::instance()->stream_entry();

                break;

            case 'letsbox-shorten-url':
                if (false === User::can_deeplink()) {
                    exit;
                }

                $cached_node = Client::instance()->get_entry();
                $url = esc_url_raw($_REQUEST['url']);

                $shortened_url = API::shorten_url($url, null, ['name' => $cached_node->get_name()]);

                $data = [
                    'id' => $cached_node->get_id(),
                    'name' => $cached_node->get_name(),
                    'url' => $shortened_url,
                ];

                echo json_encode($data);

                exit;

            case 'letsbox-create-zip':
                if (false === User::can_download()) {
                    exit;
                }

                switch ($_REQUEST['type']) {
                    case 'do-zip':
                        $zip = new Zip();
                        $zip->do_zip();

                        exit;

                        break;
                }

                break;

            case 'letsbox-create-link':
            case 'letsbox-embedded':
                if (isset($_REQUEST['entries'])) {
                    foreach ($_REQUEST['entries'] as $entry_id) {
                        $link['links'][] = Client::instance()->get_shared_link_for_output($entry_id);
                    }
                } else {
                    $link = Client::instance()->get_shared_link_for_output();
                }
                echo json_encode($link);

                break;

            case 'letsbox-get-gallery':
                if (is_wp_error($authorized)) {
                    echo json_encode(['lastpath' => '', 'folder' => '', 'html' => '']);

                    exit;
                }

                switch ($_REQUEST['type']) {
                    case 'carousel':
                        $carousel = new Carousel($this);
                        $carousel->get_images_list();

                        break;

                    default:
                        $gallery = new Gallery();

                        if (isset($_REQUEST['query']) && !empty($_REQUEST['query']) && '1' === $this->options['search']) { // Search files
                            $imagelist = $gallery->searchImageFiles();
                        } else {
                            $imagelist = $gallery->getImagesList(); // Read folder
                        }

                        break;
                }

                break;

            case 'letsbox-upload-file':
                $user_can_upload = User::can_upload();

                if (false === $user_can_upload) {
                    exit;
                }

                $upload_processor = new Upload();

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

                exit;

            case 'letsbox-delete-entries':
                // Check if user is allowed to delete entry
                $user_can_delete = User::can_delete_files() || User::can_delete_folders();

                if (false === $user_can_delete) {
                    echo json_encode(['result' => '-1', 'msg' => esc_html__('Failed to delete file.', 'wpcloudplugins')]);

                    exit;
                }

                $entries_to_delete = [];
                foreach ($_REQUEST['entries'] as $requested_id) {
                    $entries_to_delete[] = $requested_id;
                }

                $entries = Client::instance()->delete_entries($entries_to_delete);

                foreach ($entries as $entry) {
                    if (false === $entry) {
                        echo json_encode(['result' => '-1', 'msg' => esc_html__('Not all files could be deleted.', 'wpcloudplugins')]);

                        exit;
                    }
                }
                echo json_encode(['result' => '1', 'msg' => esc_html__('File was deleted.', 'wpcloudplugins')]);

                exit;

            case 'letsbox-rename-entry':
                // Check if user is allowed to rename entry
                $user_can_rename = User::can_rename_files() || User::can_rename_folders();

                if (false === $user_can_rename) {
                    echo json_encode(['result' => '-1', 'msg' => esc_html__('Failed to rename file.', 'wpcloudplugins')]);

                    exit;
                }

                // Strip unsafe characters
                $newname = rawurldecode($_REQUEST['newname']);
                $new_filename = Helpers::filter_filename($newname, false);

                $file = Client::instance()->rename_entry($new_filename);

                if (is_wp_error($file)) {
                    echo json_encode(['result' => '-1', 'msg' => $file->get_error_message()]);
                } else {
                    echo json_encode(['result' => '1', 'msg' => esc_html__('File was renamed.', 'wpcloudplugins')]);
                }

                exit;

            case 'letsbox-copy-entries':
                // Check if user is allowed to copy entry
                $user_can_copy = User::can_copy_files() || User::can_copy_folders();

                if (false === $user_can_copy) {
                    echo json_encode(['result' => '-1', 'msg' => esc_html__('Failed to copy file.', 'wpcloudplugins')]);

                    exit;
                }

                $entries_to_copy = [];
                foreach ($_REQUEST['entries'] as $requested_id) {
                    $entries_to_copy[] = $requested_id;
                }

                $entries = Client::instance()->move_entries($entries_to_copy, $_REQUEST['target'], true);

                foreach ($entries as $entry) {
                    if (is_wp_error($entry) || empty($entry)) {
                        echo json_encode(['result' => '-1', 'msg' => esc_html__('Not all files could be copied.', 'wpcloudplugins')]);

                        exit;
                    }
                }
                echo json_encode(['result' => '1', 'msg' => esc_html__('Successfully copied to new location', 'wpcloudplugins')]);

                exit;

            case 'letsbox-move-entries':
                // Check if user is allowed to move entry
                $user_can_move = User::can_move_files() || User::can_move_folders();

                if (false === $user_can_move) {
                    echo json_encode(['result' => '-1', 'msg' => esc_html__('Failed to move file.', 'wpcloudplugins')]);

                    exit;
                }

                $entries_to_move = [];
                foreach ($_REQUEST['entries'] as $requested_id) {
                    $entries_to_move[] = $requested_id;
                }

                $entries = Client::instance()->move_entries($entries_to_move, $_REQUEST['target']);

                foreach ($entries as $entry) {
                    if (is_wp_error($entry) || empty($entry)) {
                        echo json_encode(['result' => '-1', 'msg' => esc_html__('Not all files could be moved.', 'wpcloudplugins')]);

                        exit;
                    }
                }
                echo json_encode(['result' => '1', 'msg' => esc_html__('Successfully moved to new location.', 'wpcloudplugins')]);

                exit;

            case 'letsbox-edit-description-entry':
                // Check if user is allowed to rename entry
                $user_can_editdescription = User::can_edit_description();

                if (false === $user_can_editdescription) {
                    echo json_encode(['result' => '-1', 'msg' => esc_html__('Failed to edit description.', 'wpcloudplugins')]);

                    exit;
                }

                $newdescription = sanitize_textarea_field(wp_unslash($_REQUEST['newdescription']));
                $result = Client::instance()->update_description($newdescription);

                if (is_wp_error($result)) {
                    echo json_encode(['result' => '-1', 'msg' => $result->get_error_message()]);
                } else {
                    echo json_encode(['result' => '1', 'msg' => esc_html__('Description was edited.', 'wpcloudplugins'), 'description' => $result]);
                }

                exit;

            case 'letsbox-create-entry':
                // Strip unsafe characters
                $_name = rawurldecode($_REQUEST['name']);
                $new_name = Helpers::filter_filename($_name, false);
                $mimetype = $_REQUEST['mimetype'];

                // Check if user is allowed
                $user_can_create_entry = User::can_add_folders();

                if (false === $user_can_create_entry) {
                    echo json_encode(['result' => '-1', 'msg' => esc_html__('Failed to add file.', 'wpcloudplugins')]);

                    exit;
                }

                $file = Client::instance()->add_folder($new_name);

                if (is_wp_error($file)) {
                    echo json_encode(['result' => '-1', 'msg' => $file->get_error_message()]);
                } else {
                    echo json_encode(['result' => '1', 'msg' => $new_name.' '.esc_html__('was added', 'wpcloudplugins'), 'lastFolder' => $file->get_id()]);
                }

                exit;

            case 'letsbox-get-playlist':
                $mediaplayer = new Mediaplayer();
                $playlist = $mediaplayer->getMediaList();

                break;

            case 'letsbox-event-log':
                return;

            case 'letsbox-getads':
                $ads_url = ('' !== $this->get_shortcode_option('ads_tag_url') ? htmlspecialchars_decode($this->get_shortcode_option('ads_tag_url')) : $this->get_setting('mediaplayer_ads_tagurl'));
                $response = wp_remote_get($ads_url);
                header('Content-Type: text/xml; charset=UTF-8');
                echo wp_remote_retrieve_body($response);

                exit;

            default:
                error_log('[WP Cloud Plugin message]: '.sprintf('No valid AJAX request: %s', $_REQUEST['action']));

                exit('Lets-Box: '.esc_html__('No valid AJAX request.', 'wpcloudplugins'));
        }

        exit;
    }

    public function create_from_shortcode($atts)
    {
        $atts = (is_string($atts)) ? [] : $atts;
        $atts = $this->remove_deprecated_options($atts);

        $defaults = [
            'singleaccount' => '1',
            'account' => false,
            'startaccount' => false,
            'dir' => false,
            'subfolder' => false,
            'class' => '',
            'startid' => false,
            'mode' => 'files',
            'userfolders' => 'off',
            'usertemplatedir' => '',
            'viewuserfoldersrole' => 'administrator',
            'userfoldernametemplate' => '',
            'maxuserfoldersize' => '-1',
            'showfiles' => '1',
            'maxfiles' => '-1',
            'showfolders' => '1',
            'filesize' => '1',
            'filedate' => '1',
            'fileinfo_on_hover' => '0',
            'hoverthumbs' => '1',
            'filedescription' => '0',
            'filelayout' => 'grid',
            'allow_switch_view' => '1',
            'showext' => '1',
            'sortfield' => 'name',
            'sortorder' => 'asc',
            'show_header' => '1',
            'showbreadcrumb' => '1',
            'candownloadzip' => '0',
            'lightboxnavigation' => '1',
            'lightboxthumbs' => '1',
            'showsharelink' => '0',
            'share_password' => '',
            'share_expire_after' => '',
            'share_allow_download' => '1',
            'showrefreshbutton' => '1',
            'use_custom_roottext' => '1',
            'roottext' => esc_html__('Start', 'wpcloudplugins'),
            'search' => '1',
            'searchrole' => 'all',
            'searchfrom' => 'parent',
            'searchterm' => '',
            'searchcontents' => '1',
            'include' => '*',
            'includeext' => '*',
            'exclude' => '*',
            'excludeext' => '*',
            'maxwidth' => '100%',
            'maxheight' => '',
            'scrolltotop' => '1',
            'viewrole' => 'administrator|editor|author|contributor|subscriber|guest',
            'previewrole' => 'all',
            'downloadrole' => 'administrator|editor|author|contributor|subscriber|guest',
            'sharerole' => 'all',
            'edit' => '0',
            'editrole' => 'administrator|editor|author',
            'previewinline' => '1',
            'quality' => '90',
            'maximages' => '25',
            'lightbox_open' => '0',
            'slideshow' => '0',
            'pausetime' => '5000',
            'showfilenames' => '0',
            'show_descriptions' => '0',
            'showdescriptionsontop' => '0',
            'targetheight' => '300',
            'folderthumbs' => '0',
            'mediaskin' => '',
            'mediabuttons' => 'prevtrack|playpause|nexttrack|volume|current|duration|fullscreen',
            'media_ratio' => '16:9',
            'autoplay' => '0',
            'showplaylist' => '1',
            'showplaylistonstart' => '1',
            'playlistinline' => '0',
            'playlistautoplay' => '1',
            'playlistthumbnails' => '1',
            'playlist_search' => '0',
            'linktoshop' => '',
            'ads' => '0',
            'ads_tag_url' => '',
            'ads_skipable' => '1',
            'ads_skipable_after' => '',
            'axis' => 'horizontal',
            'padding' => '',
            'border_radius' => '',
            'description_position' => 'hover',
            'navigation_dots' => '1',
            'navigation_arrows' => '1',
            'slide_items' => '3',
            'slide_height' => '300px',
            'slide_by' => '1',
            'slide_speed' => '300',
            'slide_center' => '0',
            'slide_auto_size' => '0',
            'carousel_autoplay' => '1',
            'pausetime' => '5000',
            'hoverpause' => '0',
            'direction' => 'forward',
            'notificationupload' => '0',
            'notificationdownload' => '0',
            'notificationdeletion' => '0',
            'notificationemail' => '%admin_email%',
            'notification_skipemailcurrentuser' => '0',
            'notification_from_name' => '',
            'notification_from_email' => '',
            'notification_replyto_email' => '',
            'upload' => '0',
            'upload_folder' => '1',
            'upload_auto_start' => '1',
            'upload_filename' => '%file_name%%file_extension%',
            'upload_create_shared_link' => '0',
            'upload_button_text' => '',
            'upload_button_text_plural' => '',
            'overwrite' => '0',
            'uploadext' => '.',
            'uploadrole' => 'administrator|editor|author|contributor|subscriber',
            'minfilesize' => '0',
            'maxfilesize' => '0',
            'maxnumberofuploads' => '-1',
            'delete' => '0',
            'deletefilesrole' => 'administrator|editor',
            'deletefoldersrole' => 'administrator|editor',
            'rename' => '0',
            'renamefilesrole' => 'administrator|editor',
            'renamefoldersrole' => 'administrator|editor',
            'move' => '0',
            'movefilesrole' => 'administrator|editor',
            'movefoldersrole' => 'administrator|editor',
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
            'popup' => '0',
            'post_id' => empty($atts['popup']) ? get_the_ID() : null,
            'themestyle' => 'default',
            'demo' => '0',
        ];

        // Read shortcode & Create a unique identifier
        $shortcode = shortcode_atts($defaults, $atts, 'letsbox');
        $this->listtoken = md5(serialize($defaults).serialize($shortcode));
        extract($shortcode);

        $cached_shortcode = Shortcodes::instance()->get_shortcode_by_id($this->listtoken);

        if (false === $cached_shortcode) {
            $authorized = $this->_is_action_authorized();

            if (is_wp_error($authorized)) {
                return '&#9888; <strong>'.esc_html__('Module cannot be rendered due to an authorization issue. Contact the administrator to get access.', 'wpcloudplugins').'</strong>';
            }

            switch ($mode) {
                case 'gallery':
                    $includeext = ('*' == $includeext) ? 'gif|jpg|jpeg|png|bmp|tif|tiff|webp|heic|mp4|m4v|ogg|ogv|webmv' : $includeext;
                    $uploadext = ('.' == $uploadext) ? 'gif|jpg|jpeg|png|bmp|tif|tiff|webp|heic|mp4|m4v|ogg|ogv|webmv' : $uploadext;

                    break;

                case 'carousel':
                    $includeext = ('*' == $includeext) ? 'gif|jpg|jpeg|png|bmp|tif|tiff|webp|heic' : $includeext;

                    break;

                case 'search':
                    $searchfrom = 'root';

                    break;

                default:
                    break;
            }

            if (!empty($account)) {
                $singleaccount = '1';
            }

            if ('0' === $singleaccount) {
                $dir = '0';
                $account = false;
            }

            if (empty($account)) {
                $primary_account = Accounts::instance()->get_primary_account();
                if (null !== $primary_account) {
                    $account = $primary_account->get_id();
                }
            }

            $account_class = Accounts::instance()->get_account_by_id($account);
            if (null === $account_class || false === $account_class->get_authorization()->is_valid()) {
                error_log('[WP Cloud Plugin message]: Module cannot be rendered as the requested account is not linked with the plugin');

                return '&#9888; <strong>'.esc_html__('Module cannot be rendered as the requested content is not (longer) accessible. Contact the administrator to get access.', 'wpcloudplugins').'</strong>';
            }

            App::set_current_account($account_class);

            $rootfolder = API::get_root_folder();

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
            $deletefilesrole = explode('|', $deletefilesrole);
            $deletefoldersrole = explode('|', $deletefoldersrole);
            $renamefilesrole = explode('|', $renamefilesrole);
            $renamefoldersrole = explode('|', $renamefoldersrole);
            $movefilesrole = explode('|', $movefilesrole);
            $movefoldersrole = explode('|', $movefoldersrole);
            $copyfilesrole = explode('|', $copyfilesrole);
            $copyfoldersrole = explode('|', $copyfoldersrole);
            $editdescriptionrole = explode('|', $editdescriptionrole);
            $addfolderrole = explode('|', $addfolderrole);
            $createdocumentrole = explode('|', $createdocumentrole);
            $viewuserfoldersrole = explode('|', $viewuserfoldersrole);
            $deeplinkrole = explode('|', $deeplinkrole);
            $mediabuttons = explode('|', $mediabuttons);
            $searchrole = explode('|', $searchrole);

            $this->options = [
                'single_account' => $singleaccount,
                'account' => $account,
                'startaccount' => $startaccount,
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
                'media_ratio' => $media_ratio,
                'autoplay' => $autoplay,
                'showplaylist' => $showplaylist,
                'showplaylistonstart' => $showplaylistonstart,
                'playlistinline' => $playlistinline,
                'playlistautoplay' => $playlistautoplay,
                'playlistthumbnails' => $playlistthumbnails,
                'playlist_search' => $playlist_search,
                'linktoshop' => $linktoshop,
                'ads' => $ads,
                'ads_tag_url' => $ads_tag_url,
                'ads_skipable' => $ads_skipable,
                'ads_skipable_after' => $ads_skipable_after,
                'show_files' => $showfiles,
                'show_folders' => $showfolders,
                'show_filesize' => $filesize,
                'show_filedate' => $filedate,
                'fileinfo_on_hover' => $fileinfo_on_hover,
                'hover_thumbs' => $hoverthumbs,
                'show_description' => $filedescription,
                'max_files' => $maxfiles,
                'filelayout' => $filelayout,
                'allow_switch_view' => $allow_switch_view,
                'show_ext' => $showext,
                'sort_field' => $sortfield,
                'sort_order' => $sortorder,
                'show_header' => $show_header,
                'show_breadcrumb' => $showbreadcrumb,
                'can_download_zip' => $candownloadzip,
                'lightbox_navigation' => $lightboxnavigation,
                'lightbox_thumbnails' => $lightboxthumbs,
                'show_sharelink' => $showsharelink,
                'share_password' => $share_password,
                'share_expire_after' => $share_expire_after,
                'share_allow_download' => $share_allow_download,
                'show_refreshbutton' => $showrefreshbutton,
                'use_custom_roottext' => $use_custom_roottext,
                'root_text' => $roottext,
                'search' => $search,
                'search_role' => $searchrole,
                'searchfrom' => $searchfrom,
                'searchterm' => $searchterm,
                'searchcontents' => $searchcontents,
                'include' => explode('|', htmlspecialchars_decode($include)),
                'include_ext' => explode('|', strtolower($includeext)),
                'exclude' => explode('|', htmlspecialchars_decode($exclude)),
                'exclude_ext' => explode('|', strtolower($excludeext)),
                'maxwidth' => $maxwidth,
                'maxheight' => $maxheight,
                'scrolltotop' => $scrolltotop,
                'view_role' => $viewrole,
                'preview_role' => $previewrole,
                'download_role' => $downloadrole,
                'share_role' => $sharerole,
                'edit' => $edit,
                'edit_role' => $editrole,
                'previewinline' => ('none' === $previewrole) ? '0' : $previewinline,
                'maximages' => $maximages,
                'axis' => $axis,
                'padding' => $padding,
                'border_radius' => $border_radius,
                'description_position' => $description_position,
                'navigation_dots' => $navigation_dots,
                'navigation_arrows' => $navigation_arrows,
                'slide_items' => $slide_items,
                'slide_height' => $slide_height,
                'slide_by' => $slide_by,
                'slide_speed' => $slide_speed,
                'slide_center' => $slide_center,
                'slide_auto_size' => $slide_auto_size,
                'carousel_autoplay' => $carousel_autoplay,
                'pausetime' => $pausetime,
                'hoverpause' => $hoverpause,
                'direction' => $direction,
                'notificationupload' => $notificationupload,
                'notificationdownload' => $notificationdownload,
                'notificationdeletion' => $notificationdeletion,
                'notificationemail' => $notificationemail,
                'notification_skip_email_currentuser' => $notification_skipemailcurrentuser,
                'notification_from_name' => $notification_from_name,
                'notification_from_email' => $notification_from_email,
                'notification_replyto_email' => $notification_replyto_email,
                'upload' => $upload,
                'upload_folder' => $upload_folder,
                'upload_auto_start' => $upload_auto_start,
                'upload_filename' => $upload_filename,
                'upload_create_shared_link' => $upload_create_shared_link,
                'upload_button_text' => $upload_button_text,
                'upload_button_text_plural' => $upload_button_text_plural,
                'overwrite' => $overwrite,
                'upload_ext' => strtolower($uploadext),
                'upload_role' => $uploadrole,
                'minfilesize' => $minfilesize,
                'maxfilesize' => Helpers::return_bytes($maxfilesize),
                'maxnumberofuploads' => $maxnumberofuploads,
                'delete' => $delete,
                'delete_files_role' => $deletefilesrole,
                'delete_folders_role' => $deletefoldersrole,
                'rename' => $rename,
                'rename_files_role' => $renamefilesrole,
                'rename_folders_role' => $renamefoldersrole,
                'move' => $move,
                'move_files_role' => $movefilesrole,
                'move_folders_role' => $movefoldersrole,
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
                'show_descriptions' => $show_descriptions,
                'show_descriptions_on_top' => $showdescriptionsontop,
                'targetheight' => $targetheight,
                'folderthumbs' => $folderthumbs,
                'lightbox_open' => $lightbox_open,
                'slideshow' => $slideshow,
                'pausetime' => $pausetime,
                'popup' => $popup,
                'post_id' => $post_id,
                'themestyle' => $themestyle,
                'demo' => $demo,
                'expire' => strtotime('+1 weeks'),
                'listtoken' => $this->listtoken, ];

            $this->options = apply_filters('letsbox_shortcode_add_options', $this->options, $this, $atts);

            $this->save_shortcodes();

            $this->options = apply_filters('letsbox_shortcode_set_options', $this->options, $this, $atts);

            // Create userfolders if needed

            if ('auto' === $this->options['user_upload_folders']) {
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

                    $userfolder = UserFolders::instance()->create_user_folders($allusers);
                }
            }
        } else {
            $this->options = apply_filters('letsbox_shortcode_set_options', $cached_shortcode, $this, $atts);
        }

        if (empty($this->options['startaccount'])) {
            App::set_current_account_by_id($this->options['account']);
        } else {
            App::set_current_account_by_id($this->options['startaccount']);
        }

        if (null === App::get_current_account() || false === App::get_current_account()->get_authorization()->has_access_token()) {
            return '&#9888; <strong>'.esc_html__('Module cannot be rendered as the requested content is not (longer) accessible. Contact the administrator to get access.', 'wpcloudplugins').'</strong>';
        }

        ob_start();
        $this->render_template();

        return ob_get_clean();
    }

    public function render_template()
    {
        // Reload User Object for this new shortcode
        $user = User::reset();

        if (false === User::can_view()) {
            do_action('letsbox_shortcode_no_view_permission', $this);

            return;
        }

        // Update Unique ID
        update_option('lets_box_uniqueID', get_option('lets_box_uniqueID', 0) + 1);

        // Render the  template
        $dataid = '';
        $dataaccountid = (false !== $this->options['startaccount']) ? $this->options['startaccount'] : $this->options['account'];

        if ('manual' === $this->options['user_upload_folders']) {
            $userfolder = get_user_option('lets_box_linkedto');
            if (is_array($userfolder) && isset($userfolder['folderid'])) {
                $dataaccountid = $userfolder['accountid'];
            } else {
                $defaultuserfolder = get_site_option('lets_box_guestlinkedto');
                if (is_array($defaultuserfolder) && isset($defaultuserfolder['folderid'])) {
                    $dataaccountid = $defaultuserfolder['accountid'];
                } else {
                    echo "<div id='LetsBox'>";
                    $this->load_scripts('general');

                    include sprintf('%s/templates/frontend/noaccess.php', LETSBOX_ROOTDIR);
                    echo '</div>';

                    return;
                }
            }

            $linked_account = Accounts::instance()->get_account_by_id($dataaccountid);
            App::set_current_account($linked_account);
        }

        $dataorgid = $dataid;
        $dataid = (false !== $this->options['startid']) ? $this->options['startid'] : $dataid;

        $shortcode_class = ('shortcode_buider' === $this->options['popup']) ? 'initiate' : '';
        $shortcode_class .= ('1' === $this->options['scrolltotop']) ? ' wpcp-has-scroll-to-top' : '';

        if ('0' !== $this->options['popup']) {
            $shortcode_class .= ' wpcp-theme-light';
        } else {
            $shortcode_class .= ('default' !== $this->options['themestyle']) ? ' wpcp-theme-'.$this->options['themestyle'] : '';
        }

        do_action('letsbox_before_shortcode', $this);

        echo "<div id='LetsBox' class='{$this->options['class']} {$this->options['mode']} {$shortcode_class}' style='display:none'>";
        echo "<noscript><div class='LetsBox-nojsmessage'>".esc_html__('To view this content, you need to have JavaScript enabled in your browser', 'wpcloudplugins').'.<br/>';
        echo "<a href='http://www.enable-javascript.com/' target='_blank'>".esc_html__('To do so, please follow these instructions', 'wpcloudplugins').'</a>.</div></noscript>';

        switch ($this->options['mode']) {
            case 'files':
                $this->load_scripts('files');

                echo "<div id='LetsBox-{$this->listtoken}' class='wpcp-module LetsBox files jsdisabled ".('grid' === $this->options['filelayout'] ? 'wpcp-thumb-view' : 'wpcp-list-view')."' data-list='files' data-token='{$this->listtoken}' data-account-id='{$dataaccountid}' data-id='".$dataid."' data-path='".base64_encode(json_encode($this->_folderPath))."' data-sort='".$this->options['sort_field'].':'.$this->options['sort_order']."' data-org-id='".$dataorgid."' data-org-path='".base64_encode(json_encode($this->_folderPath))."' data-source='".md5($this->options['account'].$this->options['root'].$this->options['mode'])."' data-layout='".$this->options['filelayout']."' data-lightboxnav='".$this->options['lightbox_navigation']."' data-lightboxthumbs='{$this->options['lightbox_thumbnails']}' data-query='{$this->options['searchterm']}' data-action='{$this->options['popup']}'>";

                include sprintf('%s/templates/frontend/file_browser.php', LETSBOX_ROOTDIR);
                $this->render_uploadform();

                echo '</div>';

                break;

            case 'upload':
                echo "<div id='LetsBox-{$this->listtoken}' class='wpcp-module LetsBox upload jsdisabled'  data-token='{$this->listtoken}' data-account-id='{$dataaccountid}' data-id='".$dataid."' data-path='".base64_encode(json_encode($this->_folderPath))."' >";
                $this->render_uploadform();
                echo '</div>';

                break;

            case 'gallery':
                $this->load_scripts('files');

                $nextimages = '';
                if ('0' !== $this->options['maximages']) {
                    $nextimages = "data-loadimages='".$this->options['maximages']."'";
                }

                echo "<div id='LetsBox-{$this->listtoken}' class='wpcp-module LetsBox wpcp-gallery jsdisabled' data-list='gallery' data-token='{$this->listtoken}' data-account-id='{$dataaccountid}' data-id='".$dataid."' data-path='".base64_encode(json_encode($this->_folderPath))."' data-sort='".$this->options['sort_field'].':'.$this->options['sort_order']."' data-org-id='".$dataid."' data-org-path='".base64_encode(json_encode($this->_folderPath))."' data-source='".md5($this->options['account'].$this->options['root'].$this->options['mode'])."' data-targetheight='".$this->options['targetheight']."' data-slideshow='".$this->options['slideshow']."' data-pausetime='".$this->options['pausetime']."' {$nextimages} data-lightboxnav='".$this->options['lightbox_navigation']."' data-lightboxthumbs='{$this->options['lightbox_thumbnails']}' data-lightboxopen='{$this->options['lightbox_open']}' data-query='{$this->options['searchterm']}'>";

                include sprintf('%s/templates/frontend/gallery.php', LETSBOX_ROOTDIR);
                $this->render_uploadform();
                echo '</div>';

                break;

            case 'carousel':
                $this->load_scripts('carousel');

                echo "<div id='LetsBox-{$this->listtoken}' class='wpcp-module LetsBox carousel jsdisabled' data-list='carousel' data-token='{$this->listtoken}' data-account-id='{$dataaccountid}' data-id='".$dataid."' data-path='".base64_encode(json_encode($this->_folderPath))."' data-sort='".$this->options['sort_field'].':'.$this->options['sort_order']."' data-org-id='".$dataid."' data-org-path='".base64_encode(json_encode($this->_folderPath))."' data-source='".md5($this->options['account'].$this->options['root'].$this->options['mode'])."'>";

                include sprintf('%s/templates/frontend/carousel.php', LETSBOX_ROOTDIR);
                echo '</div>';

                break;

            case 'search':
                echo "<div id='LetsBox-{$this->listtoken}' class='wpcp-module LetsBox files searchlist jsdisabled' data-list='search' data-token='{$this->listtoken}' data-account-id='{$dataaccountid}' data-id='".$dataid."' data-path='".base64_encode(json_encode($this->_folderPath))."' data-sort='".$this->options['sort_field'].':'.$this->options['sort_order']."' data-org-id='".$dataorgid."' data-org-path='".base64_encode(json_encode($this->_folderPath))."' data-source='".md5($this->options['account'].$this->options['root'].$this->options['mode'])."' data-layout='".$this->options['filelayout']."' data-lightboxnav='".$this->options['lightbox_navigation']."' data-lightboxthumbs='{$this->options['lightbox_thumbnails']}' data-query='{$this->options['searchterm']}'>";
                $this->load_scripts('files');

                include sprintf('%s/templates/frontend/search.php', LETSBOX_ROOTDIR);
                echo '</div>';

                break;

            case 'video':
            case 'audio':
                $mediaplayer = $this->load_mediaplayer($this->options['mediaskin']);

                echo "<div id='LetsBox-{$this->listtoken}' class='wpcp-module LetsBox media ".$this->options['mode']." jsdisabled' data-list='media' data-token='{$this->listtoken}'   data-account-id='{$dataaccountid}' data-id='".$dataid."' data-sort='".$this->options['sort_field'].':'.$this->options['sort_order']."' data-source='".md5($this->options['account'].$this->options['root'].$this->options['mode'])."' data-layout='".$this->options['filelayout']."'>";
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
        $user_can_upload = User::can_upload();

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

            return new $class();
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
        $notification = new Notification($notification_type, $entries);
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
        if ($entry->is_file() && ('0' === $this->get_shortcode_option('show_files'))) {
            return false;
        }
        // Skip entry if its a folder, and we dont want to show folders
        if ($entry->is_dir() && ('0' === $this->get_shortcode_option('show_folders')) && ($entry->get_id() !== $this->get_requested_entry())) {
            return false;
        }

        // Only add allowed files to array
        $extension = $entry->get_extension();
        $allowed_extensions = $this->get_shortcode_option('include_ext');
        if ('*' != $allowed_extensions[0] && $entry->is_file() && (empty($extension) || (!in_array(strtolower($extension), $allowed_extensions)))) {
            return false;
        }

        // Hide files with extensions
        $hide_extensions = $this->get_shortcode_option('exclude_ext');
        if ('*' != $hide_extensions[0] && $entry->is_file() && !empty($extension) && in_array(strtolower($extension), $hide_extensions)) {
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
                $cached_hidden_entry = Cache::instance()->get_node_by_name($hidden_entry);

                if (false === $cached_hidden_entry) {
                    $cached_hidden_entry = Cache::instance()->get_node_by_id($hidden_entry);
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
        $cachedentry = Client::instance()->get_entry($entryid, false);

        if (false === $cachedentry) {
            return false;
        }

        if (in_array($cachedentry->get_entry()->get_extension(), ['jpg', 'jpeg', 'gif', 'png', 'webp', 'heic'])) {
            $download = Client::instance()->download_content($cachedentry);
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

  public function get_network_setting($key, $default = null)
  {
      $network_settings = get_site_option('letsbox_network_settings', []);

      if (!isset($network_settings[$key])) {
          return $default;
      }

      return $network_settings[$key];
  }

    public function set_network_setting($key, $value)
    {
        $network_settings = get_site_option('letsbox_network_settings', []);
        $network_settings[$key] = $value;

        return update_site_option('letsbox_network_settings', $network_settings);
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
        $cached_shortcode = Shortcodes::instance()->get_shortcode_by_id($listtoken);

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

    public static function reset_complete_cache($including_shortcodes = false, $including_thumbnails = false)
    {
        if (!file_exists(LETSBOX_CACHEDIR)) {
            return false;
        }

        require_once ABSPATH.'wp-admin/includes/class-wp-filesystem-base.php';

        require_once ABSPATH.'wp-admin/includes/class-wp-filesystem-direct.php';

        $wp_file_system = new \WP_Filesystem_Direct(false);

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(LETSBOX_CACHEDIR, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
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

            if (false === $including_shortcodes && 'shortcodes' === $path->getExtension()) {
                continue;
            }

            if (false !== strpos($path->getPathname(), 'thumbnails') && false === $including_thumbnails) {
                continue;
            }

            if ('index' === $path->getExtension()) {
                // index files can be locked during purge request
                $fp = fopen($path->getPathname(), 'w');

                if (flock($fp, LOCK_EX)) {
                    ftruncate($fp, 0);
                    flock($fp, LOCK_UN);
                }
            }

            try {
                $wp_file_system->delete($path->getPathname(), true);
            } catch (\Exception $ex) {
                continue;
            }
        }

        return true;
    }

    public static function do_shutdown()
    {
        $error = error_get_last();

        if (null === $error) {
            return;
        }

        if (E_ERROR !== $error['type']) {
            return;
        }

        if (isset($error['file']) && false !== strpos($error['file'], LETSBOX_ROOTDIR)) {
            $url = Helpers::get_page_url();
            error_log('[WP Cloud Plugin message]: Complete reset; URL: '.$url.';Reason: '.var_export($error, true));

            // fatal error has occured
            Cache::instance()->reset_cache();
        }
    }

    /**
     * @deprecated
     *
     * @return \TheLion\LetsBox\Cache
     */
    public function get_cache()
    {
        Helpers::is_deprecated('function', 'get_cache()', '\TheLion\LetsBox\Cache::instance()');

        return Cache::instance();
    }

    /**
     * @deprecated
     *
     * @return \TheLion\LetsBox\UserFolders
     */
    public function get_user_folders()
    {
        Helpers::is_deprecated('function', 'get_cache()', '\TheLion\LetsBox\UserFolders::instance()');

        return UserFolders::instance();
    }

    /**
     * @deprecated
     *
     * @return \TheLion\LetsBox\Shortcodes
     */
    public function get_shortcodes()
    {
        Helpers::is_deprecated('function', 'get_shortcodes()', '\TheLion\LetsBox\Shortcodes::instance()');

        return Shortcodes::instance();
    }

    /**
     * @deprecated
     *
     * @return \TheLion\LetsBox\Account
     */
    public function set_current_account(Account $account)
    {
        Helpers::is_deprecated('function', 'set_current_account()', '\TheLion\LetsBox\App::set_current_account($account) or \TheLion\LetsBox\App::set_current_account_by_id($account_id)');

        return App::set_current_account($account);
    }

    /**
     * @deprecated
     *
     * @return \TheLion\LetsBox\Account
     */
    public function get_current_account()
    {
        Helpers::is_deprecated('function', 'get_current_account()', '\TheLion\LetsBox\App::get_current_account()');

        return App::get_current_account();
    }

    /**
     * @deprecated
     *
     * @return \TheLion\LetsBox\App
     */
    public function get_app()
    {
        Helpers::is_deprecated('function', 'get_app()', '\TheLion\LetsBox\App::instance()');

        return App::instance();
    }

    /**
     * @deprecated
     *
     * @return \TheLion\LetsBox\Client
     */
    public function get_client()
    {
        Helpers::is_deprecated('function', 'get_client()', '\TheLion\LetsBox\Client::instance()');

        return Client::instance();
    }

    /**
     * @deprecated
     *
     * @return \TheLion\LetsBox\User
     */
    public function get_user()
    {
        Helpers::is_deprecated('function', 'get_user()', '\TheLion\LetsBox\User::instance()');

        return User::instance();
    }

    /**
     * @deprecated
     *
     * @return \TheLion\LetsBox\Accounts
     */
    public function get_accounts()
    {
        Helpers::is_deprecated('function', 'get_accounts()', '\TheLion\LetsBox\Accounts::instance()');

        return Accounts::instance();
    }

    protected function load_scripts($template)
    {
        if (true === $this->_load_scripts[$template]) {
            return false;
        }

        Core::instance()->load_scripts();
        Core::instance()->load_styles();

        switch ($template) {
            case 'general':
                wp_enqueue_style('Eva-Icons');
                wp_enqueue_style('LetsBox');
                wp_enqueue_script('LetsBox');
                wp_enqueue_script('google-recaptcha');

                break;

            case 'files':
                if (User::can_move_files() || User::can_move_folders()) {
                    wp_enqueue_script('jquery-ui-droppable');
                    wp_enqueue_script('jquery-ui-draggable');
                }

                wp_enqueue_script('jquery-effects-core');
                wp_enqueue_script('jquery-effects-fade');
                wp_enqueue_style('ilightbox');
                wp_enqueue_style('ilightbox-skin-letsbox');

                break;

            case 'carousel':
                wp_enqueue_script('LetsBox.Carousel');

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

        if (isset($options['userfolders']) && '0' === $options['userfolders']) {
            $options['userfolders'] = 'off';
        }

        if (!empty($options['mode']) && in_array($options['mode'], ['video', 'audio'])) {
            if (isset($options['linktomedia'])) {
                if ('0' === $options['linktomedia']) {
                    $options['downloadrole'] = empty($options['downloadrole']) ? 'none' : $options['downloadrole'];
                } else {
                    $options['downloadrole'] = empty($options['downloadrole']) ? 'all' : $options['downloadrole'];
                }
                unset($options['linktomedia']);
            }
        }

        if (isset($options['allowpreview']) && '0' === $options['allowpreview']) {
            unset($options['allowpreview']);
            $options['previewrole'] = 'none';
        }

        if (isset($options['upload_filename_prefix'])) {
            $options['upload_filename'] = $options['upload_filename_prefix'].(isset($options['upload_filename']) ? $options['upload_filename'] : '%file_name%%file_extension%');
            unset($options['upload_filename_prefix']);
        }

        if (isset($options['delete_role'])) {
            $options['deletefilesrole'] = $options['delete_role'];
            $options['deletefoldersrole'] = $options['delete_role'];
            unset($options['delete_role']);
        }

        if (isset($options['rename_role'])) {
            $options['renamefilesrole'] = $options['rename_role'];
            $options['renamefoldersrole'] = $options['rename_role'];
            unset($options['rename_role']);
        }

        if (isset($options['move_role'])) {
            $options['movefilesrole'] = $options['move_role'];
            $options['movefoldersrole'] = $options['move_role'];
            unset($options['move_role']);
        }

        if (isset($options['hideplaylist'])) {
            $options['showplaylist'] = '0' !== $options['hideplaylist'];
        }

        if (isset($options['mcepopup'])) {
            $options['popup'] = $options['mcepopup'];
            unset($options['mcepopup']);
        }

        return $options;
    }

    protected function save_shortcodes()
    {
        Shortcodes::instance()->set_shortcode($this->listtoken, $this->options);
        Shortcodes::instance()->update_cache();
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
                case 'letsbox-copy-entries':
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

                case 'editpost': // Required for integrations
                case 'wpseo_filter_shortcodes':
                case 'elementor':
                case 'elementor_ajax':
                case 'frm_insert_field':
                    return false;

                default:
                    error_log('[WP Cloud Plugin message]: '." Function _is_action_authorized() didn't receive a valid action: ".$_REQUEST['action']);

                    exit;
            }

            if (false === $is_authorized) {
                error_log('[WP Cloud Plugin message]: '." Function _is_action_authorized() didn't receive a valid nonce");

                exit;
            }
        }

        return true;
    }
}
