<?php
function help_button($name, $text)
{
    ?>
  <a onclick="return false;" onkeypress="return false;" class="wpcp_help_tooltip" data-tippy-content="<strong><?php echo $name; ?></strong></br><?php echo $text; ?>">
			<i class="eva eva-question-mark-circle-outline"></i>
		</a>
  <?php
}

$network_wide_authorization = $this->get_processor()->is_network_authorized();
?>

<div class="letsbox admin-settings">
  <form id="letsbox-options" method="post" action="<?php echo network_admin_url('edit.php?action='.$this->plugin_network_options_key); ?>">
    <?php settings_fields('lets_box_settings'); ?>
    <input type="hidden" name="lets_box_settings[box_account_type]" id="box_account_type" value="<?php echo @esc_attr($this->settings['box_account_type']); ?>" >

    <div class="wrap">
      <div class="letsbox-header">
                <div class="letsbox-logo"><a href="https://www.wpcloudplugins.com" target="_blank"><img src="<?php echo LETSBOX_ROOTPATH; ?>/css/images/wpcp-logo-dark.svg" height="64" width="64"/></a></div>
        <div class="letsbox-form-buttons" style="<?php echo (false === is_plugin_active_for_network(LETSBOX_SLUG)) ? 'display:none;' : ''; ?>"> <div id="wpcp-save-settings-button" class="simple-button default"><?php esc_html_e('Save Settings', 'wpcloudplugins'); ?>&nbsp;<div class='wpcp-spinner'></div></div></div>
        <div class="letsbox-title"><?php esc_html_e('Settings', 'wpcloudplugins'); ?></div>
      </div>

      <div id="" class="letsbox-panel letsbox-panel-left">      
                <div class="letsbox-nav-header"><?php esc_html_e('Settings', 'wpcloudplugins'); ?> <a href="<?php echo admin_url('update-core.php'); ?>">(Ver: <?php echo LETSBOX_VERSION; ?>)</a></div>

        <ul class="letsbox-nav-tabs">
          <li id="settings_general_tab" data-tab="settings_general" class="current"><a ><?php esc_html_e('General', 'wpcloudplugins'); ?></a></li>
          <?php if ($network_wide_authorization) { ?>
              <li id="settings_advanced_tab" data-tab="settings_advanced" ><a ><?php esc_html_e('Advanced', 'wpcloudplugins'); ?></a></li>
          <?php } ?>
          <li id="settings_system_tab" data-tab="settings_system" ><a><?php esc_html_e('System information', 'wpcloudplugins'); ?></a></li>
          <li id="settings_help_tab" data-tab="settings_help" ><a><?php esc_html_e('Support', 'wpcloudplugins'); ?></a></li>
        </ul>

        <div class="letsbox-nav-header" style="margin-top: 50px;"><?php esc_html_e('Other Cloud Plugins', 'wpcloudplugins'); ?></div>
        <ul class="letsbox-nav-tabs">
          <li id="settings_help_tab" data-tab="settings_help"><a href="https://1.envato.market/L6yXj" target="_blank" style="color:#522058;">Google Drive <i class="eva eva-external-link" aria-hidden="true"></i></a></li>
          <li id="settings_help_tab" data-tab="settings_help"><a href="https://1.envato.market/vLjyO" target="_blank" style="color:#522058;">Dropbox <i class="eva eva-external-link" aria-hidden="true"></i></a></li>
          <li id="settings_help_tab" data-tab="settings_help"><a href="https://1.envato.market/yDbyv" target="_blank" style="color:#522058;">OneDrive <i class="eva eva-external-link" aria-hidden="true"></i></a></li>
        </ul> 

                            <div class="letsbox-nav-footer">
          <a href="https://www.wpcloudplugins.com/" target="_blank">
            <img alt="" height="auto" src="<?php echo LETSBOX_ROOTPATH; ?>/css/images/wpcloudplugins-logo-dark.png">
          </a>
        </div>
      </div>

      <div class="letsbox-panel letsbox-panel-right">

        <!-- General Tab -->
        <div id="settings_general" class="letsbox-tab-panel current">

          <div class="letsbox-tab-panel-header"><?php esc_html_e('General', 'wpcloudplugins'); ?></div>

          <div class="letsbox-option-title"><?php esc_html_e('Plugin License', 'wpcloudplugins'); ?></div>
          <?php
          echo $this->get_plugin_activated_box();
          ?>

          <?php if (is_plugin_active_for_network(LETSBOX_SLUG)) { ?>
              <div class="letsbox-option-title"><?php esc_html_e('Network Wide Authorization', 'wpcloudplugins'); ?>
                <div class="letsbox-onoffswitch">
                  <input type='hidden' value='No' name='lets_box_settings[network_wide]'/>
                  <input type="checkbox" name="lets_box_settings[network_wide]" id="wpcp-network_wide-button" class="letsbox-onoffswitch-checkbox" <?php echo (empty($network_wide_authorization)) ? '' : 'checked="checked"'; ?> data-div-toggle="network_wide"/>
                  <label class="letsbox-onoffswitch-label" for="wpcp-network_wide-button"></label>
                </div>
              </div>


              <?php
              if ($network_wide_authorization) {
                  echo $this->get_plugin_authorization_box();
              }
              ?>

              <?php
          }
          ?>

        </div>
        <!-- End General Tab -->


        <!--  Advanced Tab -->
        <?php if ($network_wide_authorization) { ?>
            <div id="settings_advanced"  class="letsbox-tab-panel">
              <div class="letsbox-tab-panel-header"><?php esc_html_e('Advanced', 'wpcloudplugins'); ?></div>

              <div class="letsbox-option-title"><?php esc_html_e('"Lost Authorization" notification', 'wpcloudplugins'); ?></div>
              <div class="letsbox-option-description"><?php esc_html_e('If the plugin somehow loses its authorization, a notification email will be send to the following email address', 'wpcloudplugins'); ?>:</div>
              <input class="letsbox-option-input-large" type="text" name="lets_box_settings[lostauthorization_notification]" id="lostauthorization_notification" value="<?php echo esc_attr($this->settings['lostauthorization_notification']); ?>">  

              <div class="letsbox-option-title"><?php esc_html_e('Own App', 'wpcloudplugins'); ?>
                <div class="letsbox-onoffswitch">
                  <input type='hidden' value='No' name='lets_box_settings[box_app_own]'/>
                  <input type="checkbox" name="lets_box_settings[box_app_own]" id="box_app_own" class="letsbox-onoffswitch-checkbox" <?php echo (empty($this->settings['box_app_client_id']) || empty($this->settings['box_app_client_secret'])) ? '' : 'checked="checked"'; ?> data-div-toggle="own-app"/>
                  <label class="letsbox-onoffswitch-label" for="box_app_own"></label>
                </div>
              </div>

              <div class="letsbox-suboptions own-app <?php echo (empty($this->settings['box_app_client_id']) || empty($this->settings['box_app_client_secret'])) ? 'hidden' : ''; ?> ">
                <div class="letsbox-option-description">
                  <strong>Using your own Box App is <u>optional</u></strong>. For an easy setup you can just use the default App of the plugin itself by leaving the ID and Secret empty. The advantage of using your own app is limited. If you decided to create your own Box App anyway, please enter your settings. In the <a href="https://florisdeleeuwnl.zendesk.com/hc/en-us/articles/205059105-How-do-I-create-my-own-Box-App-" target="_blank">documentation</a> you can find how you can create a Box App.
                  <br/><br/>
                  <div class="wpcp-warning">
                    <i><strong>NOTICE</strong>: If you encounter any issues when trying to use your own App, please fall back on the default App by disabling this setting.</i>
                  </div>
                </div>

                <div class="letsbox-option-title">Box Client ID</div>
                <div class="letsbox-option-description"><?php echo esc_html__('Only if you want to use your own App, insert your Client ID here', 'wpcloudplugins'); ?>.</div>
                <input class="letsbox-option-input-large" type="text" name="lets_box_settings[box_app_client_id]" id="box_app_client_id" value="<?php echo esc_attr($this->settings['box_app_client_id']); ?>" placeholder="<--- <?php esc_html_e('Leave empty for easy setup', 'wpcloudplugins'); ?> --->" >

                <div class="letsbox-option-title">Box Client secret</div>
                <div class="letsbox-option-description"><?php echo esc_html__('If you want to use your own App, insert your Client Secret here', 'wpcloudplugins'); ?>.</div>
                <input class="letsbox-option-input-large" type="text" name="lets_box_settings[box_app_client_secret]" id="box_app_client_secret" value="<?php echo esc_attr($this->settings['box_app_client_secret']); ?>" placeholder="<--- <?php esc_html_e('Leave empty for easy setup', 'wpcloudplugins'); ?> --->" >   

                <div class="letsbox-option-title">OAuth 2.0 Redirect URI</div>
                <div class="letsbox-option-description"><?php echo esc_html__('Set the redirect URI in your application to the following', 'wpcloudplugins'); ?>:</div>
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
                  <div class="letsbox-option-title"><?php esc_html_e('Business Accounts', 'wpcloudplugins'); ?> | <?php esc_html_e('Scope shared-links', 'wpcloudplugins'); ?></div>
                  <div class="letsbox-option-description"><?php echo wp_kses(__('Who should be able to access the links that are created by the plugin? If set to <strong>Public</strong> the links will be accessible by anyone. <strong>Within Organization</strong> will make links accessible within your organization only. Anonymous links may be disabled by the tenant administrator', 'wpcloudplugins'), ['strong' => []]); ?>.</div>
                  <select type="text" name="lets_box_settings[link_scope]" id="link_scope">
                    <option value="open" <?php echo 'open' === $this->settings['link_scope'] ? "selected='selected'" : ''; ?>>Public</option>
                    <option value="company" <?php echo 'company' === $this->settings['link_scope'] ? "selected='selected'" : ''; ?>>Within Organization</option>
                  </select>
                  <?php
              }
              ?>
            </div>
        <?php } ?>
        <!-- End Advanced Tab -->

        <!-- System info Tab -->
        <div id="settings_system"  class="letsbox-tab-panel">
          <div class="letsbox-tab-panel-header"><?php esc_html_e('System information', 'wpcloudplugins'); ?></div>
          <?php echo $this->get_system_information(); ?>
        </div>
        <!-- End System info -->

        <!-- Help Tab -->
        <div id="settings_help"  class="letsbox-tab-panel">
          <div class="letsbox-tab-panel-header"><?php esc_html_e('Support', 'wpcloudplugins'); ?></div>

          <div class="letsbox-option-title"><?php esc_html_e('Support & Documentation', 'wpcloudplugins'); ?></div>
          <div id="message">
            <p><?php esc_html_e('Check the documentation of the plugin in case you encounter any problems or are looking for support.', 'wpcloudplugins'); ?></p>
            <div id='wpcp-open-docs-button' type='button' class='simple-button blue'><?php esc_html_e('Open Documentation', 'wpcloudplugins'); ?></div>
            <br/><br/>
            <div style='padding:56.25% 0 0 0;position:relative;'><iframe src='https://vimeo.com/showcase/9015621/embed' allowfullscreen loading="lazy" frameborder='0' style='position:absolute;top:0;left:0;width:100%;height:100%;'></iframe></div>            
          </div>
          <br/>
          <div class="letsbox-option-title"><?php esc_html_e('Cache', 'wpcloudplugins'); ?></div>
          <?php echo $this->get_plugin_reset_cache_box(); ?>

        </div>  
      </div>
      <!-- End Help info -->
    </div>
  </form>
</div>