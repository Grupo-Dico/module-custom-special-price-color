<?php

declare(strict_types=1);

namespace LeanCommerce\CustomSpecialPriceColor\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean as BooleanSource;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Adds the super_oferta product attribute (boolean/yes-no, global scope).
 * Idempotent: skips creation if attribute already exists.
 * Does NOT modify existing attributes.
 */
class AddSuperOfertaProductAttribute implements DataPatchInterface
{
    private const ATTRIBUTE_CODE = 'super_oferta';

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

        if (!$categorySetup->getAttributeId(Product::ENTITY, self::ATTRIBUTE_CODE)) {
            $categorySetup->addAttribute(
                Product::ENTITY,
                self::ATTRIBUTE_CODE,
                [
                    'type'                       => 'int',
                    'label'                      => 'Súper oferta',
                    'input'                      => 'boolean',
                    'source'                     => BooleanSource::class,
                    'required'                   => false,
                    'visible'                    => true,
                    'user_defined'               => true,
                    'global'                     => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'group'                      => 'General',
                    'sort_order'                 => 5,
                    'default'                    => 0,
                    'used_in_product_listing'    => true,
                    'visible_on_front'           => true,
                    'is_html_allowed_on_front'   => false,
                    'is_configurable'            => false,
                    'searchable'                 => false,
                    'filterable'                 => 1, // filterable with results
                    'comparable'                 => true,
                    'visible_in_advanced_search' => false,
                    'used_for_sort_by'           => false,
                    'used_for_promo_rules'       => true,
                    'is_used_in_grid'            => false,
                    'is_visible_in_grid'         => false,
                    'is_filterable_in_grid'      => false,
                    'unique'                     => false,
                    'position'                   => 2,
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
