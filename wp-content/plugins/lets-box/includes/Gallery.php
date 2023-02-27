<?php

namespace TheLion\LetsBox;

class Gallery
{
    /**
     * @var \TheLion\LetsBox\Processor
     */
    private $_processor;
    private $_search = false;
    private $_parentfolders = [];

    public function __construct(Processor $_processor)
    {
        $this->_processor = $_processor;
    }

    /**
     * @return \TheLion\LetsBox\Processor
     */
    public function get_processor()
    {
        return $this->_processor;
    }

    public function getImagesList()
    {
        $this->_folder = $this->get_processor()->get_client()->get_folder();

        if ((false !== $this->_folder)) {
            $this->imagesarray = $this->createImageArray();
            $this->renderImagesList();
        }
    }

    public function searchImageFiles()
    {
        if ('POST' !== $_SERVER['REQUEST_METHOD']) {
            exit(-1);
        }

        $this->_search = true;
        $_REQUEST['query'] = esc_attr($_REQUEST['query']);
        $input = $_REQUEST['query'];
        $this->_folder = [];
        $this->_folder['folder'] = $this->get_processor()->get_client()->get_entry($this->get_processor()->get_root_folder());
        $this->_folder['contents'] = $this->get_processor()->get_client()->search($input);

        if ((false !== $this->_folder)) {
            // Create Gallery array
            $this->imagesarray = $this->createImageArray();

            $this->renderImagesList();
        }
    }

    public function setFolder($folder)
    {
        $this->_folder = $folder;
    }

    public function setParentFolder()
    {
        if (true === $this->_search) {
            return;
        }

        $currentfolder = $this->_folder['folder']->get_entry()->get_id();
        if ($currentfolder !== $this->get_processor()->get_root_folder()) {
            // Get parent folder from known folder path
            $cacheparentfolder = $this->get_processor()->get_client()->get_entry($this->get_processor()->get_root_folder());
            $folder_path = $this->get_processor()->get_folder_path();
            $parentid = end($folder_path);
            if (false !== $parentid) {
                $cacheparentfolder = $this->get_processor()->get_client()->get_entry($parentid);
            }

            /* Check if parent folder indeed is direct parent of entry
             * If not, return all known parents */
            $parentfolders = [];
            if (false !== $cacheparentfolder && $cacheparentfolder->has_children() && array_key_exists($currentfolder, $cacheparentfolder->get_children())) {
                $parentfolders[] = $cacheparentfolder->get_entry();
            } else {
                if ($this->_folder['folder']->has_parents()) {
                    foreach ($this->_folder['folder']->get_parents() as $parent) {
                        $parentfolders[] = $parent->get_entry();
                    }
                }
            }
            $this->_parentfolders = $parentfolders;
        }
    }

