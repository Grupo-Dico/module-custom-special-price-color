<?php

declare(strict_types=1);

namespace LeanCommerce\CustomSpecialPriceColor\Observer;

use LeanCommerce\CustomSpecialPriceColor\Model\Cache\SpecialPriceColorCacheInvalidator;
use Magento\Catalog\Model\Category;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class FlushCacheOnCategorySave implements ObserverInterface
{
    private const ATTRIBUTE_CODE = 'special_price_color';

    private SpecialPriceColorCacheInvalidator $cacheInvalidator;

    public function __construct(SpecialPriceColorCacheInvalidator $cacheInvalidator)
    {
        $this->cacheInvalidator = $cacheInvalidator;
    }

    public function execute(Observer $observer): void
    {
        $category = $observer->getEvent()->getData('category');

        if (!$category instanceof Category || !$this->hasSpecialPriceColorChanged($category)) {
            return;
        }

        $this->cacheInvalidator->cleanCategoryCache($category);
    }

    private function hasSpecialPriceColorChanged(Category $category): bool
    {
        if (!$category->hasData(self::ATTRIBUTE_CODE)) {
            return false;
        }

        return $category->dataHasChangedFor(self::ATTRIBUTE_CODE);
    }
}
