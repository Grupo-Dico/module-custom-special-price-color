<?php

declare(strict_types=1);

namespace LeanCommerce\CustomSpecialPriceColor\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateSpecialPriceColorProductAttributeAdminGroup implements DataPatchInterface
{
    private const ATTRIBUTE_CODE = 'special_price_color';
    private const TARGET_GROUP = 'General';
    private const SORT_ORDER = 6;

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
        $attributeId = $categorySetup->getAttributeId(Product::ENTITY, self::ATTRIBUTE_CODE);

        if ($attributeId) {
            foreach ($categorySetup->getAllAttributeSetIds(Product::ENTITY) as $attributeSetId) {
                $groupId = $categorySetup->getAttributeGroup(
                    Product::ENTITY,
                    $attributeSetId,
                    self::TARGET_GROUP,
                    'attribute_group_id'
                );

                if ($groupId) {
                    $categorySetup->addAttributeToGroup(
                        Product::ENTITY,
                        $attributeSetId,
                        $groupId,
                        $attributeId,
                        self::SORT_ORDER
                    );
                }
            }
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
