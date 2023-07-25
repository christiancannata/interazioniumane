<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\LetsBox;

class Carousel
{
    private $_folder;

    public function get_images_list()
    {
        $this->_folder = Client::instance()->get_folder();

        if (false === $this->_folder) {
            return json_encode([
                'images' => [],
                'total' => 0,
            ]);
        }

        $images = $this->get_images();

        $data = [
            'images' => $images,
            'total' => count($images),
        ];

        if ($data['total'] > 0) {
            $response = json_encode($data);

            $cached_request = new \TheLion\LetsBox\CacheRequest();
            $cached_request->add_cached_response($response);
            echo $response;
        }

        exit;
    }

    public function get_images()
    {
        $entries = Client::instance()->get_folder_recursive_filtered($this->_folder['folder']);

        $images = [];

        foreach ($entries as $entry_id => $cached_entry) {
            $entry = $cached_entry->get_entry();
            if ($entry->is_dir()) {
                continue;
            }

            // Check if entry has thumbnail
            if (!$entry->has_own_thumbnail()) {
                continue;
            }

            $images[] = $entry;
        }

        $images = Processor::instance()->sort_filelist($images);
        $data = [];

        if ('-1' !== Processor::instance()->get_shortcode_option('max_files')) {
            $images = array_slice($images, 0, Processor::instance()->get_shortcode_option('max_files'));
        }

        foreach ($images as $entry) {
            $download_url = User::can_download() ? LETSBOX_ADMIN_URL.'?action=letsbox-download&id='.urlencode($entry->get_id()).'&account_id='.$this->_folder['folder']->get_account_id().'&dl=1&listtoken='.Processor::instance()->get_listtoken() : null;
            $lightbox_url = User::can_preview() ? (('boxthumbnail' === Processor::instance()->get_setting('loadimages') || false === User::can_download()) ? Client::instance()->get_thumbnail($entry, true, 1024, 768, false, true) : $download_url) : null;

            $data[] = [
                'id' => $entry->get_id(),
                'name' => htmlspecialchars($entry->get_basename(), ENT_COMPAT | ENT_HTML401 | ENT_QUOTES, 'UTF-8'),
                'width' => $entry->get_media('width'),
                'height' => $entry->get_media('height'),
                'last_edited_time' => $entry->get_last_edited(),
                'last_edited_time_str' => $entry->get_last_edited_str(),
                'url' => Client::instance()->get_thumbnail($entry, true, 1024, 768, false, true),
                'description' => htmlentities(nl2br($entry->get_description(), ENT_QUOTES | ENT_HTML401)),
                'preloaded' => false,
                'download_url' => $download_url,
                'lightbox_link' => $lightbox_url,
            ];
        }

        return $data;
    }
}
