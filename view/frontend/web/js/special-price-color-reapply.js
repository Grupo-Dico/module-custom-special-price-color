define([
    'jquery',
    'domReady!'
], function ($) {
    'use strict';

    var markerSelector = '[data-lc-special-price-color]';
    var priceBoxSelector = '.price-box' + markerSelector;
    var specialPriceSelector = '.special-price' + markerSelector;
    var observerAttribute = 'data-lc-special-price-color-observed';
    var scheduled = false;

    function isPdp() {
        return $('body').hasClass('catalog-product-view');
    }

    function getColor(element) {
        var color = $(element).attr('data-lc-special-price-color');

        if (/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/.test(color || '')) {
            return color;
        }

        return null;
    }

    function applyColorToPrice(priceElement, color) {
        if (priceElement && priceElement.style && color) {
            priceElement.style.setProperty('color', color, 'important');
        }
    }

    function applyFromSpecialPrice(specialPrice) {
        var color = getColor(specialPrice);

        if (!color) {
            return;
        }

        $(specialPrice).find('.price').each(function () {
            applyColorToPrice(this, color);
        });
    }

    function applyFromPriceBox(priceBox) {
        var color = getColor(priceBox);

        if (!color) {
            return;
        }

        $(priceBox).find('.special-price .price').each(function () {
            applyColorToPrice(this, color);
        });
    }

    function observePriceBox(priceBox) {
        var observer;

        if (!window.MutationObserver || priceBox.getAttribute(observerAttribute)) {
            return;
        }

        observer = new window.MutationObserver(function () {
            applyFromPriceBox(priceBox);
        });

        observer.observe(priceBox, {
            childList: true,
            subtree: true
        });

        priceBox.setAttribute(observerAttribute, '1');
    }

    function applyAll() {
        if (!isPdp()) {
            return;
        }

        $(specialPriceSelector).each(function () {
            applyFromSpecialPrice(this);
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
