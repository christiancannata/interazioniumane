<?php

namespace TheLion\LetsBox;

class Filebrowser
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

    public function getFilesList()
    {
        $this->_folder = $this->get_processor()->get_client()->get_folder();

        if ((false !== $this->_folder)) {
            $this->filesarray = $this->createFilesArray();
            $this->renderFilelist();
        } else {
            exit('Folder is not received');
        }
    }

    public function searchFiles()
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
            $this->filesarray = $this->createFilesArray();

            $this->renderFilelist();
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

    public function renderFilelist()
    {
        // Create HTML Filelist
        $filelist_html = '';

        $breadcrumb_class = ('1' === $this->get_processor()->get_shortcode_option('show_breadcrumb')) ? 'has-breadcrumb' : 'no-breadcrumb';

        $filescount = 0;
        $folderscount = 0;

        $filelist_html = "<div class='files {$breadcrumb_class}'>";
        $filelist_html .= "<div class='folders-container'>";

        if (count($this->filesarray) > 0) {
            // Limit the number of files if needed
            if ('-1' !== $this->get_processor()->get_shortcode_option('max_files')) {
                $this->filesarray = array_slice($this->filesarray, 0, $this->get_processor()->get_shortcode_option('max_files'));
            }

            foreach ($this->filesarray as $item) {
                // Render folder div
                if ($item->is_dir()) {
                    $filelist_html .= $this->renderDir($item);

                    $isparent = (isset($this->_folder['folder'])) ? $this->_folder['folder']->is_in_folder($item->get_id()) : false;

                    if (!$isparent) {
                        ++$folderscount;
                    }
                }
            }
        }

        if (false === $this->_search && false === $this->_folder['folder']->get_entry()->is_virtual_folder()) {
            $filelist_html .= $this->renderNewFolder();
        }

        $filelist_html .= "</div><div class='files-container'>";

        if (count($this->filesarray) > 0) {
            foreach ($this->filesarray as $item) {
                // Render files div
                if ($item->is_file()) {
                    $filelist_html .= $this->renderFile($item);
                    ++$filescount;
                }
            }
        }

        $filelist_html .= '</div></div>';

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
                    $file_path .= "<li><a href='#{$parent_id}'class='folder' data-id='".$parent_id."'>".$parent_folder['folder']->get_name().'</a></li>';
                }
            }
            $file_path .= "<li><a href='#{$current_id}' class='folder current_folder' data-id='".$current_id."'>".$current_folder_name.'</a></li>';
        }

        if (true === $this->_search) {
            $file_path .= "<li><a href='javascript:void(0)' class='folder'>".sprintf(esc_html__('Results for %s', 'wpcloudplugins'), "'".$_REQUEST['query']."'").'</a></li>';
        }

        $file_path .= '</ol>';

        $raw_path = '';
        if ((true !== $this->_search) && (current_user_can('edit_posts') || current_user_can('edit_pages')) && ('true' == get_user_option('rich_editing'))) {
            $raw_path = $this->_folder['folder']->get_entry()->get_name();
        }

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
            'rawpath' => $raw_path,
            'folderPath' => base64_encode(json_encode($folder_path)),
            'accountId' => $this->_folder['folder']->get_account_id(),
            'virtual' => false === $this->_search && $this->_folder['folder']->get_entry()->is_virtual_folder(),
            'lastFolder' => $lastFolder,
            'breadcrumb' => $file_path,
            'html' => $filelist_html,
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

        $classmoveable = ($this->get_processor()->get_user()->can_move_folders() || $this->get_processor()->get_user()->can_move_folders()) ? 'moveable' : '';
        $isparent = (isset($this->_folder['folder'])) ? $this->_folder['folder']->is_in_folder($item->get_id()) : false;

        $return .= "<div class='entry {$classmoveable} folder ".($isparent ? 'pf' : '')."' data-id='".$item->get_id()."' data-name='".htmlspecialchars($item->get_basename(), ENT_QUOTES | ENT_HTML401, 'UTF-8')."'>\n";
        if (!$isparent) {
            if ('linkto' === $this->get_processor()->get_shortcode_option('mcepopup') || 'linktobackendglobal' === $this->get_processor()->get_shortcode_option('mcepopup')) {
                $return .= "<div class='entry_linkto'>\n";
                $return .= '<span>'."<input class='button-secondary' type='submit' title='".esc_html__('Select folder', 'wpcloudplugins')."' value='".esc_html__('Select folder', 'wpcloudplugins')."'>".'</span>';
                $return .= '</div>';
            }
        }

        $return .= "<div class='entry_block'>\n";

        $return .= "<div class='entry-info'>";

        $thumburl = $isparent ? LETSBOX_ICON_SET.'256x256/prev.png' : $item->get_icon_large();
        $return .= "<div class='entry-info-icon'><div class='preloading'></div><img class='preloading' src='".LETSBOX_ROOTPATH."/css/images/transparant.png' data-src='{$thumburl}' data-src-retina='{$thumburl}'/></div>";

        $return .= "<div class='entry-info-name'>";
        $return .= "<a class='entry_link' title='{$item->get_basename()}'>";
        $return .= '<span>';
        $return .= (($isparent) ? '<strong>'.esc_html__('Previous folder', 'wpcloudplugins').'</strong>' : $item->get_name()).' </span>';
        $return .= '</span>';
        $return .= '</a></div>';

        if (!$isparent) {
            $return .= $this->renderTags($item);

            $return .= $this->renderDescription($item);
            $return .= $this->renderActionMenu($item);
            $return .= $this->renderCheckBox($item);
        }

        $return .= "</div>\n";

        $return .= "</div>\n";
        $return .= "</div>\n";

        return $return;
    }

    public function renderFile(Entry $item)
    {
        $link = $this->renderFileNameLink($item);
        $title = $link['filename'].((('1' === $this->get_processor()->get_shortcode_option('show_filesize')) && ($item->get_size() > 0)) ? ' ('.Helpers::bytes_to_size_1024($item->get_size()).')' : '&nbsp;');

        $classmoveable = ($this->get_processor()->get_user()->can_move_files()) ? 'moveable' : '';

        if ($item->get_size() < 5242880) {
            $thumbnail_medium = $this->get_processor()->get_client()->get_thumbnail($item, true, 500, 500, false, true);
        } else {
            $thumbnail_medium = $this->get_processor()->get_client()->get_thumbnail($item, true, 500, 500, false, false);
        }

        $has_tooltip = ($item->has_own_thumbnail() && !empty($thumbnail_medium) && ('shortcode' !== $this->get_processor()->get_shortcode_option('mcepopup')) && ('1' === $this->get_processor()->get_shortcode_option('hover_thumbs'))) ? "data-tooltip=''" : '';

        $return = '';
        $return .= "<div class='entry file {$classmoveable}' data-id='".$item->get_id()."' data-name='".htmlspecialchars($item->get_basename(), ENT_QUOTES | ENT_HTML401, 'UTF-8')."' {$has_tooltip}>\n";
        $return .= "<div class='entry_block'>\n";

        $return .= "<div class='entry_thumbnail'><div class='entry_thumbnail-view-bottom'><div class='entry_thumbnail-view-center'>\n";

        $return .= "<div class='preloading'></div>";
        $return .= "<img referrerPolicy='no-referrer' class='preloading' src='".LETSBOX_ROOTPATH."/css/images/transparant.png' data-src='".$thumbnail_medium."' data-src-retina='".$thumbnail_medium."' data-src-backup='".$item->get_icon_large()."'/>";
        $return .= "</div></div></div>\n";

        $return .= "<div class='entry-info'>";
        $return .= "<div class='entry-info-icon'><img src='".$item->get_icon()."'/></div>";
        $return .= "<div class='entry-info-name'>";
        $return .= '<a '.$link['url'].' '.$link['target']." class='entry_link ".$link['class']."' ".$link['onclick']." title='".$title."' ".$link['lightbox']." data-name='".$link['filename']."' data-entry-id='{$item->get_id()}'>";

        $return .= '<span>'.$link['filename'].'</span>';

        $return .= '</a>';

        if (('shortcode' === $this->get_processor()->get_shortcode_option('mcepopup')) && (in_array($item->get_extension(), ['mp4', 'm4v', 'ogg', 'ogv', 'webmv', 'mp3', 'm4a', 'oga', 'wav', 'webm']))) {
            $return .= "&nbsp;<a class='entry_media_shortcode'><i class='eva eva-code'></i></a>";
        }

        $return .= '</div>';

        $return .= $this->renderDescriptionText($item);
        $return .= $this->renderModifiedDate($item);
        $return .= $this->renderSize($item);
        $return .= $this->renderTags($item);
        $return .= $this->renderDescription($item);
        $return .= $this->renderActionMenu($item);
        $return .= $this->renderCheckBox($item);
        $return .= "</div>\n";

        $return .= $link['lightbox_inline'];

        $return .= "</div>\n";
        $return .= "</div>\n";

        return $return;
    }

    public function renderSize(EntryAbstract $item)
    {
        if ('1' === $this->get_processor()->get_shortcode_option('show_filesize')) {
            $size = ($item->get_size() > 0) ? Helpers::bytes_to_size_1024($item->get_size()) : '&nbsp;';

            return "<div class='entry-info-size entry-info-metadata'>".$size.'</div>';
        }
    }

    public function renderModifiedDate(EntryAbstract $item)
    {
        if ('1' === $this->get_processor()->get_shortcode_option('show_filedate')) {
            return "<div class='entry-info-modified-date entry-info-metadata'>".$item->get_last_edited_str().'</div>';
        }
    }

    public function renderDescriptionText(EntryAbstract $item)
    {
        if ('1' === $this->get_processor()->get_shortcode_option('show_description')) {
            return "<div class='entry-info-entry-description-text entry-info-metadata'>".$item->get_description().'</div>';
        }
    }

    public function renderCheckBox(EntryAbstract $item)
    {
        $checkbox = '';

        if ($item->is_dir()) {
            if ($this->get_processor()->get_user()->can_download_zip() || $this->get_processor()->get_user()->can_delete_folders() || $this->get_processor()->get_user()->can_move_folders()) {
                $checkbox .= "<div class='entry-info-button entry_checkbox'><input type='checkbox' name='selected-files[]' class='selected-files' value='".$item->get_id()."' id='checkbox-{$this->get_processor()->get_listtoken()}-{$item->get_id()}'/><label for='checkbox-{$this->get_processor()->get_listtoken()}-{$item->get_id()}'></label></div>";
            }

            if ((in_array($this->get_processor()->get_shortcode_option('mcepopup'), ['links', 'embedded']))) {
                $checkbox .= "<div class='entry-info-button entry_checkbox'><input type='checkbox' name='selected-files[]' class='selected-files' value='".$item->get_id()."' id='checkbox-{$this->get_processor()->get_listtoken()}-{$item->get_id()}'/><label for='checkbox-{$this->get_processor()->get_listtoken()}-{$item->get_id()}'></label></div>";
            }
        } else {
            if ($this->get_processor()->get_user()->can_download_zip() || $this->get_processor()->get_user()->can_delete_files() || $this->get_processor()->get_user()->can_move_files()) {
                $checkbox .= "<div class='entry-info-button entry_checkbox'><input type='checkbox' name='selected-files[]' class='selected-files' value='".$item->get_id()."' id='checkbox-{$this->get_processor()->get_listtoken()}-{$item->get_id()}'/><label for='checkbox-{$this->get_processor()->get_listtoken()}-{$item->get_id()}'></label></div>";
            }

            if ((in_array($this->get_processor()->get_shortcode_option('mcepopup'), ['links', 'embedded']))) {
                $checkbox .= "<div class='entry-info-button entry_checkbox'><input type='checkbox' name='selected-files[]' class='selected-files' value='".$item->get_id()."' id='checkbox-{$this->get_processor()->get_listtoken()}-{$item->get_id()}'/><label for='checkbox-{$this->get_processor()->get_listtoken()}-{$item->get_id()}'></label></div>";
            }
        }

        return $checkbox;
    }

    public function renderFileNameLink(Entry $item)
    {
        $class = '';
        $url = '';
        $target = '';
        $onclick = '';
        $lightbox = '';
        $lightbox_inline = '';
        $datatype = 'iframe';
        $filename = ('1' === $this->get_processor()->get_shortcode_option('show_ext')) ? $item->get_name() : $item->get_basename();

        // Check if user is allowed to preview the file
        $usercanpreview = $this->get_processor()->get_user()->can_preview() && '1' === $this->get_processor()->get_shortcode_option('allow_preview');

        if (
                $item->is_dir()
                || false === $item->get_can_preview_by_cloud()
                || 'zip' === $item->get_extension()
                || false === $this->get_processor()->get_user()->can_view()
        ) {
            $usercanpreview = false;
        }

        if ($usercanpreview && ('0' === $this->get_processor()->get_shortcode_option('mcepopup'))) {
            $url = LETSBOX_ADMIN_URL.'?action=letsbox-preview&id='.($item->get_id()).'&listtoken='.$this->get_processor()->get_listtoken();

            // Check if we need to preview inline
            if ('1' === $this->get_processor()->get_shortcode_option('previewinline')) {
                $class = 'entry_link ilightbox-group';
                $onclick = "sendAnalyticsLB('Preview', '{$item->get_name()}');";

                if (in_array($item->get_extension(), ['mp4', 'm4v', 'ogg', 'ogv', 'webmv', 'mp3', 'm4a', 'ogg', 'oga']) && 'personal' === $this->get_processor()->get_app()->get_account_type()) {
                    $datatype = 'inline';
                    $url = LETSBOX_ADMIN_URL.'?action=letsbox-stream&id='.($item->get_id()).'&listtoken='.$this->get_processor()->get_listtoken();

                    if ($this->get_processor()->get_client()->has_temporarily_link($item)) {
                        $url = $this->get_processor()->get_client()->get_temporarily_link($item);
                    }
                }

                // Lightbox Settings
                $lightbox = "rel='ilightbox[".$this->get_processor()->get_listtoken()."]' ";
                $lightbox .= 'data-type="'.$datatype.'"';

                switch ($datatype) {
                    case 'image':
                        break;

                    case 'inline':
                        $id = 'ilightbox_'.$this->get_processor()->get_listtoken().'_'.md5($item->get_id());
                        $html5_element = (false === strpos($item->get_mimetype(), 'video')) ? 'audio' : 'video';
                        $icon = $this->get_processor()->get_client()->get_thumbnail($item, true, 160, 160, false, false);
                        $thumbnail = $this->get_processor()->get_client()->get_thumbnail($item, true, 320, 320, false, false);

                        $lightbox_size = (false !== strpos($item->get_mimetype(), 'audio')) ? 'width: \'85%\',' : 'width: \'85%\', height: \'85%\',';
                        $lightbox .= ' data-options="mousewheel: false, swipe:false, '.$lightbox_size.' thumbnail: \''.$thumbnail.'\'"';

                        $download = 'controlsList="nodownload"';
                        $lightbox_inline = '<div id="'.$id.'" class="html5_player" style="display:none;"><'.$html5_element.' controls '.$download.' preload="metadata"  poster="'.$thumbnail.'"> <source data-src="'.$url.'" type="'.$item->get_mimetype().'">'.esc_html__('Your browser does not support HTML5. You can only download this file', 'wpcloudplugins').'</'.$html5_element.'></div>';
                        $url = '#'.$id;

                        break;

                    case 'iframe':
                        $lightbox .= ' data-options="mousewheel: false, width: \'85%\', height: \'80%\'"';
                        // no break
                    default:
                        break;
                }
            } else {
                $url .= '&inline=0';
                $class = 'entry_action_external_view';
                $target = '_blank';
                $onclick = "sendAnalyticsLB('Preview  (new window)', '{$item->get_name()}');";
            }
        } elseif (('0' === $this->get_processor()->get_shortcode_option('mcepopup')) && $this->get_processor()->get_user()->can_download()) {
            // Check if user is allowed to download file

            $url = LETSBOX_ADMIN_URL.'?action=letsbox-download&id='.($item->get_id()).'&listtoken='.$this->get_processor()->get_listtoken();
            $class = 'entry_action_download';

            // Weblinks/Bookmarks
            if (null !== $item->url) {
                $target = '"_blank"';
                $url = $item->get_url();
            }
        }
        // No Url

        if ('woocommerce' === $this->get_processor()->get_shortcode_option('mcepopup')) {
            $class = 'entry_woocommerce_link';
        }

        if ('shortcode' === $this->get_processor()->get_shortcode_option('mcepopup')) {
            $url = '';
        }

        /* if ($this->get_processor()->is_mobile() && $datatype === 'iframe') {
          $lightbox = '';
          $class = 'entry_action_external_view';
          $target = '_blank';
          $onclick = "sendAnalyticsLB('Preview  (new window)', '{$item->get_name()}');";
          } */

        if (!empty($url)) {
            $url = "href='".$url."'";
        }
        if (!empty($target)) {
            $target = "target='".$target."'";
        }
        if (!empty($onclick)) {
            $onclick = 'onclick="'.$onclick.'"';
        }

        return ['filename' => htmlspecialchars($filename, ENT_COMPAT | ENT_HTML401 | ENT_QUOTES, 'UTF-8'), 'class' => $class, 'url' => $url, 'lightbox' => $lightbox, 'lightbox_inline' => $lightbox_inline, 'target' => $target, 'onclick' => $onclick];
    }

    public function renderDescription(Entry $item)
    {
        $html = '';

        if ($item->is_virtual_folder()) {
            return $html;
        }

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

    public function renderTags(Entry $item)
    {
        $html = '';
        $tags = $item->get_tags();

        if (empty($tags)) {
            return $html;
        }

        $html .= "<div class='entry-info-button entry-description-button entry-tags-button' tabindex='0'><i class='eva eva-pricetags-outline eva-lg'></i>\n";
        $html .= "<div class='tippy-content-holder'>";

        $html .= "<div class='tags-textbox'>";

        foreach ($tags as $tag) {
            $html .= "<span class='entry-tag'>{$tag}</span>";
        }
        $html .= '</div>';

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    public function renderActionMenu(Entry $item)
    {
        $html = '';

        $usercanpreview = $this->get_processor()->get_user()->can_preview() && '1' === $this->get_processor()->get_shortcode_option('allow_preview');

        if (
                $item->is_dir()
                || false === $item->get_can_preview_by_cloud()
                || 'zip' === $item->get_extension()
                || false === $this->get_processor()->get_user()->can_view()
        ) {
            $usercanpreview = false;
        }

        $usercanread = $this->get_processor()->get_user()->can_download() && ($item->is_file() || '1' === $this->get_processor()->get_shortcode_option('can_download_zip'));
        $usercanshare = $this->get_processor()->get_user()->can_share();
        $usercanedit = $this->get_processor()->get_user()->can_edit();
        $usercaneditdescription = $this->get_processor()->get_user()->can_edit_description();

        $usercandeeplink = $this->get_processor()->get_user()->can_deeplink();
        $usercanrename = ($item->is_dir()) ? $this->get_processor()->get_user()->can_rename_folders() : $this->get_processor()->get_user()->can_rename_files();
        $usercanmove = ($item->is_dir()) ? $this->get_processor()->get_user()->can_move_folders() : $this->get_processor()->get_user()->can_move_files();
        $usercancopy = (($item->is_dir()) ? $this->get_processor()->get_user()->can_copy_folders() : $this->get_processor()->get_user()->can_copy_files());
        $usercandelete = ($item->is_dir()) ? $this->get_processor()->get_user()->can_delete_folders() : $this->get_processor()->get_user()->can_delete_files();

        $filename = $item->get_basename();
        $filename .= (('1' === $this->get_processor()->get_shortcode_option('show_ext') && !empty($item->extension)) ? '.'.$item->get_extension() : '');

        // View
        if ($usercanpreview) {
            if (('1' === $this->get_processor()->get_shortcode_option('previewinline'))) {
                $html .= "<li><a class='entry_action_view' title='".esc_html__('Preview', 'wpcloudplugins')."'><i class='eva eva-eye-outline eva-lg'></i>&nbsp;".esc_html__('Preview', 'wpcloudplugins').'</a></li>';
            }
            $url = LETSBOX_ADMIN_URL.'?action=letsbox-preview&inline=0&id='.urlencode($item->get_id()).'&listtoken='.$this->get_processor()->get_listtoken();
            $onclick = "sendAnalyticsLB('Preview (new window)', '".$item->get_basename().((!empty($item->extension)) ? '.'.$item->get_extension() : '')."');";
            $html .= "<li><a href='{$url}' target='_blank' class='entry_action_external_view' onclick=\"{$onclick}\" title='".esc_html__('Preview in new window', 'wpcloudplugins')."'><i class='eva eva-monitor-outline eva-lg'></i>&nbsp;".esc_html__('Preview in new window', 'wpcloudplugins').'</a></li>';
        }

        // Deeplink
        if ($usercandeeplink) {
            $html .= "<li><a class='entry_action_deeplink' title='".esc_html__('Direct link', 'wpcloudplugins')."'><i class='eva eva-link eva-lg'></i>&nbsp;".esc_html__('Direct link', 'wpcloudplugins').'</a></li>';
        }

        // Shortlink
        if ($usercanshare) {
            $html .= "<li><a class='entry_action_shortlink' title='".esc_html__('Share', 'wpcloudplugins')."'><i class='eva eva-share-outline eva-lg'></i>&nbsp;".esc_html__('Share', 'wpcloudplugins').'</a></li>';
        }

        // Download
        if (($usercanread) && ($item->is_file()) && null === $item->url) {
            $html .= "<li><a href='".LETSBOX_ADMIN_URL.'?action=letsbox-download&id='.$item->get_id().'&dl=1&listtoken='.$this->get_processor()->get_listtoken()."' class='entry_action_download' download='".$filename."' data-name='".$filename."' title='".esc_html__('Download', 'wpcloudplugins')."'><i class='eva eva-download eva-lg'></i>&nbsp;".esc_html__('Download', 'wpcloudplugins').'</a></li>';
        }

        if ($usercanread && $item->is_dir() && '1' === $this->get_processor()->get_shortcode_option('can_download_zip')) {
            $html .= "<li><a class='entry_action_download' download='".$item->get_name()."' data-name='".$filename."' title='".esc_html__('Download', 'wpcloudplugins')."'><i class='eva eva-download eva-lg'></i>&nbsp;".esc_html__('Download', 'wpcloudplugins').'</a></li>';
        }

        // Exportformats
        if (($usercanread) && ($item->is_file()) && (count($item->get_save_as()) > 0)) {
            $html .= "<li class='has-menu'><a><i class='eva eva-download eva-lg'></i>&nbsp;".esc_html__('Download as', 'wpcloudplugins').'<i class="eva eva-chevron-right eva-lg"></i></a><ul>';
            foreach ($item->get_save_as() as $name => $exportlinks) {
                $html .= "<li><a href='".LETSBOX_ADMIN_URL.'?action=letsbox-download&id='.$item->get_id().'&dl=1&extension='.$exportlinks['extension'].'&listtoken='.$this->get_processor()->get_listtoken()."' target='_blank' class='entry_action_export' download='".$filename."' data-name='".$filename."'><i class='eva eva-file-outline eva-lg'></i>&nbsp;".' '.$name.'</a>';
            }
            $html .= '</ul>';
        }

        if (
            ($usercanpreview | $usercanread | $usercandeeplink | $usercanshare)
        && ($usercaneditdescription || $usercanedit || $usercanrename || $usercanmove || $usercancopy)) {
            $html .= "<li class='list-separator'></li>";
        }

        // Descriptions
        if ($usercaneditdescription) {
            if (empty($item->description)) {
                $html .= "<li><a class='entry_action_description' title='".esc_html__('Add description', 'wpcloudplugins')."'><i class='eva eva-message-square-outline eva-lg'></i>&nbsp;".esc_html__('Add description', 'wpcloudplugins').'</a></li>';
            } else {
                $html .= "<li><a class='entry_action_description' title='".esc_html__('Edit description', 'wpcloudplugins')."'><i class='eva eva-message-square-outline eva-lg'></i>&nbsp;".esc_html__('Edit description', 'wpcloudplugins').'</a></li>';
            }
        }

        // Edit
        if (($usercanedit) && ($item->is_file()) && $item->get_can_edit_by_cloud()) {
            $html .= "<li><a href='".LETSBOX_ADMIN_URL.'?action=letsbox-edit&id='.$item->get_id().'&listtoken='.$this->get_processor()->get_listtoken()."' target='_blank' class='entry_action_edit' data-name='".$filename."' title='".esc_html__('Edit (new window)', 'wpcloudplugins')."'><i class='eva eva-edit-outline eva-lg'></i>&nbsp;".esc_html__('Edit (new window)', 'wpcloudplugins').'</a></li>';
        }

        // Rename
        if ($usercanrename) {
            $html .= "<li><a class='entry_action_rename' title='".esc_html__('Rename', 'wpcloudplugins')."'><i class='eva eva-edit-2-outline eva-lg'></i>&nbsp;".esc_html__('Rename', 'wpcloudplugins').'</a></li>';
        }

        // Move
        if ($usercanmove) {
            $html .= "<li><a class='entry_action_move' title='".esc_html__('Move to', 'wpcloudplugins')."'><i class='eva eva-corner-down-right eva-lg'></i>&nbsp;".esc_html__('Move to', 'wpcloudplugins').'</a></li>';
        }

        // Copy
        if ($usercancopy) {
            $html .= "<li><a class='entry_action_copy' title='".esc_html__('Make a copy', 'wpcloudplugins')."'><i class='eva eva-copy-outline eva-lg'></i>&nbsp;".esc_html__('Make a copy', 'wpcloudplugins').'</a></li>';
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
        $return = '';

        if (
            false === $this->get_processor()->get_user()->can_add_folders()
            || true === $this->_search
            || '1' === $this->get_processor()->get_shortcode_option('show_breadcrumb')
            ) {
            return $return;
        }

        $icon_set = $this->get_processor()->get_setting('icon_set');

        $return .= "<div class='entry folder newfolder'>\n";
        $return .= "<div class='entry_block'>\n";
        $return .= "<div class='entry_thumbnail'><div class='entry_thumbnail-view-bottom'><div class='entry_thumbnail-view-center'>\n";
        $return .= "<a class='entry_link'><img class='preloading' src='".LETSBOX_ROOTPATH."/css/images/transparant.png' data-src='".$icon_set.'128x128/folder-new.png'."' data-src-retina='".$icon_set.'256x256/folder-new.png'."'/></a>";
        $return .= "</div></div></div>\n";

        $return .= "<div class='entry-info'>";
        $return .= "<div class='entry-info-name'>";
        $return .= "<a class='entry_link' title='".esc_html__('Add folder', 'wpcloudplugins')."'><div class='entry-name-view'>";
        $return .= '<span>'.esc_html__('Add folder', 'wpcloudplugins').'</span>';
        $return .= '</div></a>';
        $return .= "</div>\n";

        $return .= "</div>\n";
        $return .= "</div>\n";
        $return .= "</div>\n";

        return $return;
    }

    public function createFilesArray()
    {
        $filesarray = [];

        $this->setParentFolder();

        //Add folders and files to filelist
        if (count($this->_folder['contents']) > 0) {
            foreach ($this->_folder['contents'] as $node) {
                // Check if entry is allowed
                if (!$this->get_processor()->_is_entry_authorized($node)) {
                    continue;
                }
                $filesarray[] = $node->get_entry();
            }

            if (false === $this->_search || false === apply_filters('letsbox_use_search_order', true)) {
                $filesarray = $this->get_processor()->sort_filelist($filesarray);
            }
        }

        // Add 'back to Previous folder' if needed
        if (isset($this->_folder['folder'])) {
            $folder = $this->_folder['folder']->get_entry();
            $add_parent_folder_item = true;

            if ($this->_search || $folder->get_id() === $this->get_processor()->get_root_folder()) {
                $add_parent_folder_item = false;
            }

            if ($add_parent_folder_item) {
                foreach ($this->_parentfolders as $parentfolder) {
                    array_unshift($filesarray, $parentfolder);
                }
            }
        }

        return $filesarray;
    }
}
