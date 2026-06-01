<?php

declare(strict_types=1);

namespace LeanCommerce\CustomSpecialPriceColor\Block\Pricing\Render;

use LeanCommerce\CustomSpecialPriceColor\Api\SpecialPriceColorResolverInterface;
use LeanCommerce\CustomSpecialPriceColor\Model\Config;
use LeanCommerce\CustomSpecialPriceColor\Model\Context\CurrentCategoryProvider;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface;
use Magento\Catalog\Pricing\Price\MinimalPriceCalculatorInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\View\Element\Template\Context;

class FinalPriceBox extends \Magento\Catalog\Pricing\Render\FinalPriceBox
{
    private SpecialPriceColorResolverInterface $specialPriceColorResolver;

    private Config $config;

    private CurrentCategoryProvider $currentCategoryProvider;

    private ?string $resolvedSpecialPriceColor = null;

    private bool $isSpecialPriceColorResolved = false;

    public function __construct(
        Context $context,
        SaleableInterface $saleableItem,
        PriceInterface $price,
        RendererPool $rendererPool,
        SpecialPriceColorResolverInterface $specialPriceColorResolver,
        Config $config,
        CurrentCategoryProvider $currentCategoryProvider,
        array $data = [],
        ?SalableResolverInterface $salableResolver = null,
        ?MinimalPriceCalculatorInterface $minimalPriceCalculator = null
    ) {
        parent::__construct(
            $context,
            $saleableItem,
            $price,
            $rendererPool,
            $data,
            $salableResolver,
            $minimalPriceCalculator
        );

        $this->specialPriceColorResolver = $specialPriceColorResolver;
        $this->config = $config;
        $this->currentCategoryProvider = $currentCategoryProvider;
    }

    public function renderSpecialPriceAmount(AmountInterface $amount, array $arguments = []): string
    {
        $html = $this->renderAmount($amount, $arguments);
        $color = $this->getSpecialPriceColor();

        if ($color === null) {
            return $html;
        }

        return $this->addStyleToPriceAmount($html, $color);
    }

    public function getSpecialPriceColor(): ?string
    {
        if (!$this->isSpecialPriceColorResolved) {
            $this->resolvedSpecialPriceColor = $this->resolveSpecialPriceColor();
            $this->isSpecialPriceColorResolved = true;
        }

        return $this->resolvedSpecialPriceColor;
    }

    public function getSpecialPriceMarkerAttributes(): string
    {
        if (!$this->hasSpecialPrice()) {
            return '';
        }

        $color = $this->getSpecialPriceColor();

        if ($color === null) {
            return '';
        }

        return ' data-lc-special-price-color="' . $this->escapeHtmlAttr($color) . '"';
    }

    public function getCacheKeyInfo()
    {
        $cacheKeys = parent::getCacheKeyInfo();
        $cacheKeys['lc_special_price_color'] = $this->getSpecialPriceColor() ?? 'none';
        $cacheKeys['lc_special_price_context'] = (string) $this->getRequest()->getFullActionName();

        $category = $this->currentCategoryProvider->getCurrentCategory();
        $cacheKeys['lc_special_price_category_id'] = $category ? (string) $category->getId() : 'none';

        return $cacheKeys;
    }

    protected function wrapResult($html)
    {
        return '<div class="price-box ' . $this->getData('css_classes') . '" ' .
            'data-role="priceBox" ' .
            'data-product-id="' . $this->getSaleableItem()->getId() . '" ' .
            'data-price-box="product-id-' . $this->getSaleableItem()->getId() . '"' .
            $this->getSpecialPriceMarkerAttributes() .
            '>' . $html . '</div>';
    }

