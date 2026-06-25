<?php

declare(strict_types=1);

namespace LeanCommerce\CustomSpecialPriceColor\Observer;

use LeanCommerce\CustomSpecialPriceColor\Model\Cache\SpecialPriceColorCacheInvalidator;
use Magento\Catalog\Model\Category;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class FlushCacheOnCategorySave implements ObserverInterface
{
    /**
     * Attribute codes that trigger a category cache flush when changed.
     */
    private const WATCHED_ATTRIBUTES = [
        'special_price_color',
        'special_price_label',
        'special_price_label_color',
        'special_price_background_color',
    ];

    /** @var SpecialPriceColorCacheInvalidator */
    private $cacheInvalidator;

    public function __construct(SpecialPriceColorCacheInvalidator $cacheInvalidator)
    {
        $this->cacheInvalidator = $cacheInvalidator;
    }

    public function execute(Observer $observer): void
    {
        $category = $observer->getEvent()->getData('category');

        if (!$category instanceof Category) {
            return;
        }

        if (!$this->hasAnyWatchedAttributeChanged($category)) {
            return;
        }

        $this->cacheInvalidator->cleanCategoryCache($category);
    }

    private function hasAnyWatchedAttributeChanged(Category $category): bool
    {
        foreach (self::WATCHED_ATTRIBUTES as $attributeCode) {
            if ($category->hasData($attributeCode) && $category->dataHasChangedFor($attributeCode)) {
                return true;
            }
        }

        return false;
    }
}
