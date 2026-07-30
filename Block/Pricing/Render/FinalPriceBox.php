<?php

declare(strict_types=1);

namespace LeanCommerce\CustomSpecialPriceColor\Block\Pricing\Render;

use LeanCommerce\CustomSpecialPriceColor\Api\SpecialPriceColorResolverInterface;
use LeanCommerce\CustomSpecialPriceColor\Api\SpecialPricePresentationResolverInterface;
use LeanCommerce\CustomSpecialPriceColor\Model\Config;
use LeanCommerce\CustomSpecialPriceColor\Model\Context\CurrentCategoryProvider;
use LeanCommerce\CustomSpecialPriceColor\Model\Presentation\SpecialPricePresentation;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface;
use Magento\Catalog\Pricing\Price\MinimalPriceCalculatorInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\View\Element\Template\Context;

class FinalPriceBox extends \Magento\Catalog\Pricing\Render\FinalPriceBox
{
    /** @var SpecialPricePresentationResolverInterface */
    private $presentationResolver;

    /** @var SpecialPriceColorResolverInterface */
    private $specialPriceColorResolver;

    /** @var Config */
    private $config;

    /** @var CurrentCategoryProvider */
    private $currentCategoryProvider;

    /** @var PriceCurrencyInterface */
    private $priceCurrency;

    /** @var SpecialPricePresentation|null */
    private $resolvedPresentation;

    /** @var bool */
    private $isPresentationResolved = false;

    public function __construct(
        Context $context,
        SaleableInterface $saleableItem,
        PriceInterface $price,
        RendererPool $rendererPool,
        SpecialPricePresentationResolverInterface $presentationResolver,
        SpecialPriceColorResolverInterface $specialPriceColorResolver,
        Config $config,
        CurrentCategoryProvider $currentCategoryProvider,
        PriceCurrencyInterface $priceCurrency,
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

        $this->presentationResolver      = $presentationResolver;
        $this->specialPriceColorResolver = $specialPriceColorResolver;
        $this->config                    = $config;
        $this->currentCategoryProvider   = $currentCategoryProvider;
        $this->priceCurrency             = $priceCurrency;
    }

    public function getSpecialPricePresentation(): ?SpecialPricePresentation
    {
        if (!$this->isPresentationResolved) {
            $this->resolvedPresentation = $this->resolvePresentation();
            $this->isPresentationResolved = true;
        }

        return $this->resolvedPresentation;
    }

    public function getSpecialPriceColor(): ?string
    {
        $presentation = $this->getSpecialPricePresentation();

        if ($presentation === null || !$this->isSpecialPricePresentation($presentation)) {
            return null;
        }

        return $presentation->getPriceColor();
    }

    public function renderSpecialPriceAmount(AmountInterface $amount, array $arguments = []): string
    {
        $html = $this->renderAmount($amount, $arguments);
        $presentation = $this->getSpecialPricePresentation();

        if ($presentation === null || !$this->isSpecialPricePresentation($presentation)) {
            return $html;
        }

        return $this->addStylesToPriceAmount(
            $html,
            $presentation->getPriceColor(),
            $presentation->getLabelColor(),
            $presentation->getPriceBackgroundColor()
        );
    }

    public function getSpecialPriceMarkerAttributes(): string
    {
        $presentation = $this->getSpecialPricePresentation();

        if ($presentation === null || !$this->isSpecialPricePresentation($presentation)) {
            return '';
        }

        return $this->buildPresentationDataAttributes($presentation, 'special-price');
    }

    public function getSpecialPriceModeClass(): string
    {
        $presentation = $this->getSpecialPricePresentation();

        if ($presentation === null || !$this->isSpecialPricePresentation($presentation)) {
            return '';
        }

        return ' lc-price-mode-' . $this->escapeAttributeValue($presentation->getMode());
    }

    public function getSpecialPriceLabel(): ?string
    {
        $presentation = $this->getSpecialPricePresentation();

        if ($presentation === null || !$this->isSpecialPricePresentation($presentation)) {
            return null;
        }

        return $presentation->getLabel();
    }

    public function getThirdPriceAmount(): ?float
    {
        $presentation = $this->getSpecialPricePresentation();

        return $presentation === null ? null : $presentation->getThirdPriceAmount();
    }

    public function getThirdPriceLabel(): string
    {
        return (string) __('Normal');
    }

    public function getThirdPriceAmountStyle(): string
    {
        return 'color: #595c5a; font-weight: 600; font-size: 1.4rem; text-decoration: line-through;';
    }

    public function getFormattedThirdPriceAmount(): ?string
    {
        $amount = $this->getThirdPriceAmount();

        if ($amount === null || $amount <= 0.0) {
            return null;
        }

        $saleableItem = $this->getSaleableItem();
        $storeId = $saleableItem instanceof ProductInterface ? $this->getProductStoreId($saleableItem) : null;

        return $this->priceCurrency->format(
            $amount,
            false,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $storeId
        );
    }

    public function getCacheKeyInfo()
    {
        $cacheKeys = parent::getCacheKeyInfo();
        $presentation = $this->getSpecialPricePresentation();

        $cacheKeys['lc_price_mode'] = $presentation ? $presentation->getMode() : 'none';
        $cacheKeys['lc_presentation_hash'] = $presentation
            ? sha1($presentation->toCacheKeyPart())
            : 'none';
        $cacheKeys['lc_special_price_context'] = (string) $this->getRequest()->getFullActionName();

        $category = $this->currentCategoryProvider->getCurrentCategory();
        $cacheKeys['lc_special_price_category_id'] = $category ? (string) $category->getId() : 'none';

        return $cacheKeys;
    }