    private function resolveSpecialPriceColor(): ?string
    {
        $product = $this->getSaleableItem();

        if (!$product instanceof ProductInterface || $product->getTypeId() !== Type::TYPE_SIMPLE) {
            return null;
        }

        $storeId = $this->getProductStoreId($product);
        $fullActionName = (string) $this->getRequest()->getFullActionName();
        $zone = $this->getZone();

        if ($fullActionName === 'catalog_category_view'
            && $zone === Render::ZONE_ITEM_LIST
            && $this->isCatalogListPrice()
        ) {
            if (!$this->config->isApplyInPlp($storeId)) {
                return null;
            }

            return $this->specialPriceColorResolver->resolve(
                $product,
                $this->currentCategoryProvider->getCurrentCategory(),
                $storeId
            );
        }

        if ($fullActionName === 'catalogsearch_result_index'
            && $zone === Render::ZONE_ITEM_LIST
            && $this->isCatalogListPrice()
        ) {
            if (!$this->config->isApplyInPlp($storeId)) {
                return null;
            }

            return $this->specialPriceColorResolver->resolve($product, null, $storeId);
        }

        if ($this->isPdpMainPrice($fullActionName, $zone)) {
            if (!$this->config->isApplyInPdp($storeId)) {
                return null;
            }

            return $this->specialPriceColorResolver->resolve(
                $product,
                $this->currentCategoryProvider->getCurrentCategory(),
                $storeId
            );
        }

        if ($this->isCarouselOrWidgetPrice($fullActionName, $zone)) {
            if (!$this->config->isApplyInCarousels($storeId)) {
                return null;
            }

            return $this->specialPriceColorResolver->resolve($product, null, $storeId);
        }

        return null;
    }

    private function isCatalogListPrice(): bool
    {
        return $this->isProductList() && (bool) $this->getData('list_category_page');
    }

    private function isPdpMainPrice(string $fullActionName, ?string $zone): bool
    {
        if ($fullActionName !== 'catalog_product_view') {
            return false;
        }

        if ($zone === Render::ZONE_ITEM_LIST || $this->isRestrictedCommerceContext($fullActionName)) {
            return false;
        }

        return $zone === Render::ZONE_ITEM_VIEW || $zone === Render::ZONE_DEFAULT || $zone === '';
    }

    private function isCarouselOrWidgetPrice(string $fullActionName, ?string $zone): bool
    {
        if ($zone !== Render::ZONE_ITEM_LIST || $this->isCatalogListPrice()) {
            return false;
        }

        return !$this->isRestrictedCommerceContext($fullActionName);
    }

    private function isRestrictedCommerceContext(string $fullActionName): bool
    {
        $restrictedActionPrefixes = [
            'checkout_',
            'multishipping_',
            'sales_',
            'customer_section_',
        ];

        foreach ($restrictedActionPrefixes as $prefix) {
            if (strpos($fullActionName, $prefix) === 0) {
                return true;
            }
        }

        return false;
    }

    private function addStyleToPriceAmount(string $html, string $color): string
    {
        $style = 'color: ' . $this->escapeHtmlAttr($color) . ' !important;';
        $pattern = '/(<span\b(?=[^>]*\bclass="(?:[^"]*\s)?price(?:\s[^"]*)?")[^>]*)(>)/';

        $styledHtml = preg_replace_callback(
            $pattern,
            function (array $matches) use ($style): string {
                $tagStart = $matches[1];

                if (preg_match('/\sstyle="([^"]*)"/', $tagStart)) {
                    $tagStart = preg_replace(
                        '/\sstyle="([^"]*)"/',
                        ' style="$1 ' . $style . '"',
                        $tagStart,
                        1
                    );

                    return $tagStart . $matches[2];
                }

                return $tagStart . ' style="' . $style . '"' . $matches[2];
            },
            $html,
            1
        );

        return $styledHtml ?? $html;
    }

    private function getProductStoreId(ProductInterface $product): ?int
    {
        if (!method_exists($product, 'getStoreId')) {
            return null;
        }

        $storeId = $product->getStoreId();

        return $storeId === null ? null : (int) $storeId;
    }
}
