<?php

declare(strict_types=1);

namespace LeanCommerce\CustomSpecialPriceColor\Setup\Patch\Data;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddSpecialPriceLabelColorAndListingMetadata implements DataPatchInterface
{
    private const ATTR_LABEL       = 'special_price_label';
    private const ATTR_LABEL_COLOR = 'special_price_label_color';
    private const ATTR_PRICE_COLOR = 'special_price_color';
    private const ATTR_BG          = 'special_price_background_color';

    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /** @var CategorySetupFactory */
    private $categorySetupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->moduleDataSetup      = $moduleDataSetup;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);

        $this->ensureProductLabelColor($categorySetup);
        $this->ensureCategoryLabelColor($categorySetup);
        $this->updateProductListingFlags($categorySetup);

        $this->moduleDataSetup->endSetup();

        return $this;
    }

    private function ensureProductLabelColor($categorySetup): void
    {
        if ($categorySetup->getAttributeId(Product::ENTITY, self::ATTR_LABEL_COLOR)) {
            return;
        }

        $categorySetup->addAttribute(
            Product::ENTITY,
            self::ATTR_LABEL_COLOR,
            [
                'type'                       => 'varchar',
                'label'                      => 'Special Price Label Color',
                'input'                      => 'text',
                'required'                   => false,
                'visible'                    => true,
                'user_defined'               => true,
                'global'                     => ScopedAttributeInterface::SCOPE_STORE,
                'group'                      => 'General',
                'sort_order'                 => 7,
                'default'                    => null,
                'used_in_product_listing'    => true,
                'visible_on_front'           => false,
                'is_html_allowed_on_front'   => false,
                'is_configurable'            => false,
                'searchable'                 => false,
                'filterable'                 => false,
                'comparable'                 => false,
                'visible_in_advanced_search' => false,
                'used_for_sort_by'           => false,
                'used_for_promo_rules'       => false,
                'is_used_in_grid'            => false,
                'is_visible_in_grid'         => false,
                'is_filterable_in_grid'      => false,
                'unique'                     => false,
            ]
        );
    }

    private function ensureCategoryLabelColor($categorySetup): void
    {
        if ($categorySetup->getAttributeId(Category::ENTITY, self::ATTR_LABEL_COLOR)) {
            return;
        }

        $categorySetup->addAttribute(
            Category::ENTITY,
            self::ATTR_LABEL_COLOR,
            [
                'type'          => 'varchar',
                'label'         => 'Special Price Label Color',
                'input'         => 'text',
                'required'      => false,
                'visible'       => true,
                'user_defined'  => true,
                'global'        => ScopedAttributeInterface::SCOPE_STORE,
                'group'         => 'Content',
                'sort_order'    => 67,
                'default'       => null,
            ]
        );
    }

    private function updateProductListingFlags($categorySetup): void
    {
        foreach ([self::ATTR_LABEL, self::ATTR_LABEL_COLOR, self::ATTR_PRICE_COLOR, self::ATTR_BG] as $attributeCode) {
            if (!$categorySetup->getAttributeId(Product::ENTITY, $attributeCode)) {
                continue;
            }

            $categorySetup->updateAttribute(
                Product::ENTITY,
                $attributeCode,
                'used_in_product_listing',
                1
            );
        }
    }

    public static function getDependencies()
    {
        return [
            AddSpecialPriceColorProductAttribute::class,
            AddSpecialPriceColorCategoryAttribute::class,
        ];
    }

    public function getAliases()
    {
        return [];
    }
}
