jQuery(function ($) {
    var letsbox_wc = {
        // hold a reference to the last selected Box button
        lastSelectedButton: false,
        module: $('#wpcp-modal-selector-box .wpcp-module'),

        init: function () {
            // place wpcp container bottom body
            $('#wpcp-modal-selector-box').parent().appendTo('body');

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

            // Select the already added files in the File Browser module
            this.initSelectAdded();
            this.initAddButton();

            return this;
        },

        addButtons: function () {
            var self = this;

            var button = $('<a class="button wpcp-insert-box-content">' + letsbox_woocommerce_translation.choose_from + '</a>');
            $('.downloadable_files').each(function (index) {
                // we want our button to appear next to the insert button
                var insertButton = $(this).find('a.button.insert');
                // check if button already exists on element, bail if so
                if ($(this).find('a.button.wpcp-insert-box-content').length > 0) {
                    return;
                }

                // finally clone the button to the right place
                insertButton.after(button.clone());
            });

            /* START Support for WooCommerce Product Documents */

            $('.wc-product-documents .button.wc-product-documents-set-file').each(function (index) {
                // check if button already exists on element, bail if so
                if ($(this).parent().find('a.button.wpcp-insert-box-content').length > 0) {
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
            var self = this;

            $('#woocommerce-product-data').on('click', 'a.button.wpcp-insert-box-content', function (e) {
                self.openSelector();
                e.preventDefault();

                // save a reference to clicked button
                letsbox_wc.lastSelectedButton = $(this);
            });

            $('#wpcp-modal-selector-box .wpcp-dialog-close').on('click', function (e) {
                self.closeSelector();
            });

            $('#wpcp-modal-selector-box .wpcp-wc-dialog-entry-select').on('click', function (e) {
                const account_id = self.module.attr('data-account-id');
                const entries_data = self.module
                    .find("input[name='selected-files[]']:checked")
                    .map(function () {
                        const $entry = $('.entry[data-id="' + $(this).val() + '"]');

                        return {
                            entry_id: $entry.attr('data-id'),
                            entry_name: $entry.attr('data-name'),
                            account_id: account_id
                        };
                    })
                    .get();

                if (entries_data.length === 0) {
                    return self.closeSelector();
                }

                // Send the data via postMessage
                window.top.postMessage(
                    {
                        slug: 'letsbox',
                        action: 'wpcp-select-entries',
                        entries: entries_data
                    },
                    window.location.origin
                );

                setTimeout(function () {
                    self.closeSelector();
                }, 100);
            });
        },

        openSelector: function () {
            var self = this;

            window.addEventListener('message', letsbox_wc.afterFileSelected);

            // Refresh File List to render the selected items
            if (self.module.hasClass('wpcp-thumb-view') || self.module.hasClass('wpcp-list-view')) {
                self.module.data('cp-LetsBox')._getFileList({});
            }

            $('#wpcp-modal-selector-box').show();
            $('#wpcp-modal-selector-box .wpcp-wc-dialog-entry-select').prop('disabled', 'disabled');
        },

        closeSelector: function () {
            window.removeEventListener('message', letsbox_wc.afterFileSelected);
            $('#wpcp-modal-selector-box').fadeOut();
        },

        /**
         * Mark already added file in the File Browser moulde
         */
        initSelectAdded: function () {
            const self = this;

            self.module.on('content-loaded', function (e, plugin) {
                plugin.element.find("input[name='selected-files[]']:checked").prop('checked', false).removeClass('is-selected');

                const added_files = $('.downloadable_files .file_url > input')
                    .filter(function (index) {
                        return $(this).val().includes('https://box.com/');
                    })
                    .toArray();

                added_files.forEach(function (input, index, array) {
                    const url = new URL($(input).val());
                    const entry_id = url.searchParams.get('id');
                    const account_id = url.searchParams.get('account_id');

                    // Show the entry as selected
                    $('.wpcp-module[data-account-id="' + account_id + '"] .entry[data-id="' + entry_id + '"]').addClass('is-selected');
                });
            });
        },

        /**
         * Enable & Disable add button based on selection of entries
         */
        initAddButton: function () {
            var self = this;
            $(self.module).on(
                {
                    change: function (e) {
                        if (self.module.find("input[name='selected-files[]']:checked").length) {
                            $('#wpcp-modal-selector-box .wpcp-wc-dialog-entry-select').prop('disabled', '');
                        } else {
                            $('#wpcp-modal-selector-box .wpcp-wc-dialog-entry-select').prop('disabled', 'disabled');
                        }
                    }
                },
                "input[name='selected-files[]']"
            );
        },

        /**
         * Handle selected files
         */
        afterFileSelected: function (event) {
            if (event.origin !== window.location.origin) {
                return;
            }

            if (typeof event.data !== 'object' || event.data === null || typeof event.data.action === 'undefined') {
                return;
            }

            if (event.data.action !== 'wpcp-select-entries') {
                return;
            }

            if (event.data.slug !== 'letsbox') {
                return;
            }

            let files_added = [];
            let files_failed = [];

            event.data.entries.forEach(function (entry, index, array) {
                // Make sure only a single instance of the file can be added
                if (
                    $('.downloadable_files .file_url > input').filter(function (index) {
                        return $(this)
                            .val()
                            .includes(entry.entry_id + '&account_id=' + entry.account_id);
                    }).length
                ) {
                    files_failed.push(entry.entry_name);
                    return false;
                }

                if ($(letsbox_wc.lastSelectedButton).closest('.downloadable_files').length > 0) {
                    var table = $(letsbox_wc.lastSelectedButton).closest('.downloadable_files').find('tbody');
                    var template = $(letsbox_wc.lastSelectedButton).parent().find('.button.insert:first').data('row');
                    var fileRow = $(template);

                    fileRow.find('.file_name > input:first').val(entry.entry_name).change();
                    fileRow
                        .find('.file_url > input:first')
                        .val(
                            'https://box.com/' +
                                encodeURIComponent(entry.entry_name) +
                                letsbox_woocommerce_translation.download_url +
                                entry.entry_id +
                                '&account_id=' +
                                entry.account_id
                        );
                    table.append(fileRow);

                    // trigger change event so we can save variation
                    $(table).find('input').last().change();
                }

                /* START Support for WooCommerce Product Documents */
                if ($(letsbox_wc.lastSelectedButton).closest('.wc-product-document').length > 0) {
                    var row = $(letsbox_wc.lastSelectedButton).closest('.wc-product-document');

                    row.find('.wc-product-document-label input:first').val(entry.entry_name).change();
                    row.find('.wc-product-document-file-location input:first').val(
                        letsbox_woocommerce_translation.wcpd_url + entry.entry_id + '&account_id=' + entry.account_id
                    );
                }
                /* END Support for WooCommerce Product Documents */

                // Show the entry as selected
                $('.wpcp-module[data-account-id="' + entry.account_id + '"] .entry[data-id="' + entry.entry_id + '"]').addClass(
                    'is-selected'
                );

                files_added.push(entry.entry_name);
            });

            if (files_failed.length) {
                $('p.wpcp-notification-failed').html(
                    letsbox_woocommerce_translation.notification_failed_file_msg.replace(
                        '{filename}',
                        '<strong>' + files_failed.join(', ') + '</strong>'
                    )
                );
                window.showNotification(false);
            }

            if (files_added.length) {
                $('p.wpcp-notification-success').html(
                    letsbox_woocommerce_translation.notification_success_file_msg.replace(
                        '{filename}',
                        '<strong>' + files_added.join(', ') + '</strong>'
                    )
                );
                window.showNotification(true);
            }
        }
    };
    window.letsbox_wc = letsbox_wc.init();

    /* Callback function to add shortcode to WC field */
    if (typeof window.wpcp_letsbox_wc_add_content === 'undefined') {
        window.wpcp_letsbox_wc_add_content = function (data) {
            $('#letsbox_upload_box_shortcode').val(data);
            tb_remove();
        };
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
        var shortcode = $('#letsbox_upload_box_shortcode').val();
        shortcode = shortcode.replace('[letsbox ', '').replace('"]', '');
        var query = encodeURIComponent(shortcode).split('%3D%22').join('=').split('%22%20').join('&');
        tb_show(
            'Build Shortcode for Product',
            ajaxurl +
                '?action=letsbox-getpopup&' +
                query +
                '&type=shortcodebuilder&for=woocommerce&asuploadbox=1&callback=wpcp_letsbox_wc_add_content&TB_iframe=true&height=600&width=1024'
        );
    });
});
