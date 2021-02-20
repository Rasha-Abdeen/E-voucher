jQuery(document).ready(function ($) {

    var table = $('.woocommerce_order_items');

    function getDropdownOptions(index, line_item_id, availableValues, value) {
        var html = '<option value="">' + serial_numbers_edit_order_settings.please_choose_a_serial_number + '</option>';
        var emptyValueSelectedHtml = 'selected';
        $.each(availableValues, function (index, serialNumber) {
            html += '<option value="' + serialNumber + '" ';
            if (serialNumber == value) {
                html += 'selected ';
                emptyValueSelectedHtml = '';
            }
            html += '>' + serialNumber + '</option>';
        });
        if (value !== null && value !== '') {
            html += '<option ' + emptyValueSelectedHtml + ' value="' + value + '">' + value + '</option>';
        }
        return html;
    }

    function getDropdown(index, line_item_id, availableValues, value) {
        if (availableValues === null) {
            availableValues = serial_numbers_edit_order_settings.serial_numbers_available[line_item_id];
        }
        var html = '<select class="serial-number-dropdown" name="serial_numbers[' + line_item_id + ']['
                + index + ']">';
        html += getDropdownOptions(index, line_item_id, availableValues, value);
        html += '</select>';
        return html;
    }

    var scannedSerialNumberWrapper = null;

    table.after('<input type="hidden" name="saving_serial_numbers" value="yes" />');
    $.each(serial_numbers_edit_order_settings.order_serial_numbers, function (line_item_id, line_item_serial_numbers) {
        if (line_item_serial_numbers.length == 0 && serial_numbers_edit_order_settings.serial_numbers_available[line_item_id].length == 0) {
            return;
        }
        if (line_item_serial_numbers.length == 0) {
            var maxLength = 0;
            $.each(serial_numbers_edit_order_settings.serial_numbers_available[line_item_id], function (index, value) {
                if (value.length > maxLength) {
                    maxLength = value.length;
                }
            });
            if (maxLength == 0) {
                return;
            }
        }
        var row = table.find('.order_item_id[value="' + line_item_id + '"]').closest('tr');
        var serialNumberHtml = '<h4>' + serial_numbers_edit_order_settings.serial_numbers_for + ' ' + row.find('.wc-order-item-name').text() + '</h4>';
        var input_index = 1;
        $.each(line_item_serial_numbers, function (index, line_item_serial_number) {
            serialNumberHtml += getDropdown(index, line_item_id, null, line_item_serial_number) + '<br />';
            input_index++;
        });
        var quantity = parseInt(row.find('input.quantity').val());
        while (input_index <= quantity) {
            serialNumberHtml += getDropdown(input_index, line_item_id, null, null) + '<br />';
            input_index++;
        }
        var numberOfColumns = row.find('td').length;
        row.after('<tr class="serial-numbers-for-' + line_item_id + '"><td colspan="' + numberOfColumns + '"><input type="hidden" class="line-item-id" value="' + line_item_id + '" />' + serialNumberHtml + '</td></tr>');
    });


    $(function () {
        $.widget("custom.combobox", {
            _create: function () {
                this.wrapper = $("<span>")
                        .addClass("custom-combobox")
                        .insertAfter(this.element);

                this.element.hide();
                this._createAutocomplete();
                this._createShowAllButton();
            },
            _createAutocomplete: function () {
                var selected = this.element.children(":selected"),
                        value = selected.val() ? selected.text() : "";

                this.input = $("<input>")
                        .appendTo(this.wrapper)
                        .val(value)
                        .attr("title", "")
                        .addClass("custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left")
                        .autocomplete({
                            delay: 0,
                            minLength: 0,
                            source: $.proxy(this, "_source")
                        })
                        .tooltip({
                            classes: {
                                "ui-tooltip": "ui-state-highlight"
                            }
                        });

                this._on(this.input, {
                    autocompleteselect: function (event, ui) {
                        ui.item.option.selected = true;
                        this._trigger("select", event, {
                            item: ui.item.option
                        });
                        this.element.trigger('change');
                    },
                    autocompletechange: "_removeIfInvalid"
                });
            },
            _createShowAllButton: function () {
                var input = this.input,
                        wasOpen = false;

                $("<a>")
                        .attr("tabIndex", -1)
                        .attr("title", "Show All Items")
                        .tooltip()
                        .appendTo(this.wrapper)
                        .button({
                            icons: {
                                primary: "ui-icon-triangle-1-s"
                            },
                            text: false
                        })
                        .removeClass("ui-corner-all")
                        .addClass("custom-combobox-toggle ui-corner-right")
                        .on("mousedown", function () {
                            wasOpen = input.autocomplete("widget").is(":visible");
                        })
                        .on("click", function () {
                            input.trigger("focus");

                            // Close if already visible
                            if (wasOpen) {
                                return;
                            }

                            // Pass empty string as value to search for, displaying all results
                            input.autocomplete("search", "");
                        });
            },
            _source: function (request, response) {
                var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), "i");
                response(this.element.children("option").map(function () {
                    var text = $(this).text();
                    if (this.value && (!request.term || matcher.test(text)))
                        return {
                            label: text,
                            value: text,
                            option: this
                        };
                }));
            },
            _removeIfInvalid: function (event, ui) {

                // Selected an item, nothing to do
                if (ui.item) {
                    return;
                }

                // Search for a match (case-insensitive)
                var value = this.input.val(),
                        valueLowerCase = value.toLowerCase(),
                        valid = false;
                this.element.children("option").each(function () {
                    if ($(this).text().toLowerCase() === valueLowerCase) {
                        this.selected = valid = true;
                        return false;
                    }
                });

                // Found a match, nothing to do
                if (valid) {
                    return;
                }

                // Remove invalid value
                this.input
                        .val("")
                        .attr("title", value + " didn't match any item")
                        .tooltip("open");
                this.element.val("");
                this._delay(function () {
                    this.input.tooltip("close").attr("title", "");
                }, 2500);
                this.input.autocomplete("instance").term = "";
            },
            _destroy: function () {
                this.wrapper.remove();
                this.element.show();
            }
        });
        $('.serial-number-dropdown').combobox();
        $('.custom-combobox').each(function () {
            var outerHeight = $(this).find('.custom-combobox-input').outerHeight();
            $(this).find('.custom-combobox-toggle').outerHeight(outerHeight);
        });
        $('.custom-combobox-input').attr('placeholder', serial_numbers_edit_order_settings.please_choose_a_serial_number);


        $(document).on('change', '.serial-number-dropdown', function () {
            var line_item_id = $(this).closest('td').find('.line-item-id').val();
            var availableValues = serial_numbers_edit_order_settings.serial_numbers_available[line_item_id];
            var notAvailableValues = {};
            table.find('.serial-numbers-for-' + line_item_id).each(function () {
                $(this).find('.serial-number-dropdown').each(function () {
                    var value = $(this).val();
                    if (value !== '') {
                        notAvailableValues[value] = value;
                    }
                });
            });
            var currentlyAvailableItems = {};
            $.each(availableValues, function (index, value) {
                if (typeof notAvailableValues[value] == 'undefined') {
                    currentlyAvailableItems[index] = value;
                }
            });
            table.find('.serial-numbers-for-' + line_item_id).each(function () {
                var index = 1;
                $(this).find('.serial-number-dropdown').each(function () {
                    var value = $(this).val();
                    $(this).html(getDropdownOptions(index, line_item_id, currentlyAvailableItems, value));
                    //$(this).combobox('refresh');
                    index++;
                });
            });
        });

        $('.serial-number-dropdown').change();
    });

    var serialNumberDropdownSelectedForScan;
    var dialog;
    var dialogButtons = {};
    dialogButtons[serial_numbers_edit_order_settings.cancel] = function () {
        dialog.dialog("close");
    };
    dialogButtons[serial_numbers_edit_order_settings.ok] = function () {
        dialog.dialog("close");
        serialNumberDropdownSelectedForScan.val($('#scanned-barcode').val());
        serialNumberDropdownSelectedForScan.change();
        serialNumberDropdownSelectedForScan.closest('.serial-number-dropdown-wrapper').find('.custom-combobox-input').val(serialNumberDropdownSelectedForScan.val());
        $('#scanned-barcode').val('');
    };
    dialog = $("#scan-bar-code-dialog").dialog({
        autoOpen: false,
        modal: true,
        open: function () {
            $('#scanned-barcode').focus();
        },
        buttons: dialogButtons
    });

    $('.serial-number-dropdown').each(function () {
        $(this).wrap('<div class="serial-number-dropdown-wrapper"></div>');
        $(this).closest('.serial-number-dropdown-wrapper').append(' ' + serial_numbers_edit_order_settings.or + ' <a class="button scan-serial-number" href="#">' + serial_numbers_edit_order_settings.select_with_barcode + '</a>');
    });

    $(document).on('click', '.scan-serial-number', function (e) {
        serialNumberDropdownSelectedForScan = $(this).closest('.serial-number-dropdown-wrapper').find('.serial-number-dropdown');
        dialog.dialog({position: {my: "center", at: "center", of: $(this)}});
        dialog.dialog("open");
        e.preventDefault();
    });


});