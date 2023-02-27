<?php
$network_wide_authorization = $this->get_processor()->is_network_authorized();

function wp_roles_and_users_input($name, $selected = [])
{
    if (!is_array($selected)) {
        $selected = ['administrator'];
    }

    // Workaround: Add temporarily selected value to prevent an empty selection in Tagify when only user ID 0 is selected
    $selected[] = '_______PREVENT_EMPTY_______';

    // Create value for imput field
    $value = implode(', ', $selected);

    // Input Field
    echo "<input class='letsbox-option-input-large letsbox-tagify letsbox-permissions-placeholders' type='text' name='{$name}' value='{$value}' placeholder='' />";
}

function create_color_boxes_table($colors, $settings)
{
    if (0 === count($colors)) {
        return '';
    }

    $table_html = '<table class="color-table">';

    foreach ($colors as $color_id => $color) {
        $value = isset($settings['colors'][$color_id]) ? sanitize_text_field($settings['colors'][$color_id]) : $color['default'];

        $table_html .= '<tr>';
        $table_html .= "<td>{$color['label']}</td>";
        $table_html .= "<td><input value='{$value}' data-default-color='{$color['default']}'  name='lets_box_settings[colors][{$color_id}]' id='colors-{$color_id}' type='text'  class='wpcp-color-picker' data-alpha-enabled='true' ></td>";
        $table_html .= '</tr>';
    }

    $table_html .= '</table>';

    return $table_html;
}

function create_upload_button_for_custom_images($option)
{
    $field_value = empty($option['value']) ? $option['default'] : $option['value'];

    $button_html = '<div class="upload_row">';

    $button_html .= '<div class="screenshot" id="'.$option['id'].'_image">'."\n";

    $button_html .= '<img src="'.$field_value.'" alt="" />'."\n";
    $button_html .= '<a href="javascript:void(0)" class="wpcp-image-remove-button">'.esc_html__('Remove', 'wpcloudplugins').'</a>'."\n";
    $button_html .= '<a href="javascript:void(0)" class="upload-default">'.esc_html__('Default', 'wpcloudplugins').'</a>'."\n";

    $button_html .= '</div>';

    $button_html .= '<input id="'.esc_attr($option['id']).'" class="upload letsbox-option-input-large" type="text" name="'.esc_attr($option['name']).'" value="'.esc_attr($field_value).'" autocomplete="off" />';
    $button_html .= '<input class="wpcp-image-select-button simple-button blue" type="button" value="'.esc_html__('Select Image', 'wpcloudplugins').'" title="'.esc_html__('Upload or select a file from the media library', 'wpcloudplugins').'" />';

    if ($field_value !== $option['default']) {
        $button_html .= '<input id="wpcp-default-image-button" class="wpcp-default-image-button simple-button" type="button" value="'.esc_html__('Default', 'wpcloudplugins').'" title="'.esc_html__('Fallback to the default value', 'wpcloudplugins').'"  data-default="'.$option['default'].'"/>';
    }

    $button_html .= '</div>'."\n";

    return $button_html;
}

function help_button($name, $text)
{
    ?>
  <a onclick="return false;" onkeypress="return false;" class="wpcp_help_tooltip" data-tippy-content="<strong><?php echo $name; ?></strong></br><?php echo $text; ?>">
			<i class="eva eva-question-mark-circle-outline"></i>
		</a>
  <?php
}
?>

