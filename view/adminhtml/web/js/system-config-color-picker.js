define([
    'jquery',
    'mage/translate',
    'spectrum',
    'tinycolor',
    'Magento_Ui/js/form/element/color-picker-palette'
], function ($, $t, spectrum, tinycolor, palette) {
    'use strict';

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

    function toHexValue(value) {
        var color;

        if (!value) {
            return null;
        }

        color = tinycolor(value);

        if (!color.isValid()) {
            return null;
        }

        return color.toHexString().toUpperCase();
    }

    return function (config, element) {
        var $element = $(element);
        var realInputId = String($element.attr('data-real-input-id') || '');
        var spectrumInputId = String($element.attr('data-spectrum-input-id') || '');
        var $realInput = $('#' + realInputId);
        var $spectrumInput = $('#' + spectrumInputId);

        if (!$realInput.length || !$spectrumInput.length) {
            return;
        }

        if ($element.data('lc-special-price-color-system-initialized')) {
            return;
        }

        $element.data('lc-special-price-color-system-initialized', true);

        function setPickerFromInput() {
            var color = normalizeHex($realInput.val());

            $spectrumInput.spectrum('set', color || '');
        }

        function syncColor(value) {
            var color = toHexValue(value);

            if (!color) {
                return;
            }

            $spectrumInput.val(color);
            $realInput.val(color);
            $realInput.attr('value', color);
            $realInput.css('background-color', color);
            $realInput.trigger('input');
            $realInput.trigger('change');
        }

        function syncFromSpectrumInput() {
            var color = normalizeHex($spectrumInput.val());

            if (!color) {
                return;
            }

            $realInput.val(color);
            $realInput.attr('value', color);
            $realInput.css('background-color', color);
            $realInput.trigger('input');
            $realInput.trigger('change');
        }

        function openPicker() {
            setPickerFromInput();
            window.setTimeout(function () {
                $spectrumInput.spectrum('show');
            }, 0);
        }

        $spectrumInput.spectrum({
            chooseText: $t('Apply'),
            cancelText: $t('Cancel'),
            maxSelectionSize: 8,
            clickoutFiresChange: true,
            allowEmpty: true,
            localStorageKey: 'magento.spectrum',
            palette: palette,
            showInput: true,
            showInitial: false,
            showPalette: true,
            showAlpha: false,
            showSelectionPalette: true,
            preferredFormat: 'hex',
            beforeShow: function () {
                setPickerFromInput();

                return true;
            },
            move: syncColor,
            change: syncColor,
            show: function () {
                setPickerFromInput();

                return true;
            }
        });

        setPickerFromInput();

        $spectrumInput.on('change input', syncFromSpectrumInput);
        $realInput.on('input change', setPickerFromInput);
        $realInput.on('click touchstart', function (event) {
            event.stopPropagation();
        });
        $realInput.on('focus click', function () {
            openPicker();
        });

        if ($realInput.prop('disabled')) {
            $spectrumInput.spectrum('disable');
        }
    };
});
