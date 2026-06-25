<?php

declare(strict_types=1);

namespace LeanCommerce\CustomSpecialPriceColor\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Adds special_price_label, special_price_label_color and special_price_background_color product attributes.
 * Idempotent: skips each attribute individually if it already exists.
 * Does NOT modify existing attributes.
 */
class AddSpecialPricePresentationProductAttributes implements DataPatchInterface
{
    private const ATTR_LABEL       = 'special_price_label';
    private const ATTR_LABEL_COLOR = 'special_price_label_color';
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

        // ---- special_price_label ---- //
        if (!$categorySetup->getAttributeId(Product::ENTITY, self::ATTR_LABEL)) {
            $categorySetup->addAttribute(
                Product::ENTITY,
                self::ATTR_LABEL,
                [
                    'type'                       => 'varchar',
                    'label'                      => 'Special Price Label',
                    'input'                      => 'text',
                    'required'                   => false,
                    'visible'                    => true,
                    'user_defined'               => true,
                    'global'                     => ScopedAttributeInterface::SCOPE_STORE,
                    'group'                      => 'General',
                    'sort_order'                 => 6,
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

        // ---- special_price_label_color ---- //
        if (!$categorySetup->getAttributeId(Product::ENTITY, self::ATTR_LABEL_COLOR)) {
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

        // ---- special_price_background_color ---- //
        if (!$categorySetup->getAttributeId(Product::ENTITY, self::ATTR_BG)) {
            $categorySetup->addAttribute(
                Product::ENTITY,
                self::ATTR_BG,
                [
                    'type'                       => 'varchar',
                    'label'                      => 'Special Price Background Color',
                    'input'                      => 'text',
                    'required'                   => false,
                    'visible'                    => true,
                    'user_defined'               => true,
                    'global'                     => ScopedAttributeInterface::SCOPE_STORE,
                    'group'                      => 'General',
                    'sort_order'                 => 9,
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

        $this->moduleDataSetup->endSetup();

        return $this;
    }

    public static function getDependencies()
    {
        return [
            AddSpecialPriceColorProductAttribute::class,
        ];
    }

    public function getAliases()
    {
        return [];
    }
}
