<?php

declare(strict_types=1);

namespace LeanCommerce\CustomSpecialPriceColor\Observer;

use LeanCommerce\CustomSpecialPriceColor\Model\Cache\SpecialPriceColorCacheInvalidator;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class FlushCacheOnConfigSave implements ObserverInterface
{
    private const CONFIG_PATHS = [
        'catalog/special_price_color/enabled',
        'catalog/special_price_color/default_color',
        'catalog/special_price_color/apply_in_plp',
        'catalog/special_price_color/apply_in_pdp',
        'catalog/special_price_color/apply_in_carousels',
    ];

    private SpecialPriceColorCacheInvalidator $cacheInvalidator;

    public function __construct(SpecialPriceColorCacheInvalidator $cacheInvalidator)
    {
        $this->cacheInvalidator = $cacheInvalidator;
    }

    public function execute(Observer $observer): void
    {
        $changedPaths = $observer->getEvent()->getData('changed_paths');

        if (is_array($changedPaths) && !$this->hasRelevantChangedPath($changedPaths)) {
            return;
        }

        $this->cacheInvalidator->cleanGlobalConfigCaches();
    }

    /**
     * @param string[] $changedPaths
     */
    private function hasRelevantChangedPath(array $changedPaths): bool
    {
        return (bool) array_intersect(self::CONFIG_PATHS, $changedPaths);
    }
}
