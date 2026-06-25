<?php

declare(strict_types=1);

namespace LeanCommerce\CustomSpecialPriceColor\Setup\Patch\Data;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Adds special_price_label, special_price_label_color and special_price_background_color category attributes.
 * Idempotent: skips each attribute individually if it already exists.
 * Does NOT modify existing attributes.
 */
class AddSpecialPricePresentationCategoryAttributes implements DataPatchInterface
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
        if (!$categorySetup->getAttributeId(Category::ENTITY, self::ATTR_LABEL)) {
            $categorySetup->addAttribute(
                Category::ENTITY,
                self::ATTR_LABEL,
                [
                    'type'          => 'varchar',
                    'label'         => 'Special Price Label',
                    'input'         => 'text',
                    'required'      => false,
                    'visible'       => true,
                    'user_defined'  => true,
                    'global'        => ScopedAttributeInterface::SCOPE_STORE,
                    'group'         => 'Content',
                    'sort_order'    => 66,
                    'default'       => null,
                ]
            );
        }

        // ---- special_price_label_color ---- //
        if (!$categorySetup->getAttributeId(Category::ENTITY, self::ATTR_LABEL_COLOR)) {
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

        // ---- special_price_background_color ---- //
        if (!$categorySetup->getAttributeId(Category::ENTITY, self::ATTR_BG)) {
            $categorySetup->addAttribute(
                Category::ENTITY,
                self::ATTR_BG,
                [
                    'type'          => 'varchar',
                    'label'         => 'Special Price Background Color',
                    'input'         => 'text',
                    'required'      => false,
                    'visible'       => true,
                    'user_defined'  => true,
                    'global'        => ScopedAttributeInterface::SCOPE_STORE,
                    'group'         => 'Content',
                    'sort_order'    => 68,
                    'default'       => null,
                ]
            );
        }

        $this->moduleDataSetup->endSetup();

        return $this;
    }

    public static function getDependencies()
    {
        return [
            AddSpecialPriceColorCategoryAttribute::class,
        ];
    }

    public function getAliases()
    {
        return [];
    }
}
