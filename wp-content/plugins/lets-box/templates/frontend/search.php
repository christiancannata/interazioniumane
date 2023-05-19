<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2022, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\LetsBox;

?>
<div class="list-container" style="width:<?php echo $this->options['maxwidth']; ?>;max-width:<?php echo $this->options['maxwidth']; ?>;">
    <div class="nav-header LetsBox" id="search-<?php echo $this->listtoken; ?>">
        <div class="search-div">
            <a class="" href="#"><i class="eva eva-search submit-search"></i></a>
            <input name="q" type="text" size="40" aria-label="<?php esc_html_e('Search', 'wpcloudplugins'); ?>" placeholder="<?php esc_html_e('Search for files', 'wpcloudplugins').(('1' === $this->options['searchcontents'] && '1' === $this->options['show_files']) ? ' '.esc_html__('and content', 'wpcloudplugins') : ''); ?>" class="search-input" />
        </div>
    </div>
    <div class="file-container">
        <div class="loading initialize"><?php
      $loaders = Core::get_setting('loaders');

switch ($loaders['style']) {
    case 'custom':
        break;

    case 'beat':
        ?>
            <div class='loader-beat'></div>
            <?php
        break;

    case 'spinner':
        ?>
            <svg class="loader-spinner" viewBox="25 25 50 50">
                <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10"></circle>
            </svg>
            <?php
        break;
}
?>
        </div>
        <div class="ajax-filelist" style="<?php echo (!empty($this->options['maxheight'])) ? 'max-height:'.$this->options['maxheight'].';overflow-y: scroll;overflow-x: hidden;' : ''; ?>">&nbsp;</div>
        <div class="scroll-to-top">
            <button class="scroll-to-top-action button button-round-icon secondary button-round-icon-lg button-shadow-3" type="button" title="<?php esc_html_e('Scroll to top', 'wpcloudplugins'); ?>" aria-expanded="false"><i class="eva eva-arrow-upward-outline eva-2x"></i></button>            
        </div>         
    </div>
</div>