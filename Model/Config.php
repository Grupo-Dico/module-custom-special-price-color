<?php

declare(strict_types=1);

namespace LeanCommerce\CustomSpecialPriceColor\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    // ------------------------------------------------------------------ //
    // General
    // ------------------------------------------------------------------ //
    public const XML_PATH_ENABLED = 'catalog/special_price_color/enabled';

    // Apply flags
    public const XML_PATH_APPLY_IN_PLP       = 'catalog/special_price_color/apply_in_plp';
    public const XML_PATH_APPLY_IN_PDP       = 'catalog/special_price_color/apply_in_pdp';
    public const XML_PATH_APPLY_IN_CAROUSELS = 'catalog/special_price_color/apply_in_carousels';

    // ------------------------------------------------------------------ //
    // Normal special_price global presentation
    // ------------------------------------------------------------------ //
    public const XML_PATH_DEFAULT_LABEL            = 'catalog/special_price_color/default_label';
    public const XML_PATH_DEFAULT_LABEL_COLOR      = 'catalog/special_price_color/default_label_color';
    public const XML_PATH_DEFAULT_COLOR            = 'catalog/special_price_color/default_color';
    public const XML_PATH_DEFAULT_BACKGROUND_COLOR = 'catalog/special_price_color/default_background_color';

    // ------------------------------------------------------------------ //
    // Súper Oferta global presentation
    // ------------------------------------------------------------------ //
    public const XML_PATH_SUPER_OFERTA_LABEL            = 'catalog/special_price_color/super_oferta_label';
    public const XML_PATH_SUPER_OFERTA_LABEL_COLOR      = 'catalog/special_price_color/super_oferta_label_color';
    public const XML_PATH_SUPER_OFERTA_PRICE_COLOR      = 'catalog/special_price_color/super_oferta_price_color';
    public const XML_PATH_SUPER_OFERTA_BACKGROUND_COLOR = 'catalog/special_price_color/super_oferta_background_color';

    // ------------------------------------------------------------------ //
    // Third price (al_pagar_precio) global presentation
    // ------------------------------------------------------------------ //
    public const XML_PATH_THIRD_PRICE_LABEL            = 'catalog/special_price_color/third_price_label';
    public const XML_PATH_THIRD_PRICE_LABEL_COLOR      = 'catalog/special_price_color/third_price_label_color';
    public const XML_PATH_THIRD_PRICE_PRICE_COLOR      = 'catalog/special_price_color/third_price_price_color';
    public const XML_PATH_THIRD_PRICE_BACKGROUND_COLOR = 'catalog/special_price_color/third_price_background_color';

    /** @var ScopeConfigInterface */
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    // ------------------------------------------------------------------ //
    // General flags
    // ------------------------------------------------------------------ //

    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
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

    // ------------------------------------------------------------------ //
    // Normal presentation
    // ------------------------------------------------------------------ //

    public function getDefaultLabel(?int $storeId = null): ?string
    {
        return $this->getNullableString(self::XML_PATH_DEFAULT_LABEL, $storeId);
    }

    public function getDefaultColor(?int $storeId = null): ?string
    {
        return $this->getNullableString(self::XML_PATH_DEFAULT_COLOR, $storeId);
    }

    public function getDefaultLabelColor(?int $storeId = null): ?string
    {
        return $this->getNullableString(self::XML_PATH_DEFAULT_LABEL_COLOR, $storeId);
    }

    public function getDefaultBackgroundColor(?int $storeId = null): ?string
    {
        return $this->getNullableString(self::XML_PATH_DEFAULT_BACKGROUND_COLOR, $storeId);
    }

    // ------------------------------------------------------------------ //
    // Súper Oferta presentation
    // ------------------------------------------------------------------ //

    public function getSuperOfertaLabel(?int $storeId = null): ?string
    {
        return $this->getNullableString(self::XML_PATH_SUPER_OFERTA_LABEL, $storeId);
    }

    public function getSuperOfertaTextColor(?int $storeId = null): ?string
    {
        return $this->getSuperOfertaPriceColor($storeId);
    }

    public function getSuperOfertaLabelColor(?int $storeId = null): ?string
    {
        return $this->getNullableString(self::XML_PATH_SUPER_OFERTA_LABEL_COLOR, $storeId);
    }

    public function getSuperOfertaPriceColor(?int $storeId = null): ?string
    {
        return $this->getNullableString(self::XML_PATH_SUPER_OFERTA_PRICE_COLOR, $storeId);
    }

    public function getSuperOfertaBackgroundColor(?int $storeId = null): ?string
    {
        return $this->getNullableString(self::XML_PATH_SUPER_OFERTA_BACKGROUND_COLOR, $storeId);
    }

    // ------------------------------------------------------------------ //
    // Third price (al_pagar_precio) presentation
    // ------------------------------------------------------------------ //

    public function getThirdPriceLabel(?int $storeId = null): ?string
    {
        return $this->getNullableString(self::XML_PATH_THIRD_PRICE_LABEL, $storeId);
    }

    public function getThirdPriceTextColor(?int $storeId = null): ?string
    {
        return $this->getThirdPricePriceColor($storeId);
    }

    public function getThirdPriceLabelColor(?int $storeId = null): ?string
    {
        return $this->getNullableString(self::XML_PATH_THIRD_PRICE_LABEL_COLOR, $storeId);
    }

    public function getThirdPricePriceColor(?int $storeId = null): ?string
    {
        return $this->getNullableString(self::XML_PATH_THIRD_PRICE_PRICE_COLOR, $storeId);
    }

    public function getThirdPriceBackgroundColor(?int $storeId = null): ?string
    {
        return $this->getNullableString(self::XML_PATH_THIRD_PRICE_BACKGROUND_COLOR, $storeId);
    }

    // ------------------------------------------------------------------ //
    // Helpers
    // ------------------------------------------------------------------ //

    private function getNullableString(string $path, ?int $storeId): ?string
    {
        $value = $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);

        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