    public function renderImagesList()
    {
        // Create HTML Filelist
        $imageslist_html = '';

        $filescount = 0;
        $folderscount = 0;

        if (count($this->imagesarray) > 0) {
            // Limit the number of files if needed
            if ('-1' !== $this->get_processor()->get_shortcode_option('max_files')) {
                $this->imagesarray = array_slice($this->imagesarray, 0, $this->get_processor()->get_shortcode_option('max_files'));
            }

            $imageslist_html = "<div class='images image-collage'>";
            foreach ($this->imagesarray as $item) {
                // Render folder div
                if ($item->is_dir()) {
                    $imageslist_html .= $this->renderDir($item);

                    $isparent = (isset($this->_folder['folder'])) ? $this->_folder['folder']->is_in_folder($item->get_id()) : false;

                    if (!$isparent) {
                        ++$folderscount;
                    }
                }
            }
        }

        $imageslist_html .= $this->renderNewFolder();

        if (count($this->imagesarray) > 0) {
            $i = 0;
            foreach ($this->imagesarray as $item) {
                // Render file div
                if (!$item->is_dir()) {
                    $imageslist_html .= $this->renderFile($item);
                    ++$filescount;
                }
            }

            $imageslist_html .= '</div>';
        }

        // Create HTML Filelist title
        $file_path = '<ol class="wpcp-breadcrumb">';
        $folder_path = $this->get_processor()->get_folder_path();
        $root_folder_id = $this->get_processor()->get_root_folder();
        if (!isset($this->_folder['folder'])) {
            $this->_folder['folder'] = $this->get_processor()->get_client()->get_entry($this->get_processor()->get_requested_entry());
        }

        $current_id = $this->_folder['folder']->get_entry()->get_id();
        $current_folder_name = $this->_folder['folder']->get_entry()->get_name();

        if ($root_folder_id === $current_id) {
            $file_path .= "<li class='first-breadcrumb'><a href='#{$current_id}' class='folder current_folder' data-id='".$current_id."'>".$this->get_processor()->get_shortcode_option('root_text').'</a></li>';
        } elseif (false === $this->_search || 'parent' === $this->get_processor()->get_shortcode_option('searchfrom')) {
            foreach ($folder_path as $parent_id) {
                if ($parent_id === $root_folder_id) {
                    $file_path .= "<li class='first-breadcrumb'><a href='#{$parent_id}' class='folder' data-id='".$parent_id."'>".$this->get_processor()->get_shortcode_option('root_text').'</a></li>';
                } else {
                    $parent_folder = $this->get_processor()->get_client()->get_folder($parent_id);
                    $parent_folder_name = apply_filters('letsbox_gallery_entry_text', $parent_folder['folder']->get_name(), $parent_folder['folder']->get_entry(), $this);
                    $file_path .= "<li><a href='#{$parent_id}' class='folder' data-id='".$parent_id."'>".$parent_folder_name.'</a></li>';
                }
            }

            $current_folder_name = apply_filters('letsbox_gallery_entry_text', $current_folder_name, $this->_folder['folder']->get_entry(), $this);
            $file_path .= "<li><a href='#{$current_id}' class='folder current_folder' data-id='".$current_id."'>".$current_folder_name.'</a></li>';
        }

        if (true === $this->_search) {
            $file_path .= "<li><a href='javascript:void(0)' class='folder'>".sprintf(esc_html__('Results for %s', 'wpcloudplugins'), "'".$_REQUEST['query']."'").'</a></li>';
        }

        $file_path .= '</ol>';

        // lastFolder contains current folder path of the user
        if (true !== $this->_search && (end($folder_path) !== $this->_folder['folder']->get_entry()->get_id())) {
            $folder_path[] = $this->_folder['folder']->get_entry()->get_id();
        }

        if (true === $this->_search) {
            $lastFolder = $this->get_processor()->get_last_folder();
        } else {
            $lastFolder = $this->_folder['folder']->get_entry()->get_id();
        }

        $response = json_encode([
            'folderPath' => base64_encode(json_encode($folder_path)),
            'accountId' => $this->_folder['folder']->get_account_id(),
            'virtual' => false === $this->_search && $this->_folder['folder']->get_entry()->is_virtual_folder(),
            'lastFolder' => $lastFolder,
            'breadcrumb' => $file_path,
            'html' => $imageslist_html,
            'folderscount' => $folderscount,
            'filescount' => $filescount,
            'hasChanges' => defined('HAS_CHANGES'),
        ]);

        if (false === defined('HAS_CHANGES')) {
            $cached_request = new CacheRequest($this->get_processor());
            $cached_request->add_cached_response($response);
        }

        echo $response;

        exit();
    }

