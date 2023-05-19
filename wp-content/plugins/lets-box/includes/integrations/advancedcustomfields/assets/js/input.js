(function ($) {
    var data = {};
    var $input = null;
    var $table;
    var $selector;
    var $module;

    function initialize_field($field) {
        $input = $field.find('input[data-name="id"]');
        $table = $field.find('.wpcp-acf-items-table');
        $selector = $field.find('#wpcp-modal-acf-selector-box');
        $module = $selector.find('.wpcp-module');

        // place wpcp container bottom body
        $selector.parent().appendTo('body');

        read_data();

        _init_buttons($field);

        _initSelectAdded();
    }

    function read_data() {
        try {
            data = JSON.parse($input.val());
        } catch (e) {
            data = {};
        }
        render_entries();
    }

    function save_data() {
        $input.val(JSON.stringify(data));
        render_entries();
    }

    function _init_buttons($field) {
        $($field).on('click', '.wpcp-acf-add-item', function (e) {
            openSelector();
            e.preventDefault();
        });

        $($field).on('click', '.wpcp-acf-remove-item', function (e) {
            var row = $(this).parents('tr');
            delete data[row.data('entry-id')];
            save_data();
        });

        $('.wpcp-dialog-close').on('click', function (e) {
            closeSelector();
        });

        initAddButton();

        $selector.find('.wpcp-acf-dialog-entry-select').on('click', function (e) {
            const account_id = $module.attr('data-account-id');
            const entries_data = $module
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
                return closeSelector();
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
                closeSelector();
            }, 100);
        });
    }

    function openSelector() {
        window.addEventListener('message', callback_handler);

        // Refresh File List to render the selected items
        if ($module.hasClass('wpcp-thumb-view') || $module.hasClass('wpcp-list-view')) {
            $module.data('cp-LetsBox')._getFileList({});
        }

        $selector.fadeIn();
        $selector.find('.wpcp-acf-dialog-entry-select').prop('disabled', 'disabled');
    }

    function closeSelector() {
        window.removeEventListener('message', callback_handler);
        $selector.fadeOut();
    }

    /**
     * Enable & Disable add button based on selection of entries
     */
    function initAddButton() {
        $module.on(
            {
                change: function (e) {
                    if ($module.find("input[name='selected-files[]']:checked").length) {
                        $selector.find('.wpcp-acf-dialog-entry-select').prop('disabled', '');
                    } else {
                        $selector.find('.wpcp-acf-dialog-entry-select').prop('disabled', 'disabled');
                    }
                }
            },
            "input[name='selected-files[]']"
        );
    }

    /**
     * Mark already added file in the File Browser moulde
     */
    function _initSelectAdded() {
        $module.on('content-loaded', function (e, plugin) {
            plugin.element.find("input[name='selected-files[]']:checked").prop('checked', false).removeClass('is-selected');

            for (const [key, entry] of Object.entries(data)) {
                // Show the entry as selected
                $('.wpcp-module[data-account-id="' + entry.account_id + '"] .entry[data-id="' + entry.entry_id + '"]').addClass(
                    'is-selected'
                );
            }
        });
    }

    function render_entries() {
        var $tbody = $table.find('tbody');
        $tbody.empty();

        if (Object.entries(data).length === 0) {
            $tbody.append('<tr><td></td><td>No files added</td><td></td><td></td></tr>');
            return;
        }

        for (const [key, entry] of Object.entries(data)) {
            $tbody.append(
                '<tr data-entry-id="' +
                    key +
                    '" data-account-id="' +
                    entry.account_id +
                    '"><td>' +
                    (entry.icon_url ? '<img src="' + entry.icon_url + '" style="height:18px; width:18px;"/>' : '') +
                    '</td><td>' +
                    entry.name +
                    (entry.size ? ' (' + entry.size + ')' : '') +
                    '</td><td style="max-width:300px;overflow:hidden;white-space:nowrap;text-overflow: ellipsis;">' +
                    entry.entry_id +
                    '</td><td>' +
                    (entry.direct_url
                        ? '<a href="' + entry.direct_url + '" target="_blank" class="button button-secondary button-small">View</a>&nbsp;'
                        : '') +
                    (entry.download_url
                        ? '<a href="' +
                          entry.download_url +
                          '" target="_blank" class="button button-secondary button-small">Download</a>&nbsp;'
                        : '') +
                    '<a href="#" class="wpcp-acf-remove-item button button-secondary button-small">&#10006;</a></td></tr>'
            );
        }
    }

    function callback_handler(event) {
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

        event.data.entries.forEach(function (entry, index, array) {
            data[entry.entry_id] = {
                account_id: entry.account_id,
                entry_id: entry.entry_id,
                name: entry.entry_name,
                size: '',
                direct_url: '',
                download_url: '',
                shortlived_download_url: '',
                shared_url: '',
                embed_url: '',
                thumbnail_url: '',
                icon_url: ''
            };

            // Show the entry as selected
            $('.wpcp-module[data-account-id="' + entry.account_id + '"] .entry[data-id="' + entry.entry_id + '"]').addClass('is-selected');

            files_added.push(entry.entry_name);
        });

        save_data();

        $('p.wpcp-notification-success').html('<strong>' + files_added.join(', ') + '</strong>');
        $('p.wpcp-notification-failed').html('<strong>' + files_added.join(', ') + '</strong>');

        window.showNotification(true);
    }

    if (typeof acf.add_action !== 'undefined') {
        acf.add_action('ready_field/type=LetsBox_Field', initialize_field);
        acf.add_action('append_field/type=LetsBox_Field', initialize_field);
    } else {
        $(document).on('acf/setup_fields', function (e, postbox) {
            // find all relevant fields
            $(postbox)
                .find('.field[data-field_type="LetsBox_Field"]')
                .each(function () {
                    // initialize
                    initialize_field($(this));
                });
        });
    }
})(jQuery);
