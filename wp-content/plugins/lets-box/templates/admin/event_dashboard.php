<div id="LetsBox" class="LetsBoxDashboard">
  <div class="letsbox admin-settings">

    <div class="wrap">
      <div class="letsbox-header">
                       <div class="letsbox-logo"><a href="https://www.wpcloudplugins.com" target="_blank"><img src="<?php echo LETSBOX_ROOTPATH; ?>/css/images/wpcp-logo-dark.svg" height="64" width="64"/></a></div>
        <div class="letsbox-form-buttons"> <div id="clear_statistics" class="simple-button default clear_statistics" name="clear_statistics"><?php esc_html_e('Clear all Statistics','wpcloudplugins'); ?>&nbsp;<div class='wpcp-spinner'></div></div></div>
        <div class="letsbox-title"><?php esc_html_e('Reports','wpcloudplugins'); ?></div>
      </div>

      <div class="letsbox-panel">
        <div id="letsbox-totals">
          <div class="letsbox-box letsbox-box25">
            <div class="letsbox-box-inner ">
              <div class="letsbox-option-title nopadding">
                <div class="letsbox-counter-text"><?php echo esc_html__('Total Previews', 'wpcloudplugins'); ?> </div>
                <div class="letsbox-counter" data-type="letsbox_previewed_entry">
                  <span>
                    <div class="loading"><div class='loader-beat'></div></div>
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div class="letsbox-box letsbox-box25">
            <div class="letsbox-box-inner">
              <div class="letsbox-option-title nopadding">
                <div class="letsbox-counter-text"><?php echo esc_html__('Total Downloads', 'wpcloudplugins'); ?></div>
                <div class="letsbox-counter" data-type="letsbox_downloaded_entry">
                  <span>
                    <div class="loading"><div class='loader-beat'></div></div>
                  </span>
                </div></div>
            </div>
          </div>

          <div class="letsbox-box letsbox-box25">
            <div class="letsbox-box-inner">
              <div class="letsbox-option-title nopadding">
                <div class="letsbox-counter-text"><?php echo esc_html__('Items Shared', 'wpcloudplugins'); ?></div>
                <div class="letsbox-counter" data-type="letsbox_created_link_to_entry">
                  <span>
                    <div class="loading"><div class='loader-beat'></div></div>
                  </span>
                </div></div>
            </div>
          </div>

          <div class="letsbox-box letsbox-box25">
            <div class="letsbox-box-inner">
              <div class="letsbox-option-title nopadding">
                <div class="letsbox-counter-text"><?php echo esc_html__('Documents Uploaded', 'wpcloudplugins'); ?></div>
                <div class="letsbox-counter" data-type="letsbox_uploaded_entry">
                  <span>
                    <div class="loading"><div class='loader-beat'></div></div>
                  </span>
                </div></div>
            </div>
          </div>
        </div>

        <div class="letsbox-box">
          <div class="letsbox-box-inner">
            <div class="letsbox-event-date-selector">
              <label for="chart_datepicker_from"><?php echo esc_html__('From', 'wpcloudplugins'); ?></label>
              <input type="text" class="chart_datepicker_from" name="chart_datepicker_from">
              <label for="chart_datepicker_to"><?php echo esc_html__('to', 'wpcloudplugins'); ?></label>
              <input type="text" class="chart_datepicker_to" name="chart_datepicker_to">
            </div>
            <div class="letsbox-option-title"><?php echo esc_html__('Events per Day', 'wpcloudplugins'); ?></div>
            <div class="letsbox-events-chart-container" style="height:500px !important; position:relative;">
              <div class="loading"><div class='loader-beat'></div></div>
              <canvas id="letsbox-events-chart"></canvas>
            </div>
          </div>
        </div>

        <div class="letsbox-box letsbox-box50">
          <div class="letsbox-box-inner">
            <div class="letsbox-option-title"><?php echo esc_html__('Top 25 Downloads', 'wpcloudplugins'); ?></div>
            <table id="top-downloads" class="stripe hover order-column" style="width:100%">
              <thead>
                <tr>
                  <th></th>
                  <th><?php echo esc_html__('Document', 'wpcloudplugins'); ?></th>
                  <th><?php echo esc_html__('Total', 'wpcloudplugins'); ?></th>
                </tr>
              </thead>
            </table>
          </div>
        </div>

        <div class="letsbox-box letsbox-box50">
          <div class="letsbox-box-inner">
            <div class="letsbox-option-title"><?php echo esc_html__('Top 25 Users with most Downloads', 'wpcloudplugins'); ?></div>
            <table id="top-users" class="display" style="width:100%">
              <thead>
                <tr>
                  <th></th>
                  <th><?php echo esc_html__('User', 'wpcloudplugins'); ?></th>
                  <th><?php echo esc_html__('Username'); ?></th>
                  <th><?php echo esc_html__('Downloads', 'wpcloudplugins'); ?></th>
                </tr>
              </thead>
            </table>
          </div>
        </div>

        <div class="letsbox-box letsbox-box50">
          <div class="letsbox-box-inner">
            <div class="letsbox-option-title"><?php echo esc_html__('Latest 25 Uploads', 'wpcloudplugins'); ?></div>
            <table id="latest-uploads" class="stripe hover order-column" style="width:100%">
              <thead>
                <tr>
                  <th></th>
                  <th><?php echo esc_html__('Document', 'wpcloudplugins'); ?></th>
                  <th><?php echo esc_html__('Date', 'wpcloudplugins'); ?></th>
                </tr>
              </thead>
            </table>
          </div>
        </div>

        <div class="letsbox-box letsbox-box50">
          <div class="letsbox-box-inner">
            <div class="letsbox-option-title"><?php echo esc_html__('Top 25 Users with most Uploads', 'wpcloudplugins'); ?></div>
            <table id="top-uploads" class="display" style="width:100%">
              <thead>
                <tr>
                  <th></th>
                  <th><?php echo esc_html__('User', 'wpcloudplugins'); ?></th>
                  <th><?php echo esc_html__('Username'); ?></th>
                  <th><?php echo esc_html__('Uploads', 'wpcloudplugins'); ?></th>
                </tr>
              </thead>
            </table>
          </div>
        </div>

        <div class="letsbox-box">
          <div class="letsbox-box-inner">
            <div class="letsbox-event-date-selector">
              <label for="chart_datepicker_from"><?php echo esc_html__('From', 'wpcloudplugins'); ?></label>
              <input type="text" class="chart_datepicker_from" name="chart_datepicker_from">
              <label for="chart_datepicker_to"><?php echo esc_html__('to', 'wpcloudplugins'); ?></label>
              <input type="text" class="chart_datepicker_to" name="chart_datepicker_to">
            </div>
            <div class="letsbox-option-title"><?php echo esc_html__('All Events', 'wpcloudplugins'); ?></div>
            <table id="full-log" class="display" style="width:100%">
              <thead>
                <tr>
                  <th></th>
                  <th class="all"><?php echo esc_html__('Description', 'wpcloudplugins'); ?></th>
                  <th><?php echo esc_html__('Date', 'wpcloudplugins'); ?></th>
                  <th><?php echo esc_html__('Event', 'wpcloudplugins'); ?></th>
                  <th><?php echo esc_html__('User', 'wpcloudplugins'); ?></th>
                  <th><?php echo esc_html__('Name', 'wpcloudplugins'); ?></th>
                  <th><?php echo esc_html__('Location', 'wpcloudplugins'); ?></th>
                  <th><?php echo esc_html__('Page', 'wpcloudplugins'); ?></th>
                  <th><?php echo esc_html__('Extra', 'wpcloudplugins'); ?></th>
                </tr>
              </thead>
            </table>
          </div>
        </div>

        <div class="event-details-template" style="display:none;">
          <div class="event-details-name"></div>

          <div class="letsbox-box letsbox-box25">
            <div class="letsbox-box-inner">
              <div class="event-details-user-template" style="display:none;">
                <div class="event-details-entry-img"></div>
                <a target="_blank" class="event-visit-profile event-button simple-button blue"><i class="eva eva-external-link"></i>&nbsp;<?php echo esc_html__('Visit Profile'); ?></a>

                <div class="loading"><div class="loader-beat"></div></div>
              </div>

              <div class="event-details-entry-template" style="display:none;">
                <div class="event-details-entry-img"></div>
                <p class="event-details-description"></p>
                <a target="_blank" class="event-download-entry event-button simple-button blue" download><i class="eva eva-download eva-lg"></i>&nbsp;<?php echo esc_html__('Download'); ?></a>

                <div class="loading"><div class="loader-beat"></div></div>
              </div>

              <br/>

              <div class="event-details-totals-template">
                <div class="letsbox-option-title tbpadding10 ">
                  <div class="letsbox-counter-text"><?php echo esc_html__('Previews', 'wpcloudplugins'); ?> </div>
                  <div class="letsbox-counter" data-type="letsbox_previewed_entry">
                    <span>
                      <div class="loading"><div class='loader-beat'></div></div>
                    </span>
                  </div>
                </div>

                <div class="letsbox-option-title tbpadding10">
                  <div class="letsbox-counter-text"><?php echo esc_html__('Downloads', 'wpcloudplugins'); ?></div>
                  <div class="letsbox-counter" data-type="letsbox_downloaded_entry">
                    <span>
                      <div class="loading"><div class='loader-beat'></div></div>
                    </span>
                  </div>
                </div>

                <div class="letsbox-option-title tbpadding10">
                  <div class="letsbox-counter-text"><?php echo esc_html__('Shared', 'wpcloudplugins'); ?></div>
                  <div class="letsbox-counter" data-type="letsbox_created_link_to_entry">
                    <span>
                      <div class="loading"><div class='loader-beat'></div></div>
                    </span>
                  </div>
                </div>

                <div class="letsbox-option-title tbpadding10">
                  <div class="letsbox-counter-text"><?php echo esc_html__('Uploads', 'wpcloudplugins'); ?></div>
                  <div class="letsbox-counter" data-type="letsbox_uploaded_entry">
                    <span>
                      <div class="loading"><div class='loader-beat'></div></div>
                    </span>
                  </div>
                </div>
              </div>

            </div>
          </div>

          <div class="letsbox-box letsbox-box75 event-details-table-template">
            <div class="letsbox-box-inner">
              <div class="letsbox-option-title"><?php echo esc_html__('Logged Events', 'wpcloudplugins'); ?></div>
              <table id="full-detail-log" class="display" style="width:100%">
                <thead>
                  <tr>
                    <th></th>
                    <th class="all"><?php echo esc_html__('Description', 'wpcloudplugins'); ?></th>
                    <th><?php echo esc_html__('Date', 'wpcloudplugins'); ?></th>
                    <th><?php echo esc_html__('Event', 'wpcloudplugins'); ?></th>
                    <th><?php echo esc_html__('User', 'wpcloudplugins'); ?></th>
                    <th><?php echo esc_html__('Name', 'wpcloudplugins'); ?></th>
                    <th><?php echo esc_html__('Location', 'wpcloudplugins'); ?></th>
                    <th><?php echo esc_html__('Page', 'wpcloudplugins'); ?></th>
                    <th><?php echo esc_html__('Extra', 'wpcloudplugins'); ?></th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
