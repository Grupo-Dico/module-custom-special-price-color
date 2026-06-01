<?php

declare(strict_types=1);

namespace LeanCommerce\CustomSpecialPriceColor\Model\Context;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Registry;

class CurrentCategoryProvider
{
    private Registry $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    public function getCurrentCategory(): ?CategoryInterface
    {
        $category = $this->registry->registry('current_category');

        if (!$category instanceof CategoryInterface || !$category->getId()) {
            return null;
        }

        return $category;
    }
}
