<?php

declare(strict_types=1);

namespace LeanCommerce\CustomSpecialPriceColor\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class MoveSpecialPricePresentationProductAttributesToGeneralGroup implements DataPatchInterface
{
    private const TARGET_GROUP = 'General';

    private const ATTRIBUTE_SORT_ORDERS = [
        'special_price_label' => 6,
        'special_price_label_color' => 7,
        'special_price_color' => 8,
        'special_price_background_color' => 9,
    ];

    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /** @var CategorySetupFactory */
    private $categorySetupFactory;

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

        foreach ($categorySetup->getAllAttributeSetIds(Product::ENTITY) as $attributeSetId) {
            $groupId = $categorySetup->getAttributeGroup(
                Product::ENTITY,
                $attributeSetId,
                self::TARGET_GROUP,
                'attribute_group_id'
            );

            if (!$groupId) {
                continue;
            }

            foreach (self::ATTRIBUTE_SORT_ORDERS as $attributeCode => $sortOrder) {
                $attributeId = $categorySetup->getAttributeId(Product::ENTITY, $attributeCode);
                if (!$attributeId) {
                    continue;
                }

                $categorySetup->addAttributeToGroup(
                    Product::ENTITY,
                    $attributeSetId,
                    $groupId,
                    $attributeId,
                    $sortOrder
                );
            }
        }

        $this->moduleDataSetup->endSetup();

        return $this;
    }

    public static function getDependencies()
    {
        return [
            AddSpecialPriceColorProductAttribute::class,
            AddSpecialPricePresentationProductAttributes::class,
            AddSpecialPriceLabelColorAndListingMetadata::class,
            UpdateSpecialPriceColorProductAttributeAdminGroup::class,
        ];
    }

    public function getAliases()
    {
        return [];
    }
}