    public function renderDir(Entry $item)
    {
        $return = '';

        $target_height = round($this->get_processor()->get_shortcode_option('targetheight'));
        $target_width = round($target_height * (4 / 3));

        $classmoveable = ($this->get_processor()->get_user()->can_move_folders()) ? 'moveable' : '';
        $isparent = (isset($this->_folder['folder'])) ? $this->_folder['folder']->is_in_folder($item->get_id()) : false;

        if ($isparent) {
            $return .= "<div class='image-container image-folder pf' data-id='".$item->get_id()."' data-name='".$item->get_basename()."'>";
        } else {
            $return .= "<div class='image-container image-folder entry {$classmoveable}' data-id='".$item->get_id()."' data-name='".$item->get_basename()."'>";
        }
        $return .= "<a title='".$item->get_name()."'>";

        $return .= "<div class='preloading'></div>";
        $return .= "<img class='image-folder-img' src='".LETSBOX_ROOTPATH."/css/images/transparant.png' width='{$target_width}' height='{$target_height}' style='width:{$target_width}px !important;height:{$target_height}px !important; '/>";

        if ('1' === $this->get_processor()->get_shortcode_option('folderthumbs')) {
            // Generate Folder Images
            $subfolder = $this->get_processor()->get_client()->get_folder($item->get_id());

            if (!empty($subfolder) && isset($subfolder['contents'])) {
                $maximages = 3;
                $iimages = 0;

                foreach ($subfolder['contents'] as $subfoldernode) {
                    // Only use $maximages for the folder div
                    if ($iimages >= $maximages) {
                        break;
                    }

                    if (!$this->get_processor()->_is_entry_authorized($subfoldernode)) {
                        continue;
                    }
                    // Check if entry has thumbnail
                    $subfolderentry = $subfoldernode->get_entry();
                    if ($subfolderentry->has_own_thumbnail() && $subfolderentry->is_file()) {
                        $thumbnail = $this->get_processor()->get_client()->get_thumbnail($subfolderentry, true, round($target_width * 1.5), round($target_height * 1.5), true, true);
                        $return .= "<div class='folder-thumb thumb{$iimages}' style='width:".$target_width.'px;height:'.$target_height.'px;background-image: url('.$thumbnail.")'></div>";
                        ++$iimages;
                    }
                }
            }
        }

        $text = apply_filters('letsbox_gallery_entry_text', $item->get_name(), $item, $this);
        $return .= "<div class='folder-text'><i class='eva eva-folder'></i>&nbsp;&nbsp;".($isparent ? '<strong>'.esc_html__('Previous folder', 'wpcloudplugins').' ('.$text.')</strong>' : $text).'</div>';
        $return .= '</a>';

        if (!$isparent) {
            $return .= "<div class='entry-info'>";
            $return .= $this->renderDescription($item);
            $return .= $this->renderButtons($item);
            $return .= $this->renderActionMenu($item);

            if ($this->get_processor()->get_user()->can_download_zip() || $this->get_processor()->get_user()->can_delete_files() || $this->get_processor()->get_user()->can_move_files()) {
                $return .= "<div class='entry_checkbox entry-info-button '><input type='checkbox' name='selected-files[]' class='selected-files' value='".$item->get_id()."' id='checkbox-info-{$this->get_processor()->get_listtoken()}-{$item->get_id()}'/><label for='checkbox-info-{$this->get_processor()->get_listtoken()}-{$item->get_id()}'></label></div>";
            }

            $return .= '</div>';
        }

        $return .= "<div class='entry-top-actions'>";

        $return .= $this->renderButtons($item);
        $return .= $this->renderActionMenu($item);

        if ($this->get_processor()->get_user()->can_download_zip() || $this->get_processor()->get_user()->can_delete_folders() || $this->get_processor()->get_user()->can_move_folders()) {
            $return .= "<div class='entry_checkbox entry-info-button '><input type='checkbox' name='selected-files[]' class='selected-files' value='".$item->get_id()."' id='checkbox-{$this->get_processor()->get_listtoken()}-{$item->get_id()}'/><label for='checkbox-{$this->get_processor()->get_listtoken()}-{$item->get_id()}'></label></div>";
        }

        $return .= '</div>';

        $return .= "</div>\n";

        return $return;
    }

