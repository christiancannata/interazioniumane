jQuery(document).ready(function ($) {
    'use strict';
    $.widget('cp.LetsBoxWC', {
        options: {},

        _create: function () {
            /* Ignite! */
            this._initiate();
        },

        _destroy: function () {
            return this._super();
        },

        _setOption: function (key, value) {
            this._super(key, value);
        },

        _initiate: function () {
            var self = this;
            self._initButtons();
            self._initDetails();
        },

        _initButtons: function () {
            var self = this;

            $('.wpcp-letsbox .wpcp-wc-open-box').on('click', function (e) {
                self.openUploadBox($(this));
            });
        },

        _initDetails: function () {
            var self = this;

            $('.wpcp-letsbox.wpcp-upload-container').each(function (e) {
                var item_id = $(this).data('item-id');
                var listtoken = $(this).find('.LetsBox').data('token');
                self._loadDetails(item_id, listtoken);
            });
        },

        _loadDetails: function (item_id, listtoken) {
            var self = this;

            $.ajax({
                type: 'POST',
                url: self.options.ajax_url,
                data: {
                    action: 'letsbox-get-filelist',
                    type: 'wc-item-details',
                    item_id: item_id,
                    listtoken: listtoken,
                    _ajax_nonce: self.options.refresh_nonce
                },
                success: function (response) {
                    if ($.isPlainObject(response) === false || response.length === 0) {
                        return;
                    }

                    var $upload_list = $('#wpcp-letsbox-uploads-' + item_id + ' .wpcp-uploads-list');
                    $upload_list.html('');
                    $.each(response, function (id, value) {
                        if (value === '') {
                            return;
                        }
                        $upload_list.append('<li>' + value + '</li>');
                    });

                    $upload_list.fadeIn();
                },
                dataType: 'json'
            });
        },

        openUploadBox: function (button) {
            var self = this;

            var container = button.next('.woocommerce-order-upload-box');
            var item_id = button.parent('[data-item-id]').data('item-id');
            var listtoken = container.find('[data-token]').data('token');

            /* Close any open modal windows */
            $('#letsbox-modal-action').remove();

            /* Build the Upload Dialog */
            var modalheader = $(
                '<a tabindex="0" class="close-button" title="' +
                    this.options.str_close_title +
                    '" onclick="modal_action.close();"><i class="eva eva-close eva-lg" aria-hidden="true"></i></a></div>'
            );
            var modalbody = $('<div class="letsbox-modal-body" tabindex="0"></div>');
            var modaldialog = $(
                '<div id="letsbox-modal-action" class="LetsBox letsbox-modal wpcp-woocommerce-upload-container ' +
                    this.options.content_skin +
                    '"><div class="modal-dialog"><div class="modal-content"></div></div></div>'
            );

            $('body').append(modaldialog);
            $('#letsbox-modal-action .modal-content').append(modalheader, modalbody);

            /* Fill Textarea */
            $('.letsbox-modal-body').append(container);
            container.show();

            /* Set the button actions */
            $('#letsbox-modal-action .letsbox-modal-confirm-btn').on('click', function (e) {
                modal_action.close();
            });

            /* Open the dialog */
            var modal_action = new RModal(document.getElementById('letsbox-modal-action'), {
                bodyClass: 'rmodal-open',
                dialogOpenClass: 'animated slideInDown',
                dialogCloseClass: 'animated slideOutUp',
                escapeClose: true,
                afterClose() {
                    container.hide();
                    button.after(container);
                    self._loadDetails(item_id, listtoken);
                }
            });

            document.addEventListener(
                'keydown',
                function (ev) {
                    modal_action.keydown(ev);
                },
                false
            );
            modal_action.open();
            window.modal_action = modal_action;
            return false;
        }
    });
});

// Initiate the Module!
jQuery(document).ready(function ($) {
    $(document).LetsBoxWC(LetsBox_vars);
});
