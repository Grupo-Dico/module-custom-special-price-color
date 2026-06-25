<?php

declare(strict_types=1);

namespace LeanCommerce\CustomSpecialPriceColor\Plugin\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav;
use Magento\Ui\Component\Form\Element\ColorPicker;

class SpecialPriceColorMetaPlugin
{
    private const ATTRIBUTE_CODES = [
        'special_price_color',
        'special_price_label_color',
        'special_price_background_color',
    ];

    /**
     * Switch the module's product EAV color attributes to Magento's native UI color picker.
     *
     * @param Eav $subject
     * @param array $result
     * @param ProductAttributeInterface $attribute
     * @param string $groupCode
     * @param int $sortOrder
     * @return array
     */
    public function afterSetupAttributeMeta(
        Eav $subject,
        array $result,
        ProductAttributeInterface $attribute,
        $groupCode,
        $sortOrder
    ): array {
        $attributeCode = (string)$attribute->getAttributeCode();

        if (!in_array($attributeCode, self::ATTRIBUTE_CODES, true) || !$result) {
            return $result;
        }

        if (!isset($result['arguments']['data']['config'])
            || !is_array($result['arguments']['data']['config'])
        ) {
            $result['arguments']['data']['config'] = [];
        }

        $config = $result['arguments']['data']['config'];
        $config['dataType'] = 'text';
        $config['formElement'] = 'colorPicker';
        $config['component'] = 'Magento_Ui/js/form/element/color-picker';
        $config['elementTmpl'] = 'ui/form/element/color-picker';
        $config['template'] = 'ui/form/field';
        $config['placeholder'] = __('No Color');
        $config['colorPickerMode'] = 'full';
        $config['colorFormat'] = 'hex';
        $config['colorPickerConfig'] = [
            'showInput' => true,
            'showInitial' => false,
            'showPalette' => true,
            'showAlpha' => true,
            'showSelectionPalette' => true,
            'preferredFormat' => 'hex',
        ];

        // The Magento UI color picker only renders its visual spectrum widget when the
        // field is instantiated as the "colorPicker" form element (componentType=colorPicker),
        // exactly like the working category_form.xml. Using componentType=field renders a
        // plain input and the picker disappears. Setting componentType=field was the change
        // that hid the picker; the previous "[object Object]" was caused by missing
        // dataScope/inputName below, not by componentType.
        $config['componentType'] = ColorPicker::NAME;

        if (empty($config['dataScope']) || $config['dataScope'] === 'product') {
            $config['dataScope'] = $attributeCode;
        }

        if (empty($config['inputName']) || $config['inputName'] === 'product') {
            $config['inputName'] = 'product[' . $attributeCode . ']';
        }

        if (!isset($config['validation']) || !is_array($config['validation'])) {
            $config['validation'] = [];
        }
        $config['validation']['validate-color'] = true;

        if (!isset($config['additionalClasses']) || !is_array($config['additionalClasses'])) {
            $config['additionalClasses'] = [];
        }
        $config['additionalClasses']['admin__field-medium'] = true;

        $result['arguments']['data']['config'] = $config;

        return $result;
    }
}
