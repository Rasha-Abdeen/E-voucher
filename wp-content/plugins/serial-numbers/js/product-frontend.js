jQuery(document).ready(function ($) {

    var availableSerialNumbers = serial_numbers_product_frontend_settings.available_serial_numbers;
    var previousVariation = null;

    function createDropdown(id, availableSerialNumbers) {
        var html = '<select name="serial_numbers[' + id + ']" class="serial-numbers-dropdown" id="serial-numbers-dropdown-' + id + '">';
        $.each(availableSerialNumbers, function (index, value) {
            html += '<option value="' + value + '">' + value + '</option>';
        });
        html += '</select>';
        return html;
    }

    function updateDropdowns() {
        if (availableSerialNumbers != null && typeof availableSerialNumbers == 'undefined' || availableSerialNumbers.length <= 1) {
            $('.serial-numbers-dropdown').remove();
            return;
        }
        var numberOfSerialNumbers = parseInt($('.qty').val());
        if (typeof numberOfSerialNumbers == 'undefined') {
            numberOfSerialNumbers = 1;
        }
        var numberOfDropdowns = $('.serial-numbers-dropdown').length;
        for (var i = numberOfDropdowns + 1; i <= numberOfSerialNumbers; i++) {
            $('#serial-numbers').append(createDropdown(i, availableSerialNumbers));
        }
        for (var i = numberOfSerialNumbers + 1; i <= numberOfDropdowns; i++) {
            $('#serial-numbers-dropdown-' + i).hide();
            setTimeout(function () {
                $('#serial-numbers-dropdown-' + i).remove();
            }, 500);
        }
    }

    $('form.cart').prepend('<div id="serial-numbers"></div>');
    if (serial_numbers_product_frontend_settings.is_variable == 'yes') {
        updateDropdowns();
    }

    $('.qty').change(function () {
        updateDropdowns();
    });
    $('.qty').change();

    $('form').on('found_variation', function (evt, variation) {
        if (previousVariation == variation.variation_id) {
            return;
        }
        $('.serial-numbers-dropdown').remove();
        previousVariation = variation.variation_id;
        availableSerialNumbers = serial_numbers_product_frontend_settings.available_serial_numbers_by_variation[variation.variation_id];
        updateDropdowns();
    });


});