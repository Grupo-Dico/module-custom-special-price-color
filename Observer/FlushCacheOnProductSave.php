<?php

declare(strict_types=1);

namespace LeanCommerce\CustomSpecialPriceColor\Observer;

use LeanCommerce\CustomSpecialPriceColor\Model\Cache\SpecialPriceColorCacheInvalidator;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class FlushCacheOnProductSave implements ObserverInterface
{
    /**
     * Attribute codes that trigger a product cache flush when changed.
     */
    private const WATCHED_ATTRIBUTES = [
        'special_price_color',
        'special_price_label',
        'special_price_label_color',
        'special_price_background_color',
        'super_oferta',
        'al_pagar_precio',
    ];

    /** @var SpecialPriceColorCacheInvalidator */
    private $cacheInvalidator;

    public function __construct(SpecialPriceColorCacheInvalidator $cacheInvalidator)
    {
        $this->cacheInvalidator = $cacheInvalidator;
    }

    public function execute(Observer $observer): void
    {
        $product = $observer->getEvent()->getData('product');

        if (!$product instanceof Product) {
            return;
        }

        if (!$this->hasAnyWatchedAttributeChanged($product)) {
            return;
        }

        $this->cacheInvalidator->cleanProductCache($product);
    }

    private function hasAnyWatchedAttributeChanged(Product $product): bool
    {
        foreach (self::WATCHED_ATTRIBUTES as $attributeCode) {
            if ($product->hasData($attributeCode) && $product->dataHasChangedFor($attributeCode)) {
                return true;
            }
        }

        return false;
    }
}
