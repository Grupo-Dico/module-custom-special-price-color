<?php

declare(strict_types=1);

namespace LeanCommerce\CustomSpecialPriceColor\Setup\Patch\Data;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateSpecialPriceColorCategoryAttributeAdminGroup implements DataPatchInterface
{
    private const ATTRIBUTE_CODE = 'special_price_color';
    private const TARGET_GROUP = 'Content';
    private const SORT_ORDER = 65;

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
        $attributeId = $categorySetup->getAttributeId(Category::ENTITY, self::ATTRIBUTE_CODE);

        if ($attributeId) {
            $categorySetup->updateAttribute(
                Category::ENTITY,
                self::ATTRIBUTE_CODE,
                [
                    'frontend_input' => 'text',
                    'is_required' => 0,
                    'is_user_defined' => 1,
                    'is_visible' => 1,
                    'is_visible_on_front' => 0,
                    'is_html_allowed_on_front' => 0,
                ],
                null,
                self::SORT_ORDER
            );

            foreach ($categorySetup->getAllAttributeSetIds(Category::ENTITY) as $attributeSetId) {
                $groupId = $categorySetup->getAttributeGroup(
                    Category::ENTITY,
                    $attributeSetId,
                    self::TARGET_GROUP,
                    'attribute_group_id'
                );

                if ($groupId) {
                    $categorySetup->addAttributeToGroup(
                        Category::ENTITY,
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
            AddSpecialPriceColorCategoryAttribute::class,
        ];
    }

    public function getAliases()
    {
        return [];
    }
}
