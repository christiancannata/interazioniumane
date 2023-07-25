<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\LetsBox;

class Entry extends EntryAbstract
{
    public $tags;
    public $representations;
    public $url;

    public function convert_api_entry($api_entry)
    {
        // @var $api_entry \Box\Model\File\File

        if (!$api_entry instanceof \Box\Model\File\File && !$api_entry instanceof \Box\Model\Folder\Folder) {
            error_log('[WP Cloud Plugin message]: '.sprintf('Box response is not a valid Entry.'));

            return false;
        }

        // Normal Meta Data
        $this->set_id($api_entry->getId());
        $this->set_name($api_entry->getName());

        if ($api_entry instanceof \Box\Model\Folder\Folder) {
            $this->set_is_dir(true);
        }

        $pathinfo = Helpers::get_pathinfo($api_entry->getName());
        if ($this->is_file() && isset($pathinfo['extension'])) {
            $this->set_extension(strtolower($pathinfo['extension']));
        }
        $this->set_mimetype_from_extension();

        if ($this->is_file()) {
            $this->set_basename(str_ireplace('.'.$this->get_extension(), '', $this->get_name()));
        } else {
            $this->set_basename($this->get_name());
        }

        $parent = $api_entry->getParent();
        if (!empty($parent)) {
            $this->set_parents([$parent['id']]);
        } elseif (0 != $this->get_id()) {
            // Work around to show shared folder, externally owned in the Root Folder
            $this->set_parents([0]);
        }

        $path_collection = $api_entry->getPathCollection();
        $full_path = '';
        if (!empty($path_collection) && isset($path_collection['entries'])) {
            foreach ($path_collection['entries'] as $path_item) {
                $full_path .= $path_item['name'].'/';
            }
        }
        $full_path .= $this->get_name();
        $this->set_path($full_path);

        $this->set_trashed(null !== $api_entry->getTrashedAt());

        $this->set_size($api_entry->getSize());
        $this->set_description($api_entry->getDescription());
        $this->set_tags($api_entry->getTags());

        $last_modified = $api_entry->getModifiedAt();
        $last_modified = (empty($last_modified)) ? $api_entry->getCreatedAt() : $last_modified;

        if (!empty($last_modified) && is_string($last_modified)) {
            $dtime = \DateTime::createFromFormat(DATE_RFC3339, $last_modified, new \DateTimeZone('UTC'));

            if ($dtime) {
                $this->set_last_edited($dtime->getTimestamp());
            }
        }

        // Set the permissions
        $api_permissions = $api_entry->getPermissions();
        $permissions = [
            'canpreview' => isset($api_permissions['can_preview']) ? $api_permissions['can_preview'] : false,
            'candownload' => isset($api_permissions['can_download']) ? $api_permissions['can_download'] : true,
            'candelete' => isset($api_permissions['can_delete']) ? $api_permissions['can_delete'] : true,
            'canmove' => isset($api_permissions['can_delete']) ? $api_permissions['can_delete'] : true, // No move permission yet
            'canadd' => isset($api_permissions['can_upload']) ? $api_permissions['can_upload'] : true,
            'canrename' => isset($api_permissions['can_rename']) ? $api_permissions['can_rename'] : true,
            'canshare' => isset($api_permissions['can_share']) ? $api_permissions['can_share'] : true,
        ];
        $this->set_permissions($permissions);

        if ($permissions['canpreview']) {
            $this->set_can_preview_by_cloud(true);
        }

        // Can File be edited via Box
        $editsupport = [];
        $editwithbox = in_array($this->get_extension(), $editsupport);
        if ($editwithbox) {
            $this->set_can_edit_by_cloud(true);
        }

        // Direct Download URL, not always available only for paid accounts. Valid for just 15 minutes!
        // $shared_link = $api_entry->getSharedLink();
        // if ($shared_link['download_url']) {
        //    $this->set_direct_download_link($shared_link['download_url']);
        // }
        $this->set_save_as($this->create_save_as());

        // Icon
        $default_icon = $this->get_default_icon();
        $this->set_icon($default_icon);

        // If entry has media data available set it here
        $mediadata = [];
        $this->set_media($mediadata);

        // Thumbnail
        $this->set_representations($api_entry->getRepresentations());
        $this->set_thumbnails();

        // Url if bookmark/weblink
        if ($this->is_file()) {
            $this->set_url($api_entry->getUrl());
        }

        // Add some data specific for Box Service
        $additional_data = [
        ];

        $this->set_additional_data($additional_data);
    }

    public function set_thumbnails()
    {
        $thumbnail_icon = $this->get_default_icon();
        $thumbnail_icon_large = $this->get_icon_large();

        $this->set_thumbnail_icon($thumbnail_icon);
        $this->set_thumbnail_small($thumbnail_icon);
        $this->set_thumbnail_small_cropped($thumbnail_icon);
        $this->set_thumbnail_medium($thumbnail_icon_large);
        $this->set_thumbnail_large($thumbnail_icon_large);

        // https://developer.box.com/guides/representations/thumbnail/
        if (empty($this->representations) || empty($this->representations['entries'])) {
            return $this;
        }

        $this->set_has_own_thumbnail(true);
    }

    public function set_mimetype_from_extension()
    {
        if ($this->is_dir()) {
            return null;
        }

        if (empty($this->extension)) {
            return null;
        }

        $mimetype = Helpers::get_mimetype($this->get_extension());
        $this->set_mimetype($mimetype);
    }

    public function get_default_icon()
    {
        return Helpers::get_default_icon($this->get_mimetype(), $this->is_dir());
    }

    public function get_icon_large()
    {
        return str_replace('32x32', '256x256', $this->get_icon());
    }

    public function create_save_as()
    {
        return [];
    }

    public function get_date_taken()
    {
        return $this->get_media('datetaken');
    }

    public function set_tags($tags = [])
    {
        return $this->tags = $tags;
    }

    public function get_tags()
    {
        return $this->tags;
    }

    public function set_url($url)
    {
        return $this->url = $url;
    }

    public function get_url()
    {
        return $this->url;
    }

    public function set_representations($representations)
    {
        return $this->representations = $representations;
    }

    public function get_representations()
    {
        return $this->representations;
    }
}
