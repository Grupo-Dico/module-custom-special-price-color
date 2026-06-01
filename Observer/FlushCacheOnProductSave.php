<?php

declare(strict_types=1);

namespace LeanCommerce\CustomSpecialPriceColor\Observer;

use LeanCommerce\CustomSpecialPriceColor\Model\Cache\SpecialPriceColorCacheInvalidator;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class FlushCacheOnProductSave implements ObserverInterface
{
    private const ATTRIBUTE_CODE = 'special_price_color';

    private SpecialPriceColorCacheInvalidator $cacheInvalidator;

    public function __construct(SpecialPriceColorCacheInvalidator $cacheInvalidator)
    {
        $this->cacheInvalidator = $cacheInvalidator;
    }

    public function execute(Observer $observer): void
    {
        $product = $observer->getEvent()->getData('product');

        if (!$product instanceof Product || !$this->hasSpecialPriceColorChanged($product)) {
            return;
        }

        $this->cacheInvalidator->cleanProductCache($product);
    }

    private function hasSpecialPriceColorChanged(Product $product): bool
    {
        if (!$product->hasData(self::ATTRIBUTE_CODE)) {
            return false;
        }

        return $product->dataHasChangedFor(self::ATTRIBUTE_CODE);
    }
}
