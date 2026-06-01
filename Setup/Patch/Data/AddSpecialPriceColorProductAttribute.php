<?php

declare(strict_types=1);

namespace LeanCommerce\CustomSpecialPriceColor\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddSpecialPriceColorProductAttribute implements DataPatchInterface
{
    private const ATTRIBUTE_CODE = 'special_price_color';

    private ModuleDataSetupInterface $moduleDataSetup;

    private CategorySetupFactory $categorySetupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);

        if (!$categorySetup->getAttributeId(Product::ENTITY, self::ATTRIBUTE_CODE)) {
            $categorySetup->addAttribute(
                Product::ENTITY,
                self::ATTRIBUTE_CODE,
                [
                    'type' => 'varchar',
                    'label' => 'Special Price Color',
                    'input' => 'text',
                    'required' => false,
                    'visible' => true,
                    'user_defined' => true,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Prices',
                    'sort_order' => 6,
                    'default' => null,
                    'used_in_product_listing' => true,
                    'visible_on_front' => false,
                    'is_html_allowed_on_front' => false,
                    'is_configurable' => false,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_in_advanced_search' => false,
                    'used_for_sort_by' => false,
                    'used_for_promo_rules' => false,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'unique' => false,
                ]
            );
        }

        $this->moduleDataSetup->endSetup();

        return $this;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