    public function renderFile(Entry $item)
    {
        $target_height = $this->get_processor()->get_shortcode_option('targetheight'); // Max thumbnail size

        $classmoveable = ($this->get_processor()->get_user()->can_move_files()) ? 'moveable' : '';

        $return = "<div class='image-container entry {$classmoveable}' data-id='".$item->get_id()."' data-name='".$item->get_name()."'>";

        // API call doesn't return image sizes bu default, so initially crop the images to get this working inside the gallyer grid)
        $height = $target_height;
        $width = $target_height;

        $normal_img_src = $this->get_processor()->get_client()->get_thumbnail($item, true, 0, round($height * 1), true, true);
        $retina_img_src = $this->get_processor()->get_client()->get_thumbnail($item, true, 0, round($height * 2), true, true);

        // If we do have dimension data available, use that instead
        if ($item->get_media('width')) {
            $media_height = $item->get_media('height');
            $media_width = $item->get_media('width');

            if (!empty($media_height) && !empty($media_width)) {
                $width = round(($target_height / $media_height) * $media_width);
                $normal_img_src = $this->get_processor()->get_client()->get_thumbnail($item, true, 0, round($height * 1), false, true);
                $retina_img_src = $this->get_processor()->get_client()->get_thumbnail($item, true, 0, round($height * 2), false, true);
            }
        }

        $thumbnail = 'data-options="thumbnail: \''.$normal_img_src.'\', width: \'85%\', height: \'80%\'"';
        $lightbox_type = 'image';

        $link = LETSBOX_ADMIN_URL.'?action=letsbox-download&id='.urlencode($item->get_id()).'&dl=1&listtoken='.$this->get_processor()->get_listtoken();
        if ('boxthumbnail' === $this->get_processor()->get_setting('loadimages') || false === $this->get_processor()->get_user()->can_download() || 'heic' === $item->get_extension()) {
            $lightbox_type = 'iframe';
            $link = $link = LETSBOX_ADMIN_URL.'?action=letsbox-preview&id='.urlencode($item->get_id()).'&dl=1&listtoken='.$this->get_processor()->get_listtoken();
        }

        $description = htmlentities($item->get_description(), ENT_QUOTES | ENT_HTML401);
        $data_description = ((!empty($item->description)) ? "data-caption='{$description}'" : '');

        $return .= "<a href='".$link."' title='".htmlspecialchars($item->get_basename(), ENT_COMPAT | ENT_HTML401 | ENT_QUOTES)."'  class='ilightbox-group' data-type='{$lightbox_type}' {$thumbnail} rel='ilightbox[".$this->get_processor()->get_listtoken()."]' data-entry-id='{$item->get_id()}' {$data_description}>";

        $return .= "<div class='preloading'></div>";

        $return .= "<img referrerPolicy='no-referrer' class='preloading'  src='".LETSBOX_ROOTPATH."/css/images/transparant.png' data-src='".$normal_img_src."' data-src-retina='".$retina_img_src."' width='{$width}' height='{$height}' style='width:{$width}px !important;height:{$height}px !important;'/>";

        if ('1' === $this->get_processor()->get_shortcode_option('show_filenames')) {
            $text = apply_filters('letsbox_gallery_entry_text', $item->get_basename(), $item, $this);
            $return .= "<div class='entry-text'>".$text.'</div>';
        }

        $return .= '</a>';

        if (false === empty($item->description)) {
            $return .= '<div class="entry-inline-description '.('1' === $this->get_processor()->get_shortcode_option('show_descriptions_on_top') ? ' description-visible ' : '').('1' === $this->get_processor()->get_shortcode_option('show_filenames') ? ' description-above-name ' : '').'"><span>'.nl2br($item->get_description()).'</span></div>';
        }

        $return .= "<div class='entry-info'>";
        $return .= "<div class='entry-info-name'>";
        $caption = apply_filters('letsbox_gallery_lightbox_caption', $item->get_basename(), $item, $this);
        $return .= '<span>'.$caption.'</span></div>';
        $return .= $this->renderButtons($item);
        $return .= "</div>\n";

        $return .= "<div class='entry-top-actions'>";

        if ('1' === $this->get_processor()->get_shortcode_option('show_filenames')) {
            $return .= $this->renderDescription($item);
        }

        $return .= $this->renderButtons($item);
        $return .= $this->renderActionMenu($item);

        if ($this->get_processor()->get_user()->can_download_zip() || $this->get_processor()->get_user()->can_delete_files() || $this->get_processor()->get_user()->can_move_files()) {
            $return .= "<div class='entry_checkbox entry-info-button '><input type='checkbox' name='selected-files[]' class='selected-files' value='".$item->get_id()."' id='checkbox-{$this->get_processor()->get_listtoken()}-{$item->get_id()}'/><label for='checkbox-{$this->get_processor()->get_listtoken()}-{$item->get_id()}'></label></div>";
        }

        $return .= '</div>';
        $return .= "</div>\n";

        return $return;
    }

