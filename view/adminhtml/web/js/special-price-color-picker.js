define([
    'jquery',
    'mage/translate',
    'domReady!'
], function ($) {
    'use strict';

    var selectors = [
        'input[data-lc-special-price-color-picker="1"]'
    ];
    var enhancedClass = 'lc-special-price-color-picker-enhanced';
    var pickerClass = 'lc-special-price-color-picker';
    var wrapperClass = 'lc-special-price-color-picker-control';
    var swatchClass = 'lc-special-price-color-picker-swatch';
    var previewClass = 'lc-special-price-color-picker-preview';
    var observer = null;
    var enhanceTimer = null;

    function trim(value) {
        return String(value || '').replace(/^\s+|\s+$/g, '');
    }

    function normalizeHex(value) {
        var color = trim(value).toUpperCase();
        var match;

        if (/^#[0-9A-F]{6}$/.test(color)) {
            return color;
        }

        match = color.match(/^#([0-9A-F])([0-9A-F])([0-9A-F])$/);
        if (match) {
            return '#' + match[1] + match[1] + match[2] + match[2] + match[3] + match[3];
        }

        return null;
    }

    function isTargetInput(input) {
        var $input = $(input);
        var type = String($input.attr('type') || 'text').toLowerCase();

        if ($input.hasClass(enhancedClass)) {
            return false;
        }

        if (type !== 'text') {
            return false;
        }

        if (!$input.closest('body').length) {
            return false;
        }

        return true;
    }

    function syncDisabledState($input, $picker) {
        $picker.prop('disabled', $input.prop('disabled'));
    }

    function getExistingPicker($input) {
        return $input.siblings('.' + swatchClass).find('input.' + pickerClass).first();
    }

    function getExistingSwatches($input) {
        return $input.siblings('.' + swatchClass);
    }

    function ensureWrapper($input) {
        if (!$input.parent().hasClass(wrapperClass)) {
            $input.wrap($('<span />', {
                class: wrapperClass,
                'data-lc-special-price-color-picker-control': '1'
            }));
        }
    }

    function createPicker() {
        var $swatch = $('<span />', {
            class: swatchClass
        });
        var $preview = $('<span />', {
            class: previewClass
        });
        var $picker = $('<input />', {
            type: 'color',
            class: pickerClass,
            title: $.mage.__('Pick special price color'),
            'aria-label': $.mage.__('Pick special price color')
        });

        $swatch.append($preview).append($picker);

        return $swatch;
    }

    function getReadableTextColor(color) {
        var normalized = normalizeHex(color);
        var red;
        var green;
        var blue;
        var luminance;

        if (!normalized) {
            return '';
        }

        red = parseInt(normalized.substr(1, 2), 16);
        green = parseInt(normalized.substr(3, 2), 16);
        blue = parseInt(normalized.substr(5, 2), 16);
        luminance = ((red * 299) + (green * 587) + (blue * 114)) / 1000;

        return luminance >= 140 ? '#303030' : '#FFFFFF';
    }

    function updateVisualState($input, $picker) {
        var color = normalizeHex($input.val());
        var $preview = $input.siblings('.' + swatchClass).find('.' + previewClass).first();

        if (color) {
            $picker.val(color);
            $preview.css('background-color', color);
            $input.css({
                'background-color': color,
                color: getReadableTextColor(color)
            });
            return;
        }

        $preview.css('background-color', '');
        $input.css({
            'background-color': '',
            color: ''
        });
    }

    function enhanceInput(input) {
        var $input = $(input);
        var $existingPicker;
        var $picker;
        var $swatches;

        if ($input.hasClass(enhancedClass)) {
            $existingPicker = getExistingPicker($input);
            if ($existingPicker.length) {
                syncDisabledState($input, $existingPicker);
                updateVisualState($input, $existingPicker);
            }
            return;
        }

        if (!isTargetInput(input)) {
            return;
        }

        ensureWrapper($input);
        $swatches = getExistingSwatches($input);

        if ($swatches.length > 1) {
            $swatches.slice(1).remove();
        }

        $picker = getExistingPicker($input);

        if (!$picker.length) {
            $input.after(createPicker());
            $picker = getExistingPicker($input);
        }

        syncDisabledState($input, $picker);
        updateVisualState($input, $picker);
        $input.addClass(enhancedClass);

        $picker.on('input change', function () {
            $input.val(String($picker.val()).toUpperCase()).trigger('change');
        });

        $input.on('input change', function () {
            updateVisualState($input, $picker);
            syncDisabledState($input, $picker);
        });
    }

    function enhanceAll() {
        $(selectors.join(',')).each(function () {
            enhanceInput(this);
        });
    }

    function scheduleEnhance() {
        if (enhanceTimer) {
            clearTimeout(enhanceTimer);
        }

        enhanceTimer = setTimeout(enhanceAll, 100);
    }

    enhanceAll();
    setTimeout(enhanceAll, 500);
    setTimeout(enhanceAll, 1500);

    if (window.MutationObserver && document.body) {
        observer = new window.MutationObserver(scheduleEnhance);
        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['disabled']
        });
    }
});
