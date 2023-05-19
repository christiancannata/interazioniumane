<?php
/**
 *
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2022, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\LetsBox;

class Thumbnail
{
    /**
     * @var Entry
     */
    private $_entry;

    /**
     * @var int
     */
    private $_width;

    /**
     * @var int
     */
    private $_height;

    /**
     * @var bool
     */
    private $_crop = true;

    /**
     * @var bool
     */
    private $_raw = true;

    /**
     * @var int
     */
    private $_quality = '75';

    /**
     * @var string
     */
    private $_format = 'jpeg';

    /**
     * @var string
     */
    private $_thumbnail_name;

    /**
     * @var string
     */
    private $_location_thumbnails;

    /**
     * @var string
     */
    private $_location_thumbnails_url;

    /**
     * @var string
     */
    private $_image_data;

    /**
     * Set in case the gallery is asking for a thumbnail, but doesn't have dimensions yet.
     *
     * @var bool
     */
    private $_loading_thumb = false;

    public function __construct(Entry $entry, $width, $height, $crop = true, $raw = true, $quality = 75, $format = 'jpeg', $imagedata = null, $loading_thumb = false)
    {
        $this->_entry = $entry;
        $this->_width = round($width);
        $this->_height = round($height);
        $this->_crop = $crop;
        $this->_raw = $raw;
        $this->_quality = $quality;
        $this->_format = $format;
        $this->_location_thumbnails = LETSBOX_CACHEDIR.'thumbnails/';
        $this->_location_thumbnails_url = LETSBOX_CACHEURL.'thumbnails/';
        $this->_image_data = $imagedata;

        if ($entry->get_size() < 5242880 && !in_array($this->_get_entry()->get_extension(), ['jpg', 'jpeg', 'png'])) {
            $this->_raw = false;
        }

        if (null == $this->_get_entry()->get_media('width') || null == $this->_get_entry()->get_media('height')) {
            $got_dimensions = $this->_get_dimensions_from_previous_file();
        }

        $this->set_thumbnail_name();
    }

    public function set_thumbnail_name()
    {
        $this->_thumbnail_name = $this->_get_entry()->get_id().'_'.$this->_width.'_'.$this->_height.'_c'.(($this->_crop) ? '1' : '0').'_s'.(($this->_raw) ? '1' : '0').'_q'.$this->_quality.'.'.$this->_format;
    }

    public function get_url()
    {
        if (!$this->does_thumbnail_exist()) {
            return $this->_build_thumbnail_url();
        }

        return $this->_get_location_thumbnail_url();
    }

    public function get_thumbnail_name()
    {
        return str_replace(':', '', $this->_thumbnail_name);
    }

    public function does_thumbnail_exist()
    {
        if (!file_exists($this->_get_location_thumbnail())) {
            return false;
        }

        if (filemtime($this->_get_location_thumbnail()) !== $this->_get_entry()->get_last_edited()) {
            return false;
        }

        if (filesize($this->_get_location_thumbnail()) < 1) {
            return false;
        }

        return $this->_get_location_thumbnail();
    }

    public function build_thumbnail()
    {
        @set_time_limit(60); // Creating thumbnail can take a while

        $representations = $this->_get_entry()->get_representations();
        $first_thumbnail = reset($representations['entries']);
        $first_thumbnail_location = str_replace('{+asset_path}', '', $first_thumbnail['content']['url_template']);

        // First get the Image itself
        if (empty($this->_image_data)) {
            try {
                $data = 0;

                while (is_int($data)) {
                    if (false === $this->get_raw()) {
                        $data = App::instance()->get_sdk_client()->downloadRepresentation($first_thumbnail_location);
                    }

                    // Try to load the image file itself
                    if (empty($data) && in_array($this->_get_entry()->get_extension(), ['jpg', 'jpeg', 'png', 'webp'])) {
                        $data = App::instance()->get_sdk_client()->downloadFile($this->_get_entry()->get_id(), true);
                    }

                    // When the API still isn't able to provide us with a thumbnail, please fallback to icons
                    if (empty($data)) {
                        return false;
                    }

                    if (is_int($data)) {
                        // Thumbnail still needs to be created
                        usleep($data * 1 * 10 ^ 6);  // Convert seconds to useconds
                    }
                }

                if (isset($data['headers']['location'])) {
                    $connection = App::instance()->get_sdk_client()->getConnection();
                    $data = $connection->query($data['headers']['location']);
                }

                $thumbnail = $data['body'];

                $this->_image_data = $thumbnail;
                unset($thumbnail);
            } catch (\Exception $ex) {
                error_log('[WP Cloud Plugin message]: '.sprintf('Cannot generate thumbnail: %s', $ex->getMessage()));

                exit(esc_html__('Cannot get image'));
            }
        }

        $result = $this->_create_thumbnail();
        // Also create an uncropped version for image dimensions
        if ($this->get_raw() && $this->get_crop()) {
            $this->set_crop(false);
            $this->set_thumbnail_name();
            $this->_create_thumbnail();

            $this->set_crop(true);
            $this->set_thumbnail_name();
        }

        return $result;
    }

    public function get_width()
    {
        return $this->_width;
    }

    public function get_height()
    {
        return $this->_height;
    }

    public function get_crop()
    {
        return $this->_crop;
    }

    public function get_raw()
    {
        return $this->_raw;
    }

    public function get_quality()
    {
        return $this->_quality;
    }

    public function get_format()
    {
        return $this->_format;
    }

    public function set_width($_width)
    {
        $this->_width = round((int) $_width);
    }

    public function set_height($_height)
    {
        $this->_height = round((int) $_height);
    }

    public function set_crop($_crop = false)
    {
        $this->_crop = (bool) $_crop;
    }

    public function set_raw($_raw = false)
    {
        $this->_raw = (bool) $_raw;
    }

    public function set_quality($_quality)
    {
        $this->_quality = (int) $_quality;
    }

    public function set_format($_format)
    {
        $this->_format = $_format;
    }

    private function _build_thumbnail_url()
    {
        return LETSBOX_ADMIN_URL."?action=letsbox-thumbnail&src={$this->_thumbnail_name}&listtoken=".Processor::instance()->get_listtoken().'&account_id='.App::get_current_account()->get_id();
    }

    private function _create_thumbnail()
    {
        // Create the requested thumbnail
        try {
            $php_thumb = $this->_load_phpthumb_object();
            $php_thumb->GenerateThumbnail();
            $php_thumb->CalculateThumbnailDimensions();

            // Update width/height in cache as this data isn't provided by API
            if (false === $this->get_crop()) {
                $media = [
                    'width' => $php_thumb->source_width,
                    'height' => $php_thumb->source_height,
                ];

                $cached_entry = Cache::instance()->get_node_by_id($this->_get_entry()->get_id());
                $cached_entry->get_entry()->set_media($media);
                Cache::instance()->set_updated();
            }

            $php_thumb->SetCacheFilename();
            $is_thumbnail_created = $php_thumb->RenderToFile($this->_get_location_thumbnail());
            unset($php_thumb);

            /* Set the modification date of the thumbnail to that of the entry
             * so we can check if a new thumbnail should be loaded */
            touch($this->_get_location_thumbnail(), $this->_get_entry()->get_last_edited());

            return $is_thumbnail_created;
        } catch (\Exception $ex) {
            error_log('[WP Cloud Plugin message]: '.sprintf('Cannot generate thumbnail: %s', $ex->getMessage()));

            exit(esc_html__('Cannot generate thumbnail image'));
        }
    }

    private function _get_dimensions_from_previous_file()
    {
        $file_patern = $this->_get_location_thumbnails().$this->_get_entry()->get_id().'_*_*_c0_s1*';
        $iterator = new \GlobIterator($file_patern, \FilesystemIterator::KEY_AS_PATHNAME);

        $got_dimension = false;
        foreach ($iterator as $fileinfo) {
            $size = getimagesize($fileinfo);

            $media = [
                'width' => $size[0],
                'height' => $size[1],
            ];

            $cached_entry = Cache::instance()->get_node_by_id($this->_get_entry()->get_id());
            $cached_entry->get_entry()->set_media($media);
            Cache::instance()->set_updated();

            $got_dimension = true;
        }

        return $got_dimension;
    }

    /**
     * @return phpThumb
     */
    private function _load_phpthumb_object()
    {
        if (!class_exists('\TheLion\LetsBox\phpthumb')) {
            try {
                require_once LETSBOX_ROOTDIR.'/vendors/phpThumb/phpthumb.class.php';
            } catch (\Exception $ex) {
                // TO DO LOG
                error_log('[WP Cloud Plugin message]: '.sprintf('Cannot load PHPTHUMB library: %s', $ex->getMessage()));

                exit("Can't load PHPTHUMB Library");
            }
        }

        $this->_create_thumbnail_dir();

        $php_thumb = new \TheLion\LetsBox\phpthumb();
        $php_thumb->resetObject();
        $php_thumb->setParameter('config_temp_directory', $this->_get_location_thumbnails());
        $php_thumb->setParameter('config_cache_directory', $this->_get_location_thumbnails());
        $php_thumb->setParameter('config_output_format', $this->get_format());
        $php_thumb->setParameter('q', $this->get_quality());
        $php_thumb->setParameter('zc', $this->get_crop());

        if (0 != $this->get_width()) {
        }
        if (0 != $this->get_height()) {
            $php_thumb->setParameter('h', $this->get_height());
        }

        if ($this->get_crop()) {
            $php_thumb->setParameter('w', max([$this->_width, $this->_height]));
            $php_thumb->setParameter('h', max([$this->_width, $this->_height]));
        }

        $php_thumb->setParameter('f', $this->get_format());
        $php_thumb->setParameter('bg', 'FFFFFF');
        $php_thumb->setParameter('ar', 'x');
        $php_thumb->setParameter('aoe', false);

        $max_file_size = ($this->get_width() * $this->get_height()) / 5;
        // $php_thumb->setParameter('maxb', $max_file_size);

        $php_thumb->setSourceData($this->_get_image_data());

        return $php_thumb;
    }

    private function _create_thumbnail_dir()
    {
        if (!file_exists($this->_get_location_thumbnails())) {
            @mkdir($this->_get_location_thumbnails(), 0755);
        } else {
            return true;
        }

        if (!is_writable($this->_get_location_thumbnails())) {
            @chmod($this->_get_location_thumbnails(), 0755);
        } else {
            return true;
        }

        return is_writable($this->_get_location_thumbnails());
    }

    /**
     * @return Cache\Node
     */
    private function _get_entry()
    {
        return $this->_entry;
    }

    private function _get_location_thumbnail()
    {
        $thumbnail_name = str_replace(['_jpeg', '_png'], ['.jpeg', '.png'], $this->get_thumbnail_name());

        return $this->_location_thumbnails.$thumbnail_name;
    }

    private function _get_location_thumbnail_url()
    {
        $thumbnail_name = str_replace(['_jpeg', '_png'], ['.jpeg', '.png'], $this->get_thumbnail_name());

        return $this->_location_thumbnails_url.$thumbnail_name;
    }

    private function _get_location_thumbnails()
    {
        return $this->_location_thumbnails;
    }

    private function _get_location_thumbnails_url()
    {
        return $this->_location_thumbnails_url;
    }

    private function _get_image_data()
    {
        return $this->_image_data;
    }

    private function _is_loading_thumb()
    {
        return $this->_loading_thumb;
    }
}