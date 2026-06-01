<?php

declare(strict_types=1);

namespace LeanCommerce\CustomSpecialPriceColor\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ColorPicker extends Field
{
    /**
     * Render the system config text input with Magento UI color-picker-like markup.
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $htmlId = (string)$element->getHtmlId();
        $spectrumId = $htmlId . '_spectrum';
        $wrapperId = $htmlId . '_color_picker';
        $element->addClass('colorpicker-input lc-special-price-color-system-input');

        $config = [
            '#' . $wrapperId => [
                'LeanCommerce_CustomSpecialPriceColor/js/system-config-color-picker' => [
                    'inputId' => $htmlId,
                    'spectrumId' => $spectrumId,
                ],
            ],
        ];

        return '<div id="' . $this->escapeHtmlAttr($wrapperId) . '"'
            . ' class="lc-special-price-color-system-picker"'
            . ' data-real-input-id="' . $this->escapeHtmlAttr($htmlId) . '"'
            . ' data-spectrum-input-id="' . $this->escapeHtmlAttr($spectrumId) . '">'
            . '<input type="hidden" id="' . $this->escapeHtmlAttr($spectrumId) . '"'
            . ' class="colorpicker-spectrum lc-special-price-color-system-spectrum" />'
            . $element->getElementHtml()
            . '</div>'
            . '<script type="text/x-magento-init">'
            . $this->jsonEncode($config)
            . '</script>';
    }

    /**
     * @param array $config
     * @return string
     */
    private function jsonEncode(array $config)
    {
        $json = json_encode($config, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

        return $json ?: '{}';
    }
}