    protected function wrapResult($html)
    {
        $presentation = $this->getSpecialPricePresentation();
        $boxAttrs = '';

        if ($presentation !== null && $this->isSpecialPricePresentation($presentation)) {
            $boxAttrs = $this->buildPresentationDataAttributes($presentation, 'price-box');
        }

        return '<div class="price-box ' . $this->getData('css_classes') . '" ' .
            'data-role="priceBox" ' .
            'data-product-id="' . $this->getSaleableItem()->getId() . '" ' .
            'data-price-box="product-id-' . $this->getSaleableItem()->getId() . '"' .
            $boxAttrs .
            '>' . $html . '</div>';
    }

    private function resolvePresentation(): ?SpecialPricePresentation
    {
        $product = $this->getSaleableItem();

        if (!$product instanceof ProductInterface || $product->getTypeId() !== Type::TYPE_SIMPLE) {
            return null;
        }

        $storeId    = $this->getProductStoreId($product);
        $fullAction = (string) $this->getRequest()->getFullActionName();
        $zone       = $this->getZone();

        if ($fullAction === 'catalog_category_view'
            && $zone === Render::ZONE_ITEM_LIST
            && $this->isCatalogListPrice()
        ) {
            if (!$this->config->isApplyInPlp($storeId)) {
                return null;
            }

            return $this->presentationResolver->resolve(
                $product,
                $this->currentCategoryProvider->getCurrentCategory(),
                $storeId
            );
        }

        if ($fullAction === 'catalogsearch_result_index'
            && $zone === Render::ZONE_ITEM_LIST
            && $this->isCatalogListPrice()
        ) {
            if (!$this->config->isApplyInPlp($storeId)) {
                return null;
            }

            return $this->presentationResolver->resolve($product, null, $storeId);
        }

        if ($this->isPdpMainPrice($fullAction, $zone)) {
            if (!$this->config->isApplyInPdp($storeId)) {
                return null;
            }

            return $this->presentationResolver->resolve(
                $product,
                $this->currentCategoryProvider->getCurrentCategory(),
                $storeId
            );
        }

        if ($this->isCarouselOrWidgetPrice($fullAction, $zone)) {
            if (!$this->config->isApplyInCarousels($storeId)) {
                return null;
            }

            return $this->presentationResolver->resolve($product, null, $storeId);
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

    private function isSpecialPricePresentation(SpecialPricePresentation $presentation): bool
    {
        return in_array(
            $presentation->getMode(),
            [
                SpecialPricePresentation::MODE_NORMAL,
                SpecialPricePresentation::MODE_SUPER_OFERTA,
                SpecialPricePresentation::MODE_THIRD_PRICE,
            ],
            true
        );
    }

    private function buildPresentationDataAttributes(
        SpecialPricePresentation $presentation,
        string $target
    ): string {
        $attrs  = ' data-lc-price-mode="' . $this->escapeAttributeValue($presentation->getMode()) . '"';
        $attrs .= ' data-lc-presentation-target="' . $this->escapeAttributeValue($target) . '"';

        if ($presentation->getLabelColor() !== null) {
            $attrs .= ' data-lc-special-price-label-color="'
                . $this->escapeAttributeValue($presentation->getLabelColor()) . '"';
        }

        if ($presentation->getPriceColor() !== null) {
            $attrs .= ' data-lc-special-price-color="'
                . $this->escapeAttributeValue($presentation->getPriceColor()) . '"';
        }

        if ($presentation->getPriceBackgroundColor() !== null) {
            $attrs .= ' data-lc-special-price-bg="'
                . $this->escapeAttributeValue($presentation->getPriceBackgroundColor()) . '"';
        }

        return $attrs;
    }

    private function buildColorStyle(?string $color): string
    {
        if ($color === null) {
            return '';
        }

        return 'color: ' . $color . ' !important;';
    }

    private function buildPriceStyle(?string $priceColor, ?string $backgroundColor): string
    {
        $style = $this->buildColorStyle($priceColor);

        if ($backgroundColor !== null) {
            $style .= 'background-color: ' . $backgroundColor . ' !important; padding: 1px 5px;';
        }

        return $style;
    }

    private function addStylesToPriceAmount(
        ?string $html,
        ?string $priceColor,
        ?string $labelColor,
        ?string $backgroundColor
    ): string {
        $html = $html ?? '';

        if ($labelColor !== null) {
            $html = $this->addStyleToFirstClassedSpan($html, 'price-label', $this->buildColorStyle($labelColor));
        }

        $priceStyle = $this->buildPriceStyle($priceColor, $backgroundColor);
        if ($priceStyle !== '') {
            $html = $this->addStyleToFirstClassedSpan($html, 'price', $priceStyle);
        }

        return $html;
    }

    private function addStyleToFirstClassedSpan(string $html, string $className, string $style): string
    {
        if ($style === '') {
            return $html;
        }

        $style = $this->escapeAttributeValue($style);
        $pattern = '~(<span\b[^>]*\bclass="(?:[^"]*\s)?'
            . preg_quote($className, '~')
            . '(?:\s[^"]*)?"[^>]*>)~i';
        $styledHtml = preg_replace_callback(
            $pattern,
            function (array $matches) use ($style): string {
                $tagStart = rtrim($matches[1], '>');

                if (preg_match('/\sstyle="([^"]*)"/', $tagStart)) {
                    return preg_replace(
                        '/\sstyle="([^"]*)"/',
                        ' style="$1 ' . $style . '"',
                        $tagStart,
                        1
                    ) . '>';
                }

                return $tagStart . ' style="' . $style . '">';
            },
            $html,
            1
        );

        return $styledHtml ?? $html;
    }

    private function escapeAttributeValue(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
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