    public function renderDescription(Entry $item)
    {
        $html = '';

        $has_description = (false === empty($item->description));

        $metadata = [
            'modified' => "<i class='eva eva-clock-outline'></i> ".$item->get_last_edited_str(),
            'size' => ($item->get_size() > 0) ? Helpers::bytes_to_size_1024($item->get_size()) : '',
        ];

        $html .= "<div class='entry-info-button entry-description-button ".(($has_description) ? '-visible' : '')."' tabindex='0'><i class='eva eva-info-outline eva-lg'></i>\n";
        $html .= "<div class='tippy-content-holder'>";
        $html .= "<div class='description-textbox'>";
        $html .= ($has_description) ? "<div class='description-text'>".nl2br($item->get_description()).'</div>' : '';

        $html .= "<div class='description-file-info'>".implode(' &bull; ', array_filter($metadata)).'</div>';

        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    public function renderButtons($item)
    {
        $html = '';

        if ($this->get_processor()->get_user()->can_share()) {
            $html .= "<div class='entry-info-button entry_action_shortlink' title='".esc_html__('Share', 'wpcloudplugins')."' tabindex='0'><i class='eva eva-share-outline eva-lg'></i>\n";
            $html .= '</div>';
        }

        if ($this->get_processor()->get_user()->can_deeplink()) {
            $html .= "<div class='entry-info-button entry_action_deeplink' title='".esc_html__('Direct link', 'wpcloudplugins')."' tabindex='0'><i class='eva eva-link eva-lg'></i>\n";
            $html .= '</div>';
        }

        if ($this->get_processor()->get_user()->can_download() && $item->is_file()) {
            $html .= "<div class='entry-info-button entry_action_download' title='".esc_html__('Download', 'wpcloudplugins')."' tabindex='0'><a href='".LETSBOX_ADMIN_URL.'?action=letsbox-download&id='.$item->get_id().'&dl=1&listtoken='.$this->get_processor()->get_listtoken()."' download='".$item->get_name()."' class='entry_action_download' title='".esc_html__('Download', 'wpcloudplugins')."'><i class='eva eva-download eva-lg'></i></a>\n";
            $html .= '</div>';
        }

        return $html;
    }

    public function renderActionMenu($item)
    {
        $html = '';

        $usercanread = $this->get_processor()->get_user()->can_download() && ($item->is_file() || '1' === $this->get_processor()->get_shortcode_option('can_download_zip'));
        $usercanshare = $this->get_processor()->get_user()->can_share();
        $usercandeeplink = $this->get_processor()->get_user()->can_deeplink();
        $usercanrename = ($item->is_dir()) ? $this->get_processor()->get_user()->can_rename_folders() : $this->get_processor()->get_user()->can_rename_files();
        $usercanmove = ($item->is_dir()) ? $this->get_processor()->get_user()->can_move_folders() : $this->get_processor()->get_user()->can_move_files();
        $usercandelete = ($item->is_dir()) ? $this->get_processor()->get_user()->can_delete_folders() : $this->get_processor()->get_user()->can_delete_files();
        $usercaneditdescription = $this->get_processor()->get_user()->can_edit_description();

        // Download
        if ($usercanread) {
            if ($item->is_file()) {
                $html .= "<li><a href='".LETSBOX_ADMIN_URL.'?action=letsbox-download&id='.$item->get_id().'&dl=1&listtoken='.$this->get_processor()->get_listtoken()."' download='".$item->get_name()."' class='entry_action_download' title='".esc_html__('Download', 'wpcloudplugins')."'><i class='eva eva-download eva-lg'></i>&nbsp;".esc_html__('Download', 'wpcloudplugins').'</a></li>';
            } else {
                $html .= "<li><a class='entry_action_download' download='".$item->get_name()."' data-name='".$item->get_name()."' title='".esc_html__('Download', 'wpcloudplugins')."'><i class='eva eva-download eva-lg'></i>&nbsp;".esc_html__('Download', 'wpcloudplugins').'</a></li>';
            }

            if ($usercaneditdescription || $usercanrename || $usercanmove) {
                $html .= "<li class='list-separator'></li>";
            }
        }

        // Descriptions
        if ($usercaneditdescription) {
            if (empty($item->description)) {
                $html .= "<li><a class='entry_action_description' title='".esc_html__('Add description', 'wpcloudplugins')."'><i class='eva eva-message-square-outline eva-lg'></i>&nbsp;".esc_html__('Add description', 'wpcloudplugins').'</a></li>';
            } else {
                $html .= "<li><a class='entry_action_description' title='".esc_html__('Edit description', 'wpcloudplugins')."'><i class='eva eva-message-square-outline eva-lg'></i>&nbsp;".esc_html__('Edit description', 'wpcloudplugins').'</a></li>';
            }
        }

        // Rename
        if ($usercanrename) {
            $html .= "<li><a class='entry_action_rename' title='".esc_html__('Rename', 'wpcloudplugins')."'><i class='eva eva-edit-2-outline eva-lg'></i>&nbsp;".esc_html__('Rename', 'wpcloudplugins').'</a></li>';
        }

        // Move
        if ($usercanmove) {
            $html .= "<li><a class='entry_action_move' title='".esc_html__('Move to', 'wpcloudplugins')."'><i class='eva eva-corner-down-right eva-lg'></i>&nbsp;".esc_html__('Move to', 'wpcloudplugins').'</a></li>';
        }

        // Delete
        if ($usercandelete) {
            $html .= "<li class='list-separator'></li>";
            $html .= "<li><a class='entry_action_delete' title='".esc_html__('Delete', 'wpcloudplugins')."'><i class='eva eva-trash-2-outline eva-lg'></i>&nbsp;".esc_html__('Delete', 'wpcloudplugins').'</a></li>';
        }

        if ('' !== $html) {
            return "<div class='entry-info-button entry-action-menu-button' title='".esc_html__('More actions', 'wpcloudplugins')."' tabindex='0'><i class='eva eva-more-vertical-outline'></i><div id='menu-".$item->get_id()."' class='entry-action-menu-button-content tippy-content-holder'><ul data-id='".$item->get_id()."' data-name='".$item->get_basename()."'>".$html."</ul></div></div>\n";
        }

        return $html;
    }

    public function renderNewFolder()
    {
        $html = '';

        if (
            false === $this->get_processor()->get_user()->can_add_folders()
            || true === $this->_search
            || '1' === $this->get_processor()->get_shortcode_option('show_breadcrumb')
            ) {
            return $html;
        }

        $height = $this->get_processor()->get_shortcode_option('targetheight');
        $html .= "<div class='image-container image-folder image-add-folder grey newfolder'>";
        $html .= "<a title='".esc_html__('Add folder', 'wpcloudplugins')."'>";
        $html .= "<img class='preloading' src='".LETSBOX_ROOTPATH."/css/images/transparant.png' data-src='".plugins_url('css/images/gallery-add-folder.png', dirname(__FILE__))."' data-src-retina='".plugins_url('css/images/gallery-add-folder.png', dirname(__FILE__))."' width='{$height}' height='{$height}' style='width:".$height.'px;height:'.$height."px;'/>";
        $html .= "<div class='folder-text'><i class='eva eva-folder-add-outline eva-lg'></i>&nbsp;&nbsp;".esc_html__('Add folder', 'wpcloudplugins').'</div>';
        $html .= '</a>';
        $html .= "</div>\n";

        return $html;
    }

    public function createImageArray()
    {
        $imagearray = [];

        $this->setParentFolder();

        // Add folders and files to filelist
        if (count($this->_folder['contents']) > 0) {
            foreach ($this->_folder['contents'] as $node) {
                $child = $node->get_entry();

                // Check if entry is allowed
                if (!$this->get_processor()->_is_entry_authorized($node)) {
                    continue;
                }

                // Check if entry has thumbnail
                if (!$child->has_own_thumbnail() && $child->is_file()) {
                    continue;
                }

                $imagearray[] = $child;
            }

            $imagearray = $this->get_processor()->sort_filelist($imagearray);
        }

        // Add 'back to Previous folder' if needed
        if (isset($this->_folder['folder'])) {
            $folder = $this->_folder['folder']->get_entry();

            $add_parent_folder_item = true;

            if ($this->_search || $folder->get_id() === $this->get_processor()->get_root_folder()) {
                $add_parent_folder_item = false;
            } elseif ('1' === $this->get_processor()->get_shortcode_option('show_breadcrumb')) {
                $add_parent_folder_item = false;
            }

            if ($add_parent_folder_item) {
                foreach ($this->_parentfolders as $parentfolder) {
                    array_unshift($filesarray, $parentfolder);
                }
            }
        }

        return $imagearray;
    }
}
