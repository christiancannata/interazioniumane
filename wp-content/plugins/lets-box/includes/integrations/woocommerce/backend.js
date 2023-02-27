jQuery(function ($) {
  var letsbox_wc = {
    // hold a reference to the last selected Box button
    lastSelectedButton: false,

    init: function () {
      // add button for simple product
      this.addButtons();
      this.addButtonEventHandler();
      // add buttons when variable product added
      $('#variable_product_options').on('woocommerce_variations_added', function () {
        letsbox_wc.addButtons();
        letsbox_wc.addButtonEventHandler();
      });
      // add buttons when variable products loaded
      $('#woocommerce-product-data').on('woocommerce_variations_loaded', function () {
        letsbox_wc.addButtons();
        letsbox_wc.addButtonEventHandler();
      });

      return this;
    },

    addButtons: function () {
      var self = this;

      var button = $('<a href="#TB_inline?height=100%&amp;width=800&amp;inlineId=lb-embedded" class="button insert-box thickbox">' + letsbox_woocommerce_translation.choose_from + '</a>');
      $('.downloadable_files').each(function (index) {

        // we want our button to appear next to the insert button
        var insertButton = $(this).find('a.button.insert');
        // check if button already exists on element, bail if so
        if ($(this).find('a.button.insert-box').length > 0) {
          return;
        }

        // finally clone the button to the right place
        insertButton.after(button.clone());

      });

      /* START Support for WooCommerce Product Documents */

      $('.wc-product-documents .button.wc-product-documents-set-file').each(function (index) {

        // check if button already exists on element, bail if so
        if ($(this).parent().find('a.button.insert-box').length > 0) {
          return;
        }

        // finally clone the button to the right place
        $(this).after(button.clone());

      });


      $('#wc-product-documents-data').on('click', '.wc-product-documents-add-document', function () {
        self.addButtons();
      });
      /* END Support for WooCommerce Product Documents */

    },
    /**
     * Adds the click event to the  buttons
     * and opens the Box chooser
     */
    addButtonEventHandler: function () {
      $('#woocommerce-product-data').on('click', 'a.button.insert-box', function (e) {
        window.addEventListener("message", letsbox_wc.afterFileSelected)
        e.preventDefault();

        // save a reference to clicked button
        letsbox_wc.lastSelectedButton = $(this);

      });
    },
    /**
     * Handle selected files
     */
    afterFileSelected: function (event) {

      window.removeEventListener("message", letsbox_wc.afterFileSelected);

      if (event.origin !== window.location.origin) {
        return;
      }

      if (typeof event.data !== 'object' || event.data === null || typeof event.data.action === 'undefined') {
        return;
      }

      if (event.data.action !== 'wpcp-select-entry') {
        return;
      }

      if (event.data.slug !== 'letsbox') {
        return;
      }


      if ($(letsbox_wc.lastSelectedButton).closest('.downloadable_files').length > 0) {

        var table = $(letsbox_wc.lastSelectedButton).closest('.downloadable_files').find('tbody');
        var template = $(letsbox_wc.lastSelectedButton).parent().find('.button.insert:first').data("row");
        var fileRow = $(template);

        fileRow.find('.file_name > input:first').val(event.data.entry_name).change();
        fileRow.find('.file_url > input:first').val('https://box.com/' + event.data.entry_name + letsbox_woocommerce_translation.download_url + event.data.entry_id);
        table.append(fileRow);

        // trigger change event so we can save variation
        $(table).find('input').last().change();

      }

      /* START Support for WooCommerce Product Documents */
      if ($(letsbox_wc.lastSelectedButton).closest('.wc-product-document').length > 0) {

        var row = $(letsbox_wc.lastSelectedButton).closest('.wc-product-document');

        row.find('.wc-product-document-label input:first').val(event.data.entry_name).change();
        row.find('.wc-product-document-file-location input:first').val(letsbox_woocommerce_translation.wcpd_url + event.data.entry_id);
      }
      /* END Support for WooCommerce Product Documents */
    }

  };
  window.letsbox_wc = letsbox_wc.init();

  /* Callback function to add shortcode to WC field */
  if (typeof window.wpcp_letsbox_wc_add_content === 'undefined') {
    window.wpcp_letsbox_wc_add_content = function (data) {
      $('#letsbox_upload_box_shortcode').val(data);
      tb_remove();
    }
  }

  $('input#_uploadable').on('change', function () {
    var is_uploadable = $('input#_uploadable:checked').length;
    $('.show_if_uploadable').hide();
    $('.hide_if_uploadable').hide();
    if (is_uploadable) {
      $('.hide_if_uploadable').hide();
    }
    if (is_uploadable) {
      $('.show_if_uploadable').show();
    }
  });
  $('input#_uploadable').trigger('change');

  $('input#letsbox_upload_box').on('change', function () {
    var letsbox_upload_box = $('input#letsbox_upload_box:checked').length;
    $('.show_if_letsbox_upload_box').hide();
    if (letsbox_upload_box) {
      $('.show_if_letsbox_upload_box').show();
    }
  });
  $('input#letsbox_upload_box').trigger('change');

  /* Shortcode Generator Popup */
  $('.LetsBox-shortcodegenerator').on('click', function (e) {
    var shortcode = $("#letsbox_upload_box_shortcode").val();
    shortcode = shortcode.replace('[letsbox ', '').replace('"]', '');
    var query = encodeURIComponent(shortcode).split('%3D%22').join('=').split('%22%20').join('&');
    tb_show("Build Shortcode for Product", ajaxurl + '?action=letsbox-getpopup&' + query + '&type=shortcodebuilder&for=woocommerce&asuploadbox=1&callback=wpcp_letsbox_wc_add_content&TB_iframe=true&height=600&width=800');
  });
});