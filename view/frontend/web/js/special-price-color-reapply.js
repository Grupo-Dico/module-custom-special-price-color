define([
    'jquery',
    'domReady!'
], function ($) {
    'use strict';

    var priceColorAttr = 'data-lc-special-price-color';
    var labelColorAttr = 'data-lc-special-price-label-color';
    var bgColorAttr = 'data-lc-special-price-bg';
    var observedAttr = 'data-lc-color-observed';
    var priceBoxSelector = '.price-box[' + priceColorAttr + '],'
        + '.price-box[' + labelColorAttr + '],'
        + '.price-box[' + bgColorAttr + ']';
    var specialPriceSelector = '.special-price[' + priceColorAttr + '],'
        + '.special-price[' + labelColorAttr + '],'
        + '.special-price[' + bgColorAttr + ']';
    var thirdPriceSelector = '.lc-third-price[' + priceColorAttr + '],'
        + '.lc-third-price[' + labelColorAttr + '],'
        + '.lc-third-price[' + bgColorAttr + ']';
    var hexPattern = /^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/;
    var scheduled = false;

    function isPdp() {
        return $('body').hasClass('catalog-product-view');
    }

    function isValidHex(color) {
        return typeof color === 'string' && hexPattern.test(color);
    }

    function getHex(element, attrName) {
        var color = $(element).attr(attrName);

        return isValidHex(color) ? color : null;
    }

    function applyColor(element, color) {
        if (element && element.style && color) {
            element.style.setProperty('color', color, 'important');
        }
    }

    function applyBackground(element, color) {
        if (element && element.style && color) {
            element.style.setProperty('background-color', color, 'important');
        }
    }

    function applyToSpecialPrice(specialPrice) {
        var labelColor = getHex(specialPrice, labelColorAttr);
        var priceColor = getHex(specialPrice, priceColorAttr);
        var bgColor = getHex(specialPrice, bgColorAttr);
        var $specialPrice = $(specialPrice);

        if (labelColor) {
            $specialPrice.find('.price-label').each(function () {
                applyColor(this, labelColor);
            });
        }

        if (priceColor) {
            $specialPrice.find('.price').each(function () {
                applyColor(this, priceColor);
            });
        }

        if (bgColor) {
            applyBackground(specialPrice, bgColor);
        }
    }

    function applyToThirdPrice(thirdPrice) {
        var labelColor = getHex(thirdPrice, labelColorAttr);
        var priceColor = getHex(thirdPrice, priceColorAttr);
        var bgColor = getHex(thirdPrice, bgColorAttr);
        var $thirdPrice = $(thirdPrice);

        if (labelColor) {
            $thirdPrice.find('.lc-third-price__label').each(function () {
                applyColor(this, labelColor);
            });
        }

        if (priceColor) {
            $thirdPrice.find('.lc-third-price__amount').each(function () {
                applyColor(this, priceColor);
            });
        }

        if (bgColor) {
            applyBackground(thirdPrice, bgColor);
        }
    }

    function applyFromPriceBox(priceBox) {
        var labelColor = getHex(priceBox, labelColorAttr);
        var priceColor = getHex(priceBox, priceColorAttr);
        var bgColor = getHex(priceBox, bgColorAttr);
        var $priceBox = $(priceBox);

        if (labelColor) {
            $priceBox.find('.special-price .price-label').each(function () {
                applyColor(this, labelColor);
            });
        }

        if (priceColor) {
            $priceBox.find('.special-price .price').each(function () {
                applyColor(this, priceColor);
            });
        }

        if (bgColor) {
            $priceBox.find('.special-price').each(function () {
                applyBackground(this, bgColor);
            });
        }
    }

    function observePriceBox(priceBox) {
        var observer;

        if (!window.MutationObserver || priceBox.getAttribute(observedAttr)) {
            return;
        }

        observer = new window.MutationObserver(function () {
            applyFromPriceBox(priceBox);
            $(priceBox).find(thirdPriceSelector).each(function () {
                applyToThirdPrice(this);
            });
        });

        observer.observe(priceBox, { childList: true, subtree: true });
        priceBox.setAttribute(observedAttr, '1');
    }

    function applyAll() {
        if (!isPdp()) {
            return;
        }

        $(specialPriceSelector).each(function () {
            applyToSpecialPrice(this);
        });

        $(thirdPriceSelector).each(function () {
            applyToThirdPrice(this);
        });

        $(priceBoxSelector).each(function () {
            applyFromPriceBox(this);
            observePriceBox(this);
        });
    }

    function scheduleApply() {
        if (scheduled) {
            return;
        }

        scheduled = true;
        setTimeout(function () {
            scheduled = false;
            applyAll();
        }, 0);
    }

    if (!isPdp()) {
        return;
    }

    applyAll();
    $(window).on('load', applyAll);
    $(document).on('reloadPrice updatePrice', '[data-role="priceBox"]', function () {
        scheduleApply();
        setTimeout(applyAll, 50);
    });
});
