<?php

declare(strict_types=1);

namespace LeanCommerce\CustomSpecialPriceColor\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    public const XML_PATH_ENABLED = 'catalog/special_price_color/enabled';
    public const XML_PATH_DEFAULT_COLOR = 'catalog/special_price_color/default_color';
    public const XML_PATH_APPLY_IN_PLP = 'catalog/special_price_color/apply_in_plp';
    public const XML_PATH_APPLY_IN_PDP = 'catalog/special_price_color/apply_in_pdp';
    public const XML_PATH_APPLY_IN_CAROUSELS = 'catalog/special_price_color/apply_in_carousels';

    private ScopeConfigInterface $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getDefaultColor(?int $storeId = null): ?string
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_DEFAULT_COLOR,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    public function isApplyInPlp(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_APPLY_IN_PLP,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isApplyInPdp(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_APPLY_IN_PDP,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isApplyInCarousels(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_APPLY_IN_CAROUSELS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