<div class="letsbox admin-settings">
  <form id="letsbox-options" method="post" action="options.php">
    <?php settings_fields('lets_box_settings'); ?>
    <input type="hidden" name="lets_box_settings[box_account_type]" id="box_account_type" value="<?php echo @esc_attr($this->settings['box_account_type']); ?>" >

    <div class="wrap">
      <div class="letsbox-header">
        <div class="letsbox-logo"><a href="https://www.wpcloudplugins.com" target="_blank"><img src="<?php echo LETSBOX_ROOTPATH; ?>/css/images/wpcp-logo-dark.svg" height="64" width="64"/></a></div>
        <div class="letsbox-form-buttons"> <div id="wpcp-save-settings-button" class="simple-button default"><?php esc_html_e('Save Settings', 'wpcloudplugins'); ?>&nbsp;<div class='wpcp-spinner'></div></div></div>
        <div class="letsbox-title"><?php esc_html_e('Settings', 'wpcloudplugins'); ?></div>
      </div>

      <div id="" class="letsbox-panel letsbox-panel-left">      
        <div class="letsbox-nav-header"><?php esc_html_e('Settings', 'wpcloudplugins'); ?>  <a href="<?php echo admin_url('update-core.php'); ?>">(Ver: <?php echo LETSBOX_VERSION; ?>)</a></div>

        <ul class="letsbox-nav-tabs">
          <li id="settings_general_tab" data-tab="settings_general" class="current"><a ><?php esc_html_e('General', 'wpcloudplugins'); ?></a></li>
          <?php
          if ($this->is_activated()) {
              ?>
              <li id="settings_layout_tab" data-tab="settings_layout" ><a ><?php esc_html_e('Layout', 'wpcloudplugins'); ?></a></li>
              <li id="settings_userfolders_tab" data-tab="settings_userfolders" ><a ><?php esc_html_e('Private Folders', 'wpcloudplugins'); ?></a></li>
              <li id="settings_advanced_tab" data-tab="settings_advanced" ><a ><?php esc_html_e('Advanced', 'wpcloudplugins'); ?></a></li>
              <li id="settings_integrations_tab" data-tab="settings_integrations" ><a><?php esc_html_e('Integrations', 'wpcloudplugins'); ?></a></li>
              <li id="settings_notifications_tab" data-tab="settings_notifications" ><a ><?php esc_html_e('Notifications', 'wpcloudplugins'); ?></a></li>
              <li id="settings_permissions_tab" data-tab="settings_permissions" ><a><?php esc_html_e('Permissions', 'wpcloudplugins'); ?></a></li>
              <li id="settings_stats_tab" data-tab="settings_stats" ><a><?php esc_html_e('Statistics', 'wpcloudplugins'); ?></a></li>
              <li id="settings_tools_tab" data-tab="settings_tools" ><a><?php esc_html_e('Tools', 'wpcloudplugins'); ?></a></li>
              <?php
          }
          ?>

          <li id="settings_system_tab" data-tab="settings_system" ><a><?php esc_html_e('System information', 'wpcloudplugins'); ?></a></li>
          <li id="settings_help_tab" data-tab="settings_help" ><a><?php esc_html_e('Support', 'wpcloudplugins'); ?></a></li>
        </ul>

        <div class="letsbox-nav-header" style="margin-top: 50px;"><?php esc_html_e('Other Cloud Plugins', 'wpcloudplugins'); ?></div>
        <ul class="letsbox-nav-tabs">
          <li id="settings_help_tab" data-tab="settings_help"><a href="https://1.envato.market/L6yXj" target="_blank" style="color:#522058;">Google Drive <i class="eva eva-external-link" aria-hidden="true"></i></a></li>
          <li id="settings_help_tab" data-tab="settings_help"><a href="https://1.envato.market/vLjyO" target="_blank" style="color:#522058;">Dropbox <i class="eva eva-external-link" aria-hidden="true"></i></a></li>
          <li id="settings_help_tab" data-tab="settings_help"><a href="https://1.envato.market/yDbyv" target="_blank" style="color:#522058;">OneDrive <i class="eva eva-external-link" aria-hidden="true"></i></a></li>
        </ul> 

        <div class="letsbox-nav-footer">          <a href="https://www.wpcloudplugins.com/" target="_blank">
            <img alt="" height="auto" src="<?php echo LETSBOX_ROOTPATH; ?>/css/images/wpcloudplugins-logo-dark.png">
          </a></div>
      </div>

      <div class="letsbox-panel letsbox-panel-right">
        <!-- General Tab -->
        <div id="settings_general" class="letsbox-tab-panel current">

          <div class="letsbox-tab-panel-header"><?php esc_html_e('General', 'wpcloudplugins'); ?></div>

          <?php if ($this->is_activated()) { ?>
              <div class="letsbox-option-title"><?php esc_html_e('Authorization', 'wpcloudplugins'); ?></div>
              <?php
              echo $this->get_plugin_authorization_box();
          }
          ?>
          <div class="letsbox-option-title"><?php esc_html_e('Plugin License', 'wpcloudplugins'); ?></div>
          <?php
          echo $this->get_plugin_activated_box();
          ?>

        </div>
        <!-- End General Tab -->

        <!-- Layout Tab -->
        <div id="settings_layout"  class="letsbox-tab-panel">
          <div class="letsbox-tab-panel-header"><?php esc_html_e('Layout', 'wpcloudplugins'); ?></div>

          <div class="letsbox-accordion">
            <div class="letsbox-accordion-title letsbox-option-title"><?php esc_html_e('Loading Spinner & Images', 'wpcloudplugins'); ?>         </div>
            <div>

              <div class="letsbox-option-title"><?php esc_html_e('Select Loader Spinner', 'wpcloudplugins'); ?></div>
              <select type="text" name="lets_box_settings[loaders][style]" id="loader_style">
                <option value="beat" <?php echo 'beat' === $this->settings['loaders']['style'] ? "selected='selected'" : ''; ?>><?php esc_html_e('Beat', 'wpcloudplugins'); ?></option>
                <option value="spinner" <?php echo 'spinner' === $this->settings['loaders']['style'] ? "selected='selected'" : ''; ?>><?php esc_html_e('Spinner', 'wpcloudplugins'); ?></option>
                <option value="custom" <?php echo 'custom' === $this->settings['loaders']['style'] ? "selected='selected'" : ''; ?>><?php esc_html_e('Custom Image (selected below)', 'wpcloudplugins'); ?></option>
              </select>

              <div class="letsbox-option-title"><?php esc_html_e('General Loader', 'wpcloudplugins'); ?></div>
              <?php
              $button = ['value' => $this->settings['loaders']['loading'], 'id' => 'loaders_loading', 'name' => 'lets_box_settings[loaders][loading]', 'default' => LETSBOX_ROOTPATH.'/css/images/wpcp-loader.svg'];
              echo create_upload_button_for_custom_images($button);
              ?>
              <div class="letsbox-option-title"><?php esc_html_e('No Results', 'wpcloudplugins'); ?></div>
              <?php
              $button = ['value' => $this->settings['loaders']['no_results'], 'id' => 'loaders_no_results', 'name' => 'lets_box_settings[loaders][no_results]', 'default' => LETSBOX_ROOTPATH.'/css/images/loader_no_results.svg'];
              echo create_upload_button_for_custom_images($button);
              ?>
              <div class="letsbox-option-title"><?php esc_html_e('Access Forbidden', 'wpcloudplugins'); ?></div>
              <?php
              $button = ['value' => $this->settings['loaders']['protected'], 'id' => 'loaders_protected', 'name' => 'lets_box_settings[loaders][protected]', 'default' => LETSBOX_ROOTPATH.'/css/images/loader_protected.svg'];
              echo create_upload_button_for_custom_images($button);
              ?>
              <div class="letsbox-option-title"><?php esc_html_e('iFrame Loader', 'wpcloudplugins'); ?></div>
              <?php
              $button = ['value' => $this->settings['loaders']['iframe'], 'id' => 'loaders_iframe', 'name' => 'lets_box_settings[loaders][iframe]', 'default' => LETSBOX_ROOTPATH.'/css/images/wpcp-loader.svg'];
              echo create_upload_button_for_custom_images($button);
              ?>     
            </div>

            <div class="letsbox-accordion-title letsbox-option-title"><?php esc_html_e('Color Palette', 'wpcloudplugins'); ?></div>
            <div>

              <div class="letsbox-option-title"><?php esc_html_e('Theme Style', 'wpcloudplugins'); ?></div>
              <div class="letsbox-option-description"><?php esc_html_e('Select the general style of your theme', 'wpcloudplugins'); ?>.</div>
              <select name="skin_selectbox" id="wpcp_content_skin_selectbox" class="ddslickbox">
                <option value="dark" <?php echo 'dark' === $this->settings['colors']['style'] ? "selected='selected'" : ''; ?> data-imagesrc="<?php echo LETSBOX_ROOTPATH; ?>/css/images/skin-dark.png" data-description=""><?php esc_html_e('Dark', 'wpcloudplugins'); ?></option>
                <option value="light" <?php echo 'light' === $this->settings['colors']['style'] ? "selected='selected'" : ''; ?> data-imagesrc="<?php echo LETSBOX_ROOTPATH; ?>/css/images/skin-light.png" data-description=""><?php esc_html_e('Light', 'wpcloudplugins'); ?></option>
              </select>
              <input type="hidden" name="lets_box_settings[colors][style]" id="wpcp_content_skin" value="<?php echo esc_attr($this->settings['colors']['style']); ?>">

              <?php
              $colors = [
                  'background' => [
                      'label' => esc_html__('Content Background Color', 'wpcloudplugins'),
                      'default' => '#f2f2f2',
                  ],
                  'accent' => [
                      'label' => esc_html__('Accent Color', 'wpcloudplugins'),
                      'default' => '#522058',
                  ],
                  'black' => [
                      'label' => esc_html__('Black', 'wpcloudplugins'),
                      'default' => '#222',
                  ],
                  'dark1' => [
                      'label' => esc_html__('Dark 1', 'wpcloudplugins'),
                      'default' => '#666666',
                  ],
                  'dark2' => [
                      'label' => esc_html__('Dark 2', 'wpcloudplugins'),
                      'default' => '#999999',
                  ],
                  'white' => [
                      'label' => esc_html__('White', 'wpcloudplugins'),
                      'default' => '#fff',
                  ],
                  'light1' => [
                      'label' => esc_html__('Light 1', 'wpcloudplugins'),
                      'default' => '#fcfcfc',
                  ],
                  'light2' => [
                      'label' => esc_html__('Light 2', 'wpcloudplugins'),
                      'default' => '#e8e8e8',
                  ],
              ];

              echo create_color_boxes_table($colors, $this->settings);
              ?>
            </div>

            <div class="letsbox-accordion-title letsbox-option-title"><?php esc_html_e('Icons', 'wpcloudplugins'); ?></div>
            <div>

              <div class="letsbox-option-title"><?php esc_html_e('Icon Set', 'wpcloudplugins'); ?></div>
              <div class="letsbox-option-description"><?php _e(sprintf('Location to the icon set you want to use. When you want to use your own set, just make a copy of the default icon set folder (<code>%s</code>) and place it in the <code>wp-content/</code> folder', LETSBOX_ROOTPATH.'/css/icons/'), 'wpcloudplugins'); ?>.</div>

              <div class="wpcp-warning">
                <i><strong><?php esc_html_e('NOTICE', 'wpcloudplugins'); ?></strong>: <?php esc_html_e('Modifications to the default icons set will be lost during an update.', 'wpcloudplugins'); ?>.</i>
              </div>

              <input class="letsbox-option-input-large" type="text" name="lets_box_settings[icon_set]" id="icon_set" value="<?php echo esc_attr($this->settings['icon_set']); ?>">  
            </div>

            <div class="letsbox-accordion-title letsbox-option-title"><?php esc_html_e('Lightbox', 'wpcloudplugins'); ?></div>
            <div>
              <div class="letsbox-option-title"><?php esc_html_e('Lightbox Skin', 'wpcloudplugins'); ?>
                <?php help_button(esc_html__('Lightbox Skin', 'wpcloudplugins'), esc_html__('Select which skin you want to use for the Inline Preview.', 'wpcloudplugins'));
                ?>                    
              </div>
              <select name="wpcp_lightbox_skin_selectbox" id="wpcp_lightbox_skin_selectbox" class="ddslickbox">
                <?php
                foreach (new DirectoryIterator(LETSBOX_ROOTDIR.'/vendors/iLightBox/') as $fileInfo) {
                    if ($fileInfo->isDir() && !$fileInfo->isDot() && (false !== strpos($fileInfo->getFilename(), 'skin'))) {
                        if (file_exists(LETSBOX_ROOTDIR.'/vendors/iLightBox/'.$fileInfo->getFilename().'/skin.css')) {
                            $selected = '';
                            $skinname = str_replace('-skin', '', $fileInfo->getFilename());

                            if ($skinname === $this->settings['lightbox_skin']) {
                                $selected = 'selected="selected"';
                            }

                            $icon = file_exists(LETSBOX_ROOTDIR.'/vendors/iLightBox/'.$fileInfo->getFilename().'/thumb.jpg') ? LETSBOX_ROOTPATH.'/vendors/iLightBox/'.$fileInfo->getFilename().'/thumb.jpg' : '';
                            echo '<option value="'.$skinname.'" data-imagesrc="'.$icon.'" data-description="" '.$selected.'>'.$fileInfo->getFilename()."</option>\n";
                        }
                    }
                }
                ?>
              </select>
              <input type="hidden" name="lets_box_settings[lightbox_skin]" id="wpcp_lightbox_skin" value="<?php echo esc_attr($this->settings['lightbox_skin']); ?>">


              <div class="letsbox-option-title">Lightbox Scroll
                <?php help_button(esc_html__('Lightbox Scroll', 'wpcloudplugins'), esc_html__("Sets path for switching windows. Possible values are 'vertical' and 'horizontal' and the default is 'vertical'.", 'wpcloudplugins'));
                ?>  
              </div>
              <select type="text" name="lets_box_settings[lightbox_path]" id="lightbox_path">
                <option value="horizontal" <?php echo 'horizontal' === $this->settings['lightbox_path'] ? "selected='selected'" : ''; ?>>Horizontal</option>
                <option value="vertical" <?php echo 'vertical' === $this->settings['lightbox_path'] ? "selected='selected'" : ''; ?>>Vertical</option>
              </select>

              <div class="letsbox-option-title">Lightbox <?php esc_html_e('Image Source', 'wpcloudplugins'); ?>
                <?php help_button('Lightbox'.esc_html__('Image Source', 'wpcloudplugins'), esc_html__('Select the source of the images. Large thumbnails load fast, orignal files will take some time to load.', 'wpcloudplugins'));
                ?>         
              </div>
              <select type="text" name="lets_box_settings[loadimages]" id="loadimages">
                <option value="boxthumbnail" <?php echo 'boxthumbnail' === $this->settings['loadimages'] ? "selected='selected'" : ''; ?>>Fast - Large preview thumbnails</option>
                <option value="original" <?php echo 'original' === $this->settings['loadimages'] ? "selected='selected'" : ''; ?>>Slow - Show original files</option>
              </select>

              <div class="letsbox-option-title"><?php esc_html_e('Allow Mouse Click on Image', 'wpcloudplugins'); ?>
                <?php help_button(esc_html__('Allow Mouse Click on Image', 'wpcloudplugins'), esc_html__('Should people be able to access the right click context menu to e.g. save the image?', 'wpcloudplugins'));
                ?>                
                <div class="letsbox-onoffswitch">
                  <input type='hidden' value='No' name='lets_box_settings[lightbox_rightclick]'/>
                  <input type="checkbox" name="lets_box_settings[lightbox_rightclick]" id="lightbox_rightclick" class="letsbox-onoffswitch-checkbox" <?php echo ('Yes' === $this->settings['lightbox_rightclick']) ? 'checked="checked"' : ''; ?>/>
                  <label class="letsbox-onoffswitch-label" for="lightbox_rightclick"></label>
                </div>
              </div>

              <div class="letsbox-option-title"><?php esc_html_e('Header', 'wpcloudplugins'); ?>
                <?php help_button(esc_html__('Header', 'wpcloudplugins'), esc_html__('When should the header containing title and action-menu be shown.', 'wpcloudplugins'));
                ?>                         
              </div>
              <select type="text" name="lets_box_settings[lightbox_showheader]" id="lightbox_showheader">
                <option value="true" <?php echo 'true' === $this->settings['lightbox_showheader'] ? "selected='selected'" : ''; ?>><?php esc_html_e('Always', 'wpcloudplugins'); ?></option>
                <option value="click" <?php echo 'click' === $this->settings['lightbox_showheader'] ? "selected='selected'" : ''; ?>><?php esc_html_e('Show after clicking on the Lightbox', 'wpcloudplugins'); ?></option>
                <option value="mouseenter" <?php echo 'mouseenter' === $this->settings['lightbox_showheader'] ? "selected='selected'" : ''; ?>><?php esc_html_e('Show when hovering over the Lightbox', 'wpcloudplugins'); ?></option>
                <option value="false" <?php echo 'false' === $this->settings['lightbox_showheader'] ? "selected='selected'" : ''; ?>><?php esc_html_e('Never', 'wpcloudplugins'); ?></option>
              </select>  

              <div class="letsbox-option-title"><?php esc_html_e('Caption / Description', 'wpcloudplugins'); ?>
                <?php help_button(esc_html__('Caption / Description', 'wpcloudplugins'), esc_html__('When should the description be shown in the Gallery Lightbox.', 'wpcloudplugins'));
                ?>                  
              </div>
              <select type="text" name="lets_box_settings[lightbox_showcaption]" id="lightbox_showcaption">
                <option value="true" <?php echo 'true' === $this->settings['lightbox_showcaption'] ? "selected='selected'" : ''; ?>><?php esc_html_e('Always', 'wpcloudplugins'); ?></option>
                <option value="click" <?php echo 'click' === $this->settings['lightbox_showcaption'] ? "selected='selected'" : ''; ?>><?php esc_html_e('Show after clicking on the Lightbox', 'wpcloudplugins'); ?></option>
                <option value="mouseenter" <?php echo 'mouseenter' === $this->settings['lightbox_showcaption'] ? "selected='selected'" : ''; ?>><?php esc_html_e('Show when hovering over the Lightbox', 'wpcloudplugins'); ?></option>
                <option value="false" <?php echo 'false' === $this->settings['lightbox_showcaption'] ? "selected='selected'" : ''; ?>><?php esc_html_e('Never', 'wpcloudplugins'); ?></option>
              </select>                

            </div>

            <div class="letsbox-accordion-title letsbox-option-title"><?php esc_html_e('Default Media Player Skin', 'wpcloudplugins'); ?></div>
            <div>           
              <div class="letsbox-option-description"><?php esc_html_e('Select which skin you want to use for the Media Player', 'wpcloudplugins'); ?>.</div>
              <select name="wpcp_mediaplayer_skin_selectbox" id="wpcp_mediaplayer_skin_selectbox" class="ddslickbox">
                <?php
                foreach (new DirectoryIterator(LETSBOX_ROOTDIR.'/skins/') as $fileInfo) {
                    if ($fileInfo->isDir() && !$fileInfo->isDot()) {
                        if (file_exists(LETSBOX_ROOTDIR.'/skins/'.$fileInfo->getFilename().'/js/Player.js')) {
                            $selected = '';
                            if ($fileInfo->getFilename() === $this->settings['mediaplayer_skin']) {
                                $selected = 'selected="selected"';
                            }

                            $icon = file_exists(LETSBOX_ROOTDIR.'/skins/'.$fileInfo->getFilename().'/Thumb.jpg') ? LETSBOX_ROOTPATH.'/skins/'.$fileInfo->getFilename().'/Thumb.jpg' : '';
                            echo '<option value="'.$fileInfo->getFilename().'" data-imagesrc="'.$icon.'" data-description="" '.$selected.'>'.$fileInfo->getFilename()."</option>\n";
                        }
                    }
                }
                ?>
              </select>
              <input type="hidden" name="lets_box_settings[mediaplayer_skin]" id="wpcp_mediaplayer_skin" value="<?php echo esc_attr($this->settings['mediaplayer_skin']); ?>">

              <br/><br/>
              <div class="letsbox-option-title"><?php esc_html_e('Load native MediaElement.js library', 'wpcloudplugins'); ?>
                <?php help_button(esc_html__('Load native MediaElement.js library', 'wpcloudplugins'), esc_html__('Is the layout of the Media Player all mixed up and is it not initiating properly? If that is the case, you might be encountering a conflict between media player libraries on your site. To resolve this, enable this setting to load the native MediaElement.js library.', 'wpcloudplugins'));
                ?>    

                <div class="letsbox-onoffswitch">
                  <input type='hidden' value='No' name='lets_box_settings[mediaplayer_load_native_mediaelement]'/>
                  <input type="checkbox" name="lets_box_settings[mediaplayer_load_native_mediaelement]" id="mediaplayer_load_native_mediaelement" class="letsbox-onoffswitch-checkbox" <?php echo ('Yes' === $this->settings['mediaplayer_load_native_mediaelement']) ? 'checked="checked"' : ''; ?>/>
                  <label class="letsbox-onoffswitch-label" for="mediaplayer_load_native_mediaelement"></label>
                </div>
              </div>           
            </div>

            <div class="letsbox-accordion-title letsbox-option-title"><?php esc_html_e('Custom CSS', 'wpcloudplugins'); ?></div>
            <div>
              <div class="letsbox-option-description"><?php esc_html_e("If you want to modify the looks of the plugin slightly, you can insert here your custom CSS. Don't edit the CSS files itself, because those modifications will be lost during an update.", 'wpcloudplugins'); ?>.</div>
              <textarea name="lets_box_settings[custom_css]" id="custom_css" cols="" rows="10"><?php echo esc_attr($this->settings['custom_css']); ?></textarea>
            </div>
          </div>
        </div>
        <!-- End Layout Tab -->

        <!-- UserFolders Tab -->
        <div id="settings_userfolders"  class="letsbox-tab-panel">
          <div class="letsbox-tab-panel-header"><?php esc_html_e('Private Folders', 'wpcloudplugins'); ?></div>

          <div class="letsbox-accordion">
            <div class="letsbox-accordion-title letsbox-option-title"><?php esc_html_e('Global settings Automatically linked Private Folders', 'wpcloudplugins'); ?> </div>
            <div>

              <div class="wpcp-warning">
                <i><strong>NOTICE</strong>: <?php esc_html_e('The following settings are only used for all shortcodes with automatically linked Private Folders,  unless specified otherwise in the shortcode configuration.', 'wpcloudplugins'); ?> </i>
              </div>

              <div class="letsbox-option-title"><?php esc_html_e('Create Private Folders on registration', 'wpcloudplugins'); ?>
                <?php help_button(esc_html__('Create Private Folders on registration', 'wpcloudplugins'), esc_html__('Automatically create the users Private Folders after it has been registered on the site.', 'wpcloudplugins'));
                ?>                   
                <div class="letsbox-onoffswitch">
                  <input type='hidden' value='No' name='lets_box_settings[userfolder_oncreation]'/>
                  <input type="checkbox" name="lets_box_settings[userfolder_oncreation]" id="userfolder_oncreation" class="letsbox-onoffswitch-checkbox" <?php echo ('Yes' === $this->settings['userfolder_oncreation']) ? 'checked="checked"' : ''; ?>/>
                  <label class="letsbox-onoffswitch-label" for="userfolder_oncreation"></label>
                </div>
              </div>

              <div class="letsbox-option-title"><?php esc_html_e('Create all Private Folders the 1st time a module is used', 'wpcloudplugins'); ?>
                <?php help_button(esc_html__('Create all Private Folders the 1st time a module is used', 'wpcloudplugins'), esc_html__('Create all the Private Folders for users with access to the modules that have this feature enabled.', 'wpcloudplugins'));
                ?>                   
                <div class="letsbox-onoffswitch">
                  <input type='hidden' value='No' name='lets_box_settings[userfolder_onfirstvisit]'/>
                  <input type="checkbox" name="lets_box_settings[userfolder_onfirstvisit]" id="userfolder_onfirstvisit" class="letsbox-onoffswitch-checkbox" <?php echo ('Yes' === $this->settings['userfolder_onfirstvisit']) ? 'checked="checked"' : ''; ?>/>
                  <label class="letsbox-onoffswitch-label" for="userfolder_onfirstvisit"></label>
                </div>
              </div>
              <div class="wpcp-warning">
                <i><strong>NOTICE</strong>: Creating User Folders takes around 1 sec per user, so it isn't recommended to create those on first visit when you have tons of users.</i>
              </div>


              <div class="letsbox-option-title"><?php esc_html_e('Update Private Folders after profile update', 'wpcloudplugins'); ?>
                <?php help_button(esc_html__('Update Private Folders after profile update', 'wpcloudplugins'), esc_html__('If needed, update the name of the Private Folder for an user after they have updated their profile.', 'wpcloudplugins'));
                ?>                        
                <div class="letsbox-onoffswitch">
                  <input type='hidden' value='No' name='lets_box_settings[userfolder_update]'/>
                  <input type="checkbox" name="lets_box_settings[userfolder_update]" id="userfolder_update" class="letsbox-onoffswitch-checkbox" <?php echo ('Yes' === $this->settings['userfolder_update']) ? 'checked="checked"' : ''; ?>/>
                  <label class="letsbox-onoffswitch-label" for="userfolder_update"></label>
                </div>
              </div>

              <div class="letsbox-option-title"><?php esc_html_e('Remove Private Folders after account removal', 'wpcloudplugins'); ?>
                <?php help_button(esc_html__('Remove Private Folders after account removal', 'wpcloudplugins'), esc_html__('Try to remove Private Folders after they are deleted', 'wpcloudplugins'));
                ?>                    
                <div class="letsbox-onoffswitch">
                  <input type='hidden' value='No' name='lets_box_settings[userfolder_remove]'/>
                  <input type="checkbox" name="lets_box_settings[userfolder_remove]" id="userfolder_remove" class="letsbox-onoffswitch-checkbox" <?php echo ('Yes' === $this->settings['userfolder_remove']) ? 'checked="checked"' : ''; ?> />
                  <label class="letsbox-onoffswitch-label" for="userfolder_remove"></label>
                </div>
              </div>

              <div class="letsbox-option-title"><?php esc_html_e('Name Template', 'wpcloudplugins'); ?></div>
              <div class="letsbox-option-description"><?php echo esc_html__('Template name for automatically created Private Folders.', 'wpcloudplugins').' '.esc_html__('The naming template can also be set per shortcode individually.', 'wpcloudplugins').' '.sprintf(esc_html__('Available placeholders: %s', 'wpcloudplugins'), '').'<code>%user_login%</code>,  <code>%user_firstname%</code>, <code>%user_lastname%</code>, <code>%user_email%</code>, <code>%display_name%</code>, <code>%ID%</code>, <code>%user_role%</code>, <code>%usermeta_{key}%</code>,<code>%post_id%</code>,<code>%post_title%</code>, <code>%postmeta_{key}%</code>, <code>%yyyy-mm-dd%</code>, <code>%hh:mm%</code>, <code>%uniqueID%</code>, <code>%directory_separator% (/)</code>'; ?>.</div>
              <input class="letsbox-option-input-large" type="text" name="lets_box_settings[userfolder_name]" id="userfolder_name" value="<?php echo esc_attr($this->settings['userfolder_name']); ?>">

            </div>

            <div class="letsbox-accordion-title letsbox-option-title"><?php esc_html_e('Global settings Manually linked Private Folders', 'wpcloudplugins'); ?> </div>
            <div>

              <div class="wpcp-warning">
                <i><strong>NOTICE</strong>: <?php echo sprintf(esc_html__('You can manually link users to their Private Folder via the %s[Link Private Folders]%s menu page', 'wpcloudplugins'), '<a href="'.admin_url('admin.php?page=LetsBox_settings_linkusers').'" target="_blank">', '</a>'); ?>. </i>
              </div>

              <div class="letsbox-option-title"><?php esc_html_e('Access Forbidden notice', 'wpcloudplugins'); ?>
                <?php help_button(esc_html__('Access Forbidden notice', 'wpcloudplugins'), esc_html__("Message that is displayed when an user is visiting a shortcode with the Private Folders feature set to 'Manual' mode while it doesn't have Private Folder linked to its account", 'wpcloudplugins'));
                ?>    
              </div>

              <?php
              ob_start();
              wp_editor($this->settings['userfolder_noaccess'], 'lets_box_settings_userfolder_noaccess', [
                  'textarea_name' => 'lets_box_settings[userfolder_noaccess]',
                  'teeny' => true,
                  'tinymce' => false,
                  'textarea_rows' => 15,
                  'media_buttons' => false,
              ]);
              echo ob_get_clean();
              ?>


            </div>

            <div class="letsbox-accordion-title letsbox-option-title"><?php esc_html_e('Private Folders in WP Admin Dashboard', 'wpcloudplugins'); ?> </div>
            <div>

              <div class="wpcp-warning">
                <i><strong>NOTICE</strong>: <?php esc_html_e('This setting only restrict access of the File Browsers in the Admin Dashboard (e.g. the ones in the Shortcode Builder and the File Browser menu). To enable Private Folders for your own Shortcodes, use the Shortcode Builder', 'wpcloudplugins'); ?>. </i>
              </div>

              <div class="letsbox-option-description"><?php esc_html_e('Enables Private Folders in the Shortcode Builder and Back-End File Browser', 'wpcloudplugins'); ?>.</div>
              <select type="text" name="lets_box_settings[userfolder_backend]" id="userfolder_backend" data-div-toggle="private-folders-auto" data-div-toggle-value="auto">
                <option value="No" <?php echo 'No' === $this->settings['userfolder_backend'] ? "selected='selected'" : ''; ?>>No</option>
                <option value="manual" <?php echo 'manual' === $this->settings['userfolder_backend'] ? "selected='selected'" : ''; ?>><?php esc_html_e('Yes, I link the users Manually', 'wpcloudplugins'); ?></option>
                <option value="auto" <?php echo 'auto' === $this->settings['userfolder_backend'] ? "selected='selected'" : ''; ?>><?php esc_html_e('Yes, let the plugin create the User Folders for me', 'wpcloudplugins'); ?></option>
              </select>

              <?php
              if ($this->get_app()->has_access_token()) {
                  try {
                      $this->get_app()->start_client();
                      $rootfolder = $this->get_processor()->get_client()->get_root_folder(); ?>
                      <div class="letsbox-suboptions private-folders-auto <?php echo ('auto' === ($this->settings['userfolder_backend'])) ? '' : 'hidden'; ?> ">
                        <div class="letsbox-option-title"><?php esc_html_e('Root folder for Private Folders', 'wpcloudplugins'); ?></div>
                        <div class="letsbox-option-description"><?php esc_html_e('Select in which folder the Private Folders should be created', 'wpcloudplugins'); ?>. <?php esc_html_e('Current selected folder', 'wpcloudplugins'); ?>:</div>
                        <?php
                        $private_auto_folder = $this->settings['userfolder_backend_auto_root'];

                      if (empty($private_auto_folder)) {
                          $root = $this->get_processor()->get_client()->get_root_folder();
                          $private_auto_folder = [];
                          $private_auto_folder['id'] = $root->get_entry()->get_id();
                          $private_auto_folder['name'] = $root->get_entry()->get_name();
                          $private_auto_folder['view_roles'] = ['administrator'];
                      } ?>
                        <input class="letsbox-option-input-large private-folders-auto-current" type="text" value="<?php echo $private_auto_folder['name']; ?>" disabled="disabled">
                        <input class="private-folders-auto-input-id" type='hidden' value='<?php echo $private_auto_folder['id']; ?>' name='lets_box_settings[userfolder_backend_auto_root][id]'/>
                        <input class="private-folders-auto-input-name" type='hidden' value='<?php echo $private_auto_folder['name']; ?>' name='lets_box_settings[userfolder_backend_auto_root][name]'/>
                        <div id="wpcp-select-root-button" type="button" class="button-primary private-folders-auto-button"><?php esc_html_e('Select Folder', 'wpcloudplugins'); ?>&nbsp;<div class='wpcp-spinner'></div></div>

                        <div id='lb-embedded' style='clear:both;display:none'>
                          <?php
                          echo $this->get_processor()->create_from_shortcode(
                          ['mode' => 'files',
                              'showfiles' => '1',
                              'filesize' => '0',
                              'filedate' => '0',
                              'upload' => '0',
                              'delete' => '0',
                              'rename' => '0',
                              'addfolder' => '0',
                              'showbreadcrumb' => '1',
                              'showfiles' => '0',
                              'downloadrole' => 'none',
                              'candownloadzip' => '0',
                              'showsharelink' => '0',
                              'mcepopup' => 'linktobackendglobal',
                              'search' => '0', ]
                      ); ?>
                        </div>

                        <br/><br/>
                        <div class="letsbox-option-title"><?php esc_html_e('Full Access', 'wpcloudplugins'); ?></div>
                        <div class="letsbox-option-description"><?php esc_html_e('By default only Administrator users will be able to navigate through all Private Folders', 'wpcloudplugins'); ?>. <?php esc_html_e('When you want other User Roles to be able do browse trough all the Private Folders as well, please add them below', 'wpcloudplugins'); ?>.</div>

                        <?php
                        $selected = (isset($private_auto_folder['view_roles'])) ? $private_auto_folder['view_roles'] : [];
                      wp_roles_and_users_input('lets_box_settings[userfolder_backend_auto_root][view_roles]', $selected); ?>
                      </div>
                      <?php
                  } catch (\Exception $ex) {
                  }
              }
              ?>

            </div>
          </div>

        </div>
        <!-- End UserFolders Tab -->

        <!--  Advanced Tab -->
        <div id="settings_advanced"  class="letsbox-tab-panel">
          <div class="letsbox-tab-panel-header"><?php esc_html_e('Advanced', 'wpcloudplugins'); ?></div>

          <?php if (false === $network_wide_authorization) { ?>
              <div class="letsbox-option-title"><?php esc_html_e('Own App', 'wpcloudplugins'); ?>

                <div class="letsbox-onoffswitch">
                  <input type='hidden' value='No' name='lets_box_settings[box_app_own]'/>
                  <input type="checkbox" name="lets_box_settings[box_app_own]" id="box_app_own" class="letsbox-onoffswitch-checkbox" <?php echo (empty($this->settings['box_app_client_id']) || empty($this->settings['box_app_client_secret'])) ? '' : 'checked="checked"'; ?> data-div-toggle="own-app"/>
                  <label class="letsbox-onoffswitch-label" for="box_app_own"></label>
                </div>
              </div>

              <div class="letsbox-suboptions own-app <?php echo (empty($this->settings['box_app_client_id']) || empty($this->settings['box_app_client_secret'])) ? 'hidden' : ''; ?> ">
                <div class="letsbox-option-description">

                  <strong>Using your own Box App is <u>optional</u></strong>. For an easy setup you can just use the default App of the plugin itself by leaving the ID and Secret empty. The advantage of using your own app is limited. If you decided to create your own Box App anyway, please enter your settings. In the <a href="https://florisdeleeuwnl.zendesk.com/hc/en-us/articles/202548376-How-do-I-create-my-own-Box-App-" target="_blank">documentation</a> you can find how you can create a Box App.
                  <br/><br/>

                  <div class="wpcp-warning">
                    <i><strong>NOTICE</strong>: If you encounter any issues when trying to use your own App, please fall back on the default App by disabling this setting.</i>
                  </div>
                </div>

                <div class="letsbox-option-title">Box Client ID
                  <?php help_button('Client ID', esc_html__('Only if you want to use your own App, insert your Client ID here', 'wpcloudplugins'));
                  ?>        
                </div>
                <input class="letsbox-option-input-large" type="text" name="lets_box_settings[box_app_client_id]" id="box_app_client_id" value="<?php echo esc_attr($this->settings['box_app_client_id']); ?>" placeholder="<--- <?php esc_html_e('Leave empty for easy setup', 'wpcloudplugins'); ?> --->" >

                <div class="letsbox-option-title">Box Client secret
                  <?php help_button('Box App Secret', esc_html__('If you want to use your own App, insert your Client Secret here', 'wpcloudplugins'));
                  ?>                        
                </div>
                <input class="letsbox-option-input-large" type="text" name="lets_box_settings[box_app_client_secret]" id="box_app_client_secret" value="<?php echo esc_attr($this->settings['box_app_client_secret']); ?>" placeholder="<--- <?php esc_html_e('Leave empty for easy setup', 'wpcloudplugins'); ?> --->" >   

                <div class="letsbox-option-title">OAuth 2.0 Redirect URI
                  <?php help_button('OAuth 2.0 Redirect URI', esc_html__('Set the redirect URI in your application to the following uri:', 'wpcloudplugins'));
                  ?>     
                </div>
                <code style="user-select:initial">
                  <?php
                  if ($this->get_app()->has_plugin_own_app()) {
                      echo $this->get_app()->get_auth_url();
                  } else {
                      echo esc_html__('Enter Client ID and Secret, save settings and reload the page to see the Redirect URI you will need', 'wpcloudplugins');
                  }
                  ?>
                </code>
              </div>

              <?php
              $account_type = $this->get_app()->get_account_type();
              if (!empty($account_type) && 'personal' !== $account_type) {
                  ?>
                  <div class="letsbox-option-title"><?php esc_html_e('Business Accounts', 'wpcloudplugins'); ?> | <?php esc_html_e('Scope shared-links', 'wpcloudplugins'); ?>      
                    <?php help_button(esc_html__('Scope shared-links', 'wpcloudplugins'), esc_html__('Who should be able to access the links that are created by the plugin? If set to <strong>Public</strong> the links will be accessible by anyone. <strong>Within Organization</strong> will make links accessible within your organization only. Anonymous links may be disabled by the tenant administrator', 'wpcloudplugins')); ?>   
                  </div>

                  <select type="text" name="lets_box_settings[link_scope]" id="link_scope">
                    <option value="open" <?php echo 'open' === $this->settings['link_scope'] ? "selected='selected'" : ''; ?>>Public</option>
                    <option value="company" <?php echo 'company' === $this->settings['link_scope'] ? "selected='selected'" : ''; ?>>Within Organization</option>
                  </select>
                  <?php
              }
          }
          ?>

          <div class="letsbox-option-title"><?php esc_html_e('Load Javascripts on all pages', 'wpcloudplugins'); ?>
            <?php help_button(esc_html__('Load Javascripts on all pages', 'wpcloudplugins'), esc_html__('By default the plugin will only load it scripts when the shortcode is present on the page. If you are dynamically loading content via AJAX calls and the plugin does not show up, please enable this setting', 'wpcloudplugins'));
            ?>               
            <div class="letsbox-onoffswitch">
              <input type='hidden' value='No' name='lets_box_settings[always_load_scripts]'/>
              <input type="checkbox" name="lets_box_settings[always_load_scripts]" id="always_load_scripts" class="letsbox-onoffswitch-checkbox" <?php echo ('Yes' === $this->settings['always_load_scripts']) ? 'checked="checked"' : ''; ?> />
              <label class="letsbox-onoffswitch-label" for="always_load_scripts"></label>
            </div>
          </div>       

          <div class="letsbox-option-title"><?php esc_html_e('Enable Gzip compression', 'wpcloudplugins'); ?>
            <?php help_button(esc_html__('Enable Gzip compression', 'wpcloudplugins'), esc_html__("Enables gzip-compression if the visitor's browser can handle it. This will increase the performance of the plugin if you are displaying large amounts of files and it reduces bandwidth usage as well. It uses the PHP ob_gzhandler() callback. Please use this setting with caution. Always test if the plugin still works on the Front-End as some servers are already configured to gzip content!", 'wpcloudplugins'));
            ?>               
            <div class="letsbox-onoffswitch">
              <input type='hidden' value='No' name='lets_box_settings[gzipcompression]'/>
              <input type="checkbox" name="lets_box_settings[gzipcompression]" id="gzipcompression" class="letsbox-onoffswitch-checkbox" <?php echo ('Yes' === $this->settings['gzipcompression']) ? 'checked="checked"' : ''; ?> />
              <label class="letsbox-onoffswitch-label" for="gzipcompression"></label>
            </div>
          </div>

          <div class="letsbox-option-title"><?php esc_html_e('Nonce Validation', 'wpcloudplugins'); ?>
            <?php help_button(esc_html__('Nonce Validation', 'wpcloudplugins'), esc_html__('The plugin uses, among others, the WordPress Nonce system to protect you against several types of attacks including CSRF. Disable this in case you are encountering a conflict with a plugin that alters this system', 'wpcloudplugins'));
            ?> 

            <div class="letsbox-onoffswitch">
              <input type='hidden' value='No' name='lets_box_settings[nonce_validation]'/>
              <input type="checkbox" name="lets_box_settings[nonce_validation]" id="nonce_validation" class="letsbox-onoffswitch-checkbox" <?php echo ('Yes' === $this->settings['nonce_validation']) ? 'checked="checked"' : ''; ?> />
              <label class="letsbox-onoffswitch-label" for="nonce_validation"></label>
            </div></div>

          <div class="wpcp-warning">
            <i><strong>NOTICE</strong>: Please use this setting with caution! Only disable it when really necessary.</i>
          </div>

          <div class="letsbox-option-title"><?php esc_html_e('Delete settings on Uninstall', 'wpcloudplugins'); ?>
            <?php help_button(esc_html__('Delete settings on Uninstall', 'wpcloudplugins'), esc_html__('When you uninstall the plugin, what do you want to do with your settings? You can save them for next time, or wipe them back to factory settings.', 'wpcloudplugins'));
            ?>              
            <div class="letsbox-onoffswitch">
              <input type='hidden' value='No' name='lets_box_settings[uninstall_reset]'/>
              <input type="checkbox" name="lets_box_settings[uninstall_reset]" id="uninstall_reset" class="letsbox-onoffswitch-checkbox" <?php echo ('Yes' === $this->settings['uninstall_reset']) ? 'checked="checked"' : ''; ?> />
              <label class="letsbox-onoffswitch-label" for="uninstall_reset"></label>
            </div>
          </div>
          <div class="wpcp-warning">
            <i><strong>NOTICE</strong>: <?php echo esc_html__('When you reset the settings, the plugin will not longer be linked to your accounts, but their authorization will not be revoked', 'wpcloudplugins').'. '.esc_html__('You can revoke the authorization via the General tab', 'wpcloudplugins').'.'; ?></a></i>
          </div>

        </div>
        <!-- End Advanced Tab -->

        <!-- Integrations Tab -->
        <div id="settings_integrations"  class="letsbox-tab-panel">
          <div class="letsbox-tab-panel-header"><?php esc_html_e('Integrations', 'wpcloudplugins'); ?></div>

          <div class="letsbox-accordion">
            <div class="letsbox-accordion-title letsbox-option-title">Social Sharing Buttons</div>
            <div>
              <div class="letsbox-option-description"><?php esc_html_e('Select which sharing buttons should be accessible via the sharing dialogs of the plugin.', 'wpcloudplugins'); ?></div>

              <div class="shareon shareon-settings">
                <?php foreach ($this->settings['share_buttons'] as $button => $value) {
                $title = ucfirst($button);
                echo "<button type='button' class='wpcp-shareon-toggle-button {$button} shareon-{$value} ' title='{$title}'></button>";
                echo "<input type='hidden' value='{$value}' name='lets_box_settings[share_buttons][{$button}]'/>";
            }
                ?>
              </div>
            </div>
          </div>

          <div class="letsbox-accordion">
            <div class="letsbox-accordion-title letsbox-option-title"><?php esc_html_e('Shortlinks API', 'wpcloudplugins'); ?></div>

            <div>
              <div class="letsbox-option-description"><?php esc_html_e('Select which Url Shortener Service you want to use', 'wpcloudplugins'); ?>.</div>
              <select type="text" name="lets_box_settings[shortlinks]" id="wpcp-shortlinks-selector">
                <option value="None"  <?php echo 'None' === $this->settings['shortlinks'] ? "selected='selected'" : ''; ?>>None</option>
                <option value="Shorte.st"  <?php echo 'Shorte.st' === $this->settings['shortlinks'] ? "selected='selected'" : ''; ?>>Shorte.st</option>
                <option value="Rebrandly"  <?php echo 'Rebrandly' === $this->settings['shortlinks'] ? "selected='selected'" : ''; ?>>Rebrandly</option>
                <option value="Bit.ly"  <?php echo 'Bit.ly' === $this->settings['shortlinks'] ? "selected='selected'" : ''; ?>>Bit.ly</option>
              </select>   

              <div class="letsbox-suboptions option shortest" <?php echo 'Shorte.st' !== $this->settings['shortlinks'] ? "style='display:none;'" : ''; ?>>
                <div class="letsbox-option-description"><?php esc_html_e('Sign up for Shorte.st', 'wpcloudplugins'); ?> and <a href="https://shorte<?php echo '.st/tools/api'; ?>" target="_blank">grab your API token</a></div>

                <div class="letsbox-option-title"><?php esc_html_e('API token', 'wpcloudplugins'); ?></div>
                <input class="letsbox-option-input-large" type="text" name="lets_box_settings[shortest_apikey]" id="shortest_apikey" value="<?php echo esc_attr($this->settings['shortest_apikey']); ?>">
              </div>

              <div class="letsbox-suboptions option bitly" <?php echo 'Bit.ly' !== $this->settings['shortlinks'] ? "style='display:none;'" : ''; ?>>
                <div class="letsbox-option-description"><a href="https://bitly.com/a/sign_up" target="_blank"><?php esc_html_e('Sign up for Bitly', 'wpcloudplugins'); ?></a> and <a href="http://bitly.com/a/your_api_key" target="_blank">generate an Access Token</a></div>

                <div class="letsbox-option-title">Bitly Login</div>
                <input class="letsbox-option-input-large" type="text" name="lets_box_settings[bitly_login]" id="bitly_login" value="<?php echo esc_attr($this->settings['bitly_login']); ?>">

                <div class="letsbox-option-title">Bitly Access Token</div>
                <input class="letsbox-option-input-large" type="text" name="lets_box_settings[bitly_apikey]" id="bitly_apikey" value="<?php echo esc_attr($this->settings['bitly_apikey']); ?>">
              </div> 

              <div class="letsbox-suboptions option rebrandly" <?php echo 'Rebrandly' !== $this->settings['shortlinks'] ? "style='display:none;'" : ''; ?>>
                <div class="letsbox-option-description"><a href="https://app.rebrandly.com/" target="_blank"><?php esc_html_e('Sign up for Rebrandly', 'wpcloudplugins'); ?></a> and <a href="https://app.rebrandly.com/account/api-keys" target="_blank">grab your API token</a></div>

                <div class="letsbox-option-title">Rebrandly API key</div>
                <input class="letsbox-option-input-large" type="text" name="lets_box_settings[rebrandly_apikey]" id="rebrandly_apikey" value="<?php echo esc_attr($this->settings['rebrandly_apikey']); ?>">

                <div class="letsbox-option-title">Rebrandly Domain (optional)</div>
                <input class="letsbox-option-input-large" type="text" name="lets_box_settings[rebrandly_domain]" id="rebrandly_domain" value="<?php echo esc_attr($this->settings['rebrandly_domain']); ?>">

                <div class="letsbox-option-title">Rebrandly WorkSpace ID (optional)</div>
                <input class="letsbox-option-input-large" type="text" name="lets_box_settings[rebrandly_workspace]" id="rebrandly_workspace" value="<?php echo esc_attr($this->settings['rebrandly_workspace']); ?>">
                
              </div>
            </div>
          </div>

          <div class="letsbox-accordion">

            <div class="letsbox-accordion-title letsbox-option-title">ReCaptcha V3         </div>
            <div>

              <div class="letsbox-option-description"><?php esc_html_e('reCAPTCHA protects you against spam and other types of automated abuse. With this reCAPTCHA (V3) integration module, you can block abusive downloads of your files by bots. Create your own credentials via the link below.', 'wpcloudplugins'); ?> <br/><br/><a href="https://www.google.com/recaptcha/admin" target="_blank">Manage your reCAPTCHA API keys</a></div>

              <div class="letsbox-option-title"><?php esc_html_e('Site Key', 'wpcloudplugins'); ?></div>
              <input class="letsbox-option-input-large" type="text" name="lets_box_settings[recaptcha_sitekey]" id="recaptcha_sitekey" value="<?php echo esc_attr($this->settings['recaptcha_sitekey']); ?>">

              <div class="letsbox-option-title"><?php esc_html_e('Secret Key', 'wpcloudplugins'); ?></div>
              <input class="letsbox-option-input-large" type="text" name="lets_box_settings[recaptcha_secret]" id="recaptcha_secret" value="<?php echo esc_attr($this->settings['recaptcha_secret']); ?>">
            </div>
          </div>

          <div class="letsbox-accordion">

            <div class="letsbox-accordion-title letsbox-option-title"><?php esc_html_e('Video Advertisements (IMA/VAST)', 'wpcloudplugins'); ?> </div>
            <div>
              <div class="letsbox-option-description"><?php esc_html_e('The mediaplayer of the plugin supports VAST XML advertisments to offer monetization options for your videos. You can enable advertisments for the complete site and per Media Player shortcode. Currently, this plugin only supports Linear elements with MP4', 'wpcloudplugins'); ?>.</div>

              <div class="letsbox-option-title"><?php echo 'VAST XML Tag Url'; ?></div>
              <input class="letsbox-option-input-large" type="text" name="lets_box_settings[mediaplayer_ads_tagurl]" id="mediaplayer_ads_tagurl" value="<?php echo esc_attr($this->settings['mediaplayer_ads_tagurl']); ?>" placeholder="<?php echo esc_html__('Leave empty to disable Ads', 'wpcloudplugins'); ?>" />

              <div class="wpcp-warning">
                <i><strong><?php esc_html_e('NOTICE', 'wpcloudplugins'); ?></strong>: <?php esc_html_e('If you are unable to see the example VAST url below, please make sure you do not have an ad blocker enabled.', 'wpcloudplugins'); ?>.</i>
              </div>

              <a href="https://pubads.g.doubleclick.net/gampad/ads?sz=640x480&iu=/124319096/external/single_ad_samples&ciu_szs=300x250&impl=s&gdfp_req=1&env=vp&output=vast&unviewed_position_start=1&cust_params=deployment%3Ddevsite%26sample_ct%3Dskippablelinear&correlator=" rel="no-follow">Example Tag URL</a>

              <div class="letsbox-option-title"><?php esc_html_e('Enable Skip Button', 'wpcloudplugins'); ?>
                <div class="letsbox-onoffswitch">
                  <input type='hidden' value='No' name='lets_box_settings[mediaplayer_ads_skipable]'/>
                  <input type="checkbox" name="lets_box_settings[mediaplayer_ads_skipable]" id="mediaplayer_ads_skipable" class="letsbox-onoffswitch-checkbox" <?php echo ('Yes' === $this->settings['mediaplayer_ads_skipable']) ? 'checked="checked"' : ''; ?> data-div-toggle="ads_skipable"/>
                  <label class="letsbox-onoffswitch-label" for="mediaplayer_ads_skipable"></label>
                </div>
              </div>

              <div class="letsbox-suboptions ads_skipable <?php echo ('Yes' === $this->settings['mediaplayer_ads_skipable']) ? '' : 'hidden'; ?> ">
                <div class="letsbox-option-title"><?php esc_html_e('Skip button visible after (seconds)', 'wpcloudplugins'); ?>
                  <?php help_button(esc_html__('Skip button visible after (seconds)', 'wpcloudplugins'), esc_html__('Allow user to skip advertisment after after the following amount of seconds have elapsed', 'wpcloudplugins'));
                  ?>                  
                </div>
                <input class="letsbox-option-input-large" type="text" name="lets_box_settings[mediaplayer_ads_skipable_after]" id="mediaplayer_ads_skipable_after" value="<?php echo esc_attr($this->settings['mediaplayer_ads_skipable_after']); ?>" placeholder="5">
              </div>
            </div>
          </div>

        </div>  
        <!-- End Integrations info -->

        <!-- Notifications Tab -->
        <div id="settings_notifications"  class="letsbox-tab-panel">

          <div class="letsbox-tab-panel-header"><?php esc_html_e('Notifications', 'wpcloudplugins'); ?></div>

          <div class="letsbox-accordion">
            <div class="letsbox-accordion-title letsbox-option-title"><?php esc_html_e('Sender settings', 'wpcloudplugins'); ?>         </div>
            <div>
              <div class="letsbox-option-title"><?php esc_html_e('From Name', 'wpcloudplugins'); ?>
                <?php help_button(esc_html__('From Name', 'wpcloudplugins'), esc_html__('Enter the name you would like the notification email sent from, or use one of the available placeholders.', 'wpcloudplugins'));
                ?>              
              </div>
              <input class="letsbox-option-input-large" type="text" name="lets_box_settings[notification_from_name]" id="notification_from_name" value="<?php echo esc_attr($this->settings['notification_from_name']); ?>">  

              <div class="letsbox-option-title"><?php esc_html_e('From Email', 'wpcloudplugins'); ?>
                <?php help_button(esc_html__('From Email', 'wpcloudplugins'), esc_html__('Enter an authorized email address you would like the notification email sent from. To avoid deliverability issues, always use your site domain in the from email.', 'wpcloudplugins'));
                ?>               
              </div>
              <input class="letsbox-option-input-large" type="text" name="lets_box_settings[notification_from_email]" id="notification_from_email" value="<?php echo esc_attr($this->settings['notification_from_email']); ?>">  
            </div>
          </div>
          
          <?php if (false === $network_wide_authorization) { ?>
          <div class="letsbox-accordion">
            <div class="letsbox-accordion-title letsbox-option-title"><?php esc_html_e('Lost Authorization notification', 'wpcloudplugins'); ?>         </div>
            <div>
              <div class="letsbox-option-description"><?php esc_html_e('If the plugin somehow loses its authorization, a notification email will be send to the following email address', 'wpcloudplugins'); ?>:</div>
              <input class="letsbox-option-input-large" type="text" name="lets_box_settings[lostauthorization_notification]" id="lostauthorization_notification" value="<?php echo esc_attr($this->settings['lostauthorization_notification']); ?>">  
            </div>
          </div>
          <?php } ?>
                    
          <div class="letsbox-accordion">
            <div class="letsbox-accordion-title letsbox-option-title"><?php esc_html_e('Download Notifications', 'wpcloudplugins'); ?>         </div>
            <div>

              <div class="letsbox-option-title"><?php esc_html_e('Subject download notification', 'wpcloudplugins'); ?>:</div>
              <input class="letsbox-option-input-large" type="text" name="lets_box_settings[download_template_subject]" id="download_template_subject" value="<?php echo esc_attr($this->settings['download_template_subject']); ?>">

              <div class="letsbox-option-title"><?php esc_html_e('Subject zip notification', 'wpcloudplugins'); ?>:</div>
              <input class="letsbox-option-input-large" type="text" name="lets_box_settings[download_template_subject_zip]" id="download_template_subject_zip" value="<?php echo esc_attr($this->settings['download_template_subject_zip']); ?>">

              <div class="letsbox-option-title"><?php esc_html_e('Template download', 'wpcloudplugins'); ?> (HTML):</div>
              <?php
              ob_start();
              wp_editor($this->settings['download_template'], 'lets_box_settings_download_template', [
                  'textarea_name' => 'lets_box_settings[download_template]',
                  'teeny' => true,
                  'tinymce' => false,
                  'textarea_rows' => 15,
                  'media_buttons' => false,
              ]);
              echo ob_get_clean();
              ?>

              <br/>

              <div class="letsbox-option-description"><?php echo sprintf(esc_html__('Available placeholders: %s', 'wpcloudplugins'), ''); ?>
                <code>%site_name%</code>, 
                <code>%number_of_files%</code>, 
                <code>%user_name%</code>, 
                <code>%user_email%</code>, 
                <code>%user_firstname%</code>, 
                <code>%user_lastname%</code>,                 
                <code>%recipient_name%</code>, 
                <code>%recipient_email%</code>, 
                <code>%recipient_firstname%</code>, 
                <code>%recipient_lastname%</code>,  
                <code>%admin_email%</code>, 
                <code>%file_name%</code>, 
                <code>%file_size%</code>, 
                <code>%file_icon%</code>, 
                <code>%file_relative_path%</code>, 
                <code>%file_absolute_path%</code>, 
                <code>%file_cloud_shortlived_download_url%</code>, 
                <code>%file_cloud_preview_url%</code>, 
                <code>%file_cloud_shared_url%</code>, 
                <code>%file_download_url%</code>,
                <code>%folder_name%</code>,
                <code>%folder_relative_path%</code>,
                <code>%folder_absolute_path%</code>,
                <code>%folder_url%</code>,
                <code>%ip%</code>, 
                <code>%location%</code>, 
              </div>

            </div>

            <div class="letsbox-accordion-title letsbox-option-title"><?php esc_html_e('Upload Notifications', 'wpcloudplugins'); ?>         </div>
            <div>     

              <div class="letsbox-option-title"><?php esc_html_e('Subject upload notification', 'wpcloudplugins'); ?>:</div>
              <input class="letsbox-option-input-large" type="text" name="lets_box_settings[upload_template_subject]" id="upload_template_subject" value="<?php echo esc_attr($this->settings['upload_template_subject']); ?>">

              <div class="letsbox-option-title"><?php esc_html_e('Template upload', 'wpcloudplugins'); ?> (HTML):</div>
              <?php
              ob_start();
              wp_editor($this->settings['upload_template'], 'lets_box_settings_upload_template', [
                  'textarea_name' => 'lets_box_settings[upload_template]',
                  'teeny' => true,
                  'tinymce' => false,
                  'textarea_rows' => 15,
                  'media_buttons' => false,
              ]);
              echo ob_get_clean();
              ?>

              <br/>

              <div class="letsbox-option-description"><?php echo sprintf(esc_html__('Available placeholders: %s', 'wpcloudplugins'), ''); ?>
                <code>%site_name%</code>, 
                <code>%number_of_files%</code>, 
                <code>%user_name%</code>, 
                <code>%user_email%</code>, 
                <code>%user_firstname%</code>, 
                <code>%user_lastname%</code>,                 
                <code>%recipient_name%</code>, 
                <code>%recipient_email%</code>, 
                <code>%recipient_firstname%</code>, 
                <code>%recipient_lastname%</code>, 
                <code>%admin_email%</code>, 
                <code>%file_name%</code>, 
                <code>%file_size%</code>, 
                <code>%file_icon%</code>, 
                <code>%file_relative_path%</code>, 
                <code>%file_absolute_path%</code>, 
                <code>%file_cloud_shortlived_download_url%</code>, 
                <code>%file_cloud_preview_url%</code>, 
                <code>%file_cloud_shared_url%</code>, 
                <code>%file_download_url%</code>,
                <code>%folder_name%</code>,
                <code>%folder_relative_path%</code>,
                <code>%folder_absolute_path%</code>,
                <code>%folder_url%</code>,
                <code>%ip%</code>, 
                <code>%location%</code>, 
              </div>

            </div>


            <div class="letsbox-accordion-title letsbox-option-title"><?php esc_html_e('Delete Notifications', 'wpcloudplugins'); ?>         </div>
            <div>
              <div class="letsbox-option-title"><?php esc_html_e('Subject deletion notification', 'wpcloudplugins'); ?>:</div>
              <input class="letsbox-option-input-large" type="text" name="lets_box_settings[delete_template_subject]" id="delete_template_subject" value="<?php echo esc_attr($this->settings['delete_template_subject']); ?>">

              <div class="letsbox-option-title"><?php esc_html_e('Template deletion', 'wpcloudplugins'); ?> (HTML):</div>

              <?php
              ob_start();
              wp_editor($this->settings['delete_template'], 'lets_box_settings_delete_template', [
                  'textarea_name' => 'lets_box_settings[delete_template]',
                  'teeny' => true,
                  'tinymce' => false,
                  'textarea_rows' => 15,
                  'media_buttons' => false,
              ]);
              echo ob_get_clean();
              ?>

              <br/>

              <div class="letsbox-option-description"><?php echo sprintf(esc_html__('Available placeholders: %s', 'wpcloudplugins'), ''); ?>
                <code>%site_name%</code>, 
                <code>%number_of_files%</code>, 
                <code>%user_name%</code>, 
                <code>%user_email%</code>, 
                <code>%user_firstname%</code>, 
                <code>%user_lastname%</code>,                 
                <code>%recipient_name%</code>, 
                <code>%recipient_email%</code>, 
                <code>%recipient_firstname%</code>, 
                <code>%recipient_lastname%</code>, 
                <code>%admin_email%</code>, 
                <code>%file_name%</code>, 
                <code>%file_size%</code>, 
                <code>%file_icon%</code>, 
                <code>%file_relative_path%</code>, 
                <code>%file_absolute_path%</code>, 
                <code>%file_cloud_shortlived_download_url%</code>, 
                <code>%file_cloud_preview_url%</code>, 
                <code>%file_cloud_shared_url%</code>, 
                <code>%file_download_url%</code>,
                <code>%folder_name%</code>,
                <code>%folder_relative_path%</code>,
                <code>%folder_absolute_path%</code>,
                <code>%folder_url%</code>,
                <code>%ip%</code>, 
                <code>%location%</code>, 
              </div>

            </div>

            <div class="letsbox-accordion-title letsbox-option-title"><?php esc_html_e('Template File line in %filelist%', 'wpcloudplugins'); ?>        </div>
            <div>
              <div class="letsbox-option-description"><?php esc_html_e('Template for File item in File List in the download/upload/delete template', 'wpcloudplugins'); ?> (HTML).</div>
              <?php
              ob_start();
              wp_editor($this->settings['filelist_template'], 'lets_box_settings_filelist_template', [
                  'textarea_name' => 'lets_box_settings[filelist_template]',
                  'teeny' => true,
                  'tinymce' => false,
                  'textarea_rows' => 15,
                  'media_buttons' => false,
              ]);
              echo ob_get_clean();
              ?>

              <br/>

              <div class="letsbox-option-description"><?php echo sprintf(esc_html__('Available placeholders: %s', 'wpcloudplugins'), ''); ?>
                <code>%file_name%</code>, 
                <code>%file_size%</code>, 
                <code>%file_lastedited%</code>,                
                <code>%file_icon%</code>, 
                <code>%file_cloud_shortlived_download_url%</code>, 
                <code>%file_cloud_preview_url%</code>, 
                <code>%file_cloud_shared_url%</code>, 
                <code>%file_download_url%</code>,
                <code>%file_relative_path%</code>, 
                <code>%file_absolute_path%</code>, 
                <code>%folder_relative_path%</code>,
                <code>%folder_absolute_path%</code>,
                <code>%folder_url%</code>,
              </div>

            </div>
          </div>

          <div id="wpcp-reset-notifications-button" type="button" class="simple-button blue"><?php esc_html_e('Reset to default notifications', 'wpcloudplugins'); ?>&nbsp;<div class="wpcp-spinner"></div></div>

        </div>
        <!-- End Notifications Tab -->

        <!--  Permissions Tab -->
        <div id="settings_permissions"  class="letsbox-tab-panel">
          <div class="letsbox-tab-panel-header"><?php esc_html_e('Permissions', 'wpcloudplugins'); ?></div>

          <div class="letsbox-accordion">
            <div class="letsbox-accordion-title letsbox-option-title"><?php esc_html_e('Change Plugin Settings', 'wpcloudplugins'); ?>         </div>
            <div>
              <?php wp_roles_and_users_input('lets_box_settings[permissions_edit_settings]', $this->settings['permissions_edit_settings']); ?>
            </div>

            <div class="letsbox-accordion-title letsbox-option-title"><?php esc_html_e('Link Users to Private Folders', 'wpcloudplugins'); ?>        </div>
            <div>
              <?php wp_roles_and_users_input('lets_box_settings[permissions_link_users]', $this->settings['permissions_link_users']); ?>
            </div>

            <div class="letsbox-accordion-title letsbox-option-title"><?php esc_html_e('See Reports', 'wpcloudplugins'); ?>        </div>
            <div>
              <?php wp_roles_and_users_input('lets_box_settings[permissions_see_dashboard]', $this->settings['permissions_see_dashboard']); ?>
            </div>   

            <div class="letsbox-accordion-title letsbox-option-title"><?php esc_html_e('See Back-End Filebrowser', 'wpcloudplugins'); ?>        </div>
            <div>
              <?php wp_roles_and_users_input('lets_box_settings[permissions_see_filebrowser]', $this->settings['permissions_see_filebrowser']); ?>
            </div>

            <div class="letsbox-accordion-title letsbox-option-title"><?php esc_html_e('Add Plugin Shortcodes', 'wpcloudplugins'); ?>         </div>
            <div>
              <?php wp_roles_and_users_input('lets_box_settings[permissions_add_shortcodes]', $this->settings['permissions_add_shortcodes']); ?>
            </div>

            <div class="letsbox-accordion-title letsbox-option-title"><?php esc_html_e('Add Direct links', 'wpcloudplugins'); ?>        </div>
            <div>
              <?php wp_roles_and_users_input('lets_box_settings[permissions_add_links]', $this->settings['permissions_add_links']); ?>
            </div>

            <div class="letsbox-accordion-title letsbox-option-title"><?php esc_html_e('Embed Documents', 'wpcloudplugins'); ?>        </div>
            <div>
              <?php wp_roles_and_users_input('lets_box_settings[permissions_add_embedded]', $this->settings['permissions_add_embedded']); ?>
            </div>

          </div>
        </div>
        <!-- End Permissions Tab -->

        <!--  Statistics Tab -->
        <div id="settings_stats"  class="letsbox-tab-panel">
          <div class="letsbox-tab-panel-header"><?php esc_html_e('Statistics', 'wpcloudplugins'); ?></div>

          <div class="letsbox-option-title"><?php esc_html_e('Log Events', 'wpcloudplugins'); ?>
            <div class="letsbox-onoffswitch">
              <input type='hidden' value='No' name='lets_box_settings[log_events]'/>
              <input type="checkbox" name="lets_box_settings[log_events]" id="log_events" class="letsbox-onoffswitch-checkbox" <?php echo ('Yes' === $this->settings['log_events']) ? 'checked="checked"' : ''; ?> data-div-toggle="events_options"/>
              <label class="letsbox-onoffswitch-label" for="log_events"></label>
            </div>
          </div>
          <div class="letsbox-option-description"><?php esc_html_e('Register all plugin events', 'wpcloudplugins'); ?>.</div>

          <div class="letsbox-suboptions events_options <?php echo ('Yes' === $this->settings['log_events']) ? '' : 'hidden'; ?> ">
            <div class="letsbox-option-title"><?php esc_html_e('Summary Email', 'wpcloudplugins'); ?>
              <div class="letsbox-onoffswitch">
                <input type='hidden' value='No' name='lets_box_settings[event_summary]'/>
                <input type="checkbox" name="lets_box_settings[event_summary]" id="event_summary" class="letsbox-onoffswitch-checkbox" <?php echo ('Yes' === $this->settings['event_summary']) ? 'checked="checked"' : ''; ?> data-div-toggle="event_summary"/>
                <label class="letsbox-onoffswitch-label" for="event_summary"></label>
              </div>
            </div>
            <div class="letsbox-option-description"><?php esc_html_e('Email a summary of all the events that are logged with the plugin', 'wpcloudplugins'); ?>.</div>

            <div class="event_summary <?php echo ('Yes' === $this->settings['event_summary']) ? '' : 'hidden'; ?> ">

              <div class="letsbox-option-title"><?php esc_html_e('Interval', 'wpcloudplugins'); ?></div>
              <div class="letsbox-option-description"><?php esc_html_e('Please select the interval the summary needs to be send', 'wpcloudplugins'); ?>.</div>
              <select type="text" name="lets_box_settings[event_summary_period]" id="event_summary_period">
                <option value="daily"  <?php echo 'daily' === $this->settings['event_summary_period'] ? "selected='selected'" : ''; ?>><?php esc_html_e('Every day', 'wpcloudplugins'); ?></option>
                <option value="weekly"  <?php echo 'weekly' === $this->settings['event_summary_period'] ? "selected='selected'" : ''; ?>><?php esc_html_e('Weekly', 'wpcloudplugins'); ?></option>
                <option value="monthly"  <?php echo 'monthly' === $this->settings['event_summary_period'] ? "selected='selected'" : ''; ?>><?php esc_html_e('Monthly', 'wpcloudplugins'); ?></option>
              </select>   

              <div class="letsbox-option-title"><?php esc_html_e('Recipients', 'wpcloudplugins'); ?></div>
              <div class="letsbox-option-description"><?php esc_html_e('Send the summary to the following email address(es)', 'wpcloudplugins'); ?>:</div>
              <input class="letsbox-option-input-large" type="text" name="lets_box_settings[event_summary_recipients]" id="event_summary_recipients" value="<?php echo esc_attr($this->settings['event_summary_recipients']); ?>" placeholder="<?php echo get_option('admin_email'); ?>">  
            </div>
          </div>

          <div class="letsbox-option-title"><?php esc_html_e('Google Analytics', 'wpcloudplugins'); ?>
            <div class="letsbox-onoffswitch">
              <input type='hidden' value='No' name='lets_box_settings[google_analytics]'/>
              <input type="checkbox" name="lets_box_settings[google_analytics]" id="google_analytics" class="letsbox-onoffswitch-checkbox" <?php echo ('Yes' === $this->settings['google_analytics']) ? 'checked="checked"' : ''; ?> />
              <label class="letsbox-onoffswitch-label" for="google_analytics"></label>
            </div>
          </div>
          <div class="letsbox-option-description"><?php esc_html_e('Would you like to see some statistics in Google Analytics?', 'wpcloudplugins'); ?>. <?php echo sprintf(esc_html__('If you enable this feature, please make sure you already added your %s Google Analytics web tracking %s code to your site.', 'wpcloudplugins'), "<a href='https://support.google.com/analytics/answer/1008080' target='_blank'>", '</a>'); ?>.</div>
        </div>
        <!-- End Statistics Tab -->

        <!-- System info Tab -->
        <div id="settings_system"  class="letsbox-tab-panel">
          <div class="letsbox-tab-panel-header"><?php esc_html_e('System information', 'wpcloudplugins'); ?></div>
          <?php echo $this->get_system_information(); ?>
        </div>
        <!-- End System info -->

                <!-- Tools Tab -->
        <div id="settings_tools"  class="letsbox-tab-panel">
          <div class="letsbox-tab-panel-header"><?php esc_html_e('Tools', 'wpcloudplugins'); ?></div>

          <div class="letsbox-option-title"><?php esc_html_e('Cache', 'wpcloudplugins'); ?></div>
          <?php echo $this->get_plugin_reset_cache_box(); ?>

          <div class="letsbox-option-title"><?php esc_html_e('Reset to Factory Settings', 'wpcloudplugins'); ?></div>
          <?php echo $this->get_plugin_reset_plugin_box(); ?>

        </div>  
        <!-- End Tools -->

        <!-- Help Tab -->
        <div id="settings_help"  class="letsbox-tab-panel">
          <div class="letsbox-tab-panel-header"><?php esc_html_e('Support', 'wpcloudplugins'); ?></div>

          <div id="message">
            <p><?php esc_html_e('Check the documentation of the plugin in case you encounter any problems or are looking for support.', 'wpcloudplugins'); ?></p>
            <div id='wpcp-open-docs-button' type='button' class='simple-button blue'><?php esc_html_e('Open Documentation', 'wpcloudplugins'); ?></div>
            <br/><br/>
            <div style='padding:56.25% 0 0 0;position:relative;'><iframe src='https://vimeo.com/showcase/9015621/embed' allowfullscreen loading="lazy" frameborder='0' style='position:absolute;top:0;left:0;width:100%;height:100%;'></iframe></div>            
          </div>
        </div>  
      </div>
      <!-- End Help info -->
    </div>
  </form>
</div>