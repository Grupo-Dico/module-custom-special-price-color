<?php

declare(strict_types=1);

namespace LeanCommerce\CustomSpecialPriceColor\Plugin\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav;

class SpecialPriceColorMetaPlugin
{
    private const ATTRIBUTE_CODE = 'special_price_color';

    /**
     * Switch only the module product EAV attribute to Magento's native UI color picker.
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
        if ($attribute->getAttributeCode() !== self::ATTRIBUTE_CODE || !$result) {
            return $result;
        }

        if (!isset($result['arguments']['data']['config'])
            || !is_array($result['arguments']['data']['config'])
        ) {
            $result['arguments']['data']['config'] = [];
        }

        $result['arguments']['data']['config'] = array_replace_recursive(
            $result['arguments']['data']['config'],
            [
                'dataType' => 'text',
                'formElement' => 'colorPicker',
                'componentType' => 'colorPicker',
                'component' => 'Magento_Ui/js/form/element/color-picker',
                'elementTmpl' => 'ui/form/element/color-picker',
                'template' => 'ui/form/field',
                'placeholder' => __('No Color'),
                'colorPickerMode' => 'full',
                'colorFormat' => 'hex',
                'validation' => [
                    'validate-color' => true,
                ],
                'additionalClasses' => [
                    'admin__field-medium' => true,
                ],
            ]
        );

        return $result;
    }
}
