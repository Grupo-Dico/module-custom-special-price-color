<?php

declare(strict_types=1);

namespace LeanCommerce\CustomSpecialPriceColor\Model\Resolver;

use LeanCommerce\CustomSpecialPriceColor\Api\SpecialPricePresentationResolverInterface;
use LeanCommerce\CustomSpecialPriceColor\Model\Color\HexColorNormalizer;
use LeanCommerce\CustomSpecialPriceColor\Model\Config;
use LeanCommerce\CustomSpecialPriceColor\Model\Presentation\SpecialPricePresentation;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class SpecialPricePresentationResolver implements SpecialPricePresentationResolverInterface
{
    private const ATTR_SPECIAL_PRICE_LABEL          = 'special_price_label';
    private const ATTR_SPECIAL_PRICE_LABEL_COLOR    = 'special_price_label_color';
    private const ATTR_SPECIAL_PRICE_COLOR          = 'special_price_color';
    private const ATTR_SPECIAL_PRICE_BG_COLOR       = 'special_price_background_color';
    private const ATTR_SUPER_OFERTA                 = 'super_oferta';
    private const ATTR_AL_PAGAR_PRECIO              = 'al_pagar_precio';
    private const ATTR_SPECIAL_PRICE                = 'special_price';
    private const ATTR_SPECIAL_FROM_DATE            = 'special_from_date';
    private const ATTR_SPECIAL_TO_DATE              = 'special_to_date';

    /** @var Config */
    private $config;

    /** @var HexColorNormalizer */
    private $normalizer;

    /** @var TimezoneInterface */
    private $timezone;

    public function __construct(
        Config $config,
        HexColorNormalizer $normalizer,
        TimezoneInterface $timezone
    ) {
        $this->config     = $config;
        $this->normalizer = $normalizer;
        $this->timezone   = $timezone;
    }

    public function resolve(
        ProductInterface $product,
        ?CategoryInterface $category = null,
        ?int $storeId = null
    ): SpecialPricePresentation {
        if (!$this->config->isEnabled($storeId) || $this->getProductTypeId($product) !== Type::TYPE_SIMPLE) {
            return $this->nonePresentation();
        }

        $hasActiveSpecialPrice = $this->hasActiveSpecialPrice($product, $storeId);
        $alPagarPrecio = $this->getAlPagarPrecio($product);

        if ($hasActiveSpecialPrice && $this->isSuperOferta($product)) {
            return $this->buildSuperOfertaPresentation($storeId, $alPagarPrecio);
        }

        if ($alPagarPrecio !== null && $alPagarPrecio > 0.0) {
            return $this->buildThirdPricePresentation($alPagarPrecio, $storeId);
        }

        if ($hasActiveSpecialPrice) {
            return $this->buildNormalPresentation($product, $category, $storeId);
        }

        return $this->nonePresentation();
    }

    private function nonePresentation(): SpecialPricePresentation
    {
        return new SpecialPricePresentation(SpecialPricePresentation::MODE_NONE);
    }

    private function buildSuperOfertaPresentation(?int $storeId, ?float $thirdPriceAmount): SpecialPricePresentation
    {
        return new SpecialPricePresentation(
            SpecialPricePresentation::MODE_SUPER_OFERTA,
            $this->config->getSuperOfertaLabel($storeId) ?: (string) __('Súper Oferta'),
            $this->normalizer->normalize($this->config->getSuperOfertaLabelColor($storeId)),
            $this->normalizer->normalize($this->config->getSuperOfertaPriceColor($storeId)),
            $this->normalizer->normalize($this->config->getSuperOfertaBackgroundColor($storeId)),
            $thirdPriceAmount
        );
    }

    private function buildThirdPricePresentation(float $amount, ?int $storeId): SpecialPricePresentation
    {
        return new SpecialPricePresentation(
            SpecialPricePresentation::MODE_THIRD_PRICE,
            $this->config->getThirdPriceLabel($storeId) ?: (string) __('Al pagar'),
            $this->normalizer->normalize($this->config->getThirdPriceLabelColor($storeId)),
            $this->normalizer->normalize($this->config->getThirdPricePriceColor($storeId)),
            $this->normalizer->normalize($this->config->getThirdPriceBackgroundColor($storeId)),
            $amount
        );
    }

    private function buildNormalPresentation(
        ProductInterface $product,
        ?CategoryInterface $category,
        ?int $storeId
    ): SpecialPricePresentation {
        return new SpecialPricePresentation(
            SpecialPricePresentation::MODE_NORMAL,
            $this->resolveLabel($product, $category, $storeId),
            $this->normalizer->normalize(
                $this->resolveAttributeFallback(
                    self::ATTR_SPECIAL_PRICE_LABEL_COLOR,
                    $product,
                    $category,
                    $this->config->getDefaultLabelColor($storeId)
                )
            ),
            $this->normalizer->normalize(
                $this->resolveAttributeFallback(
                    self::ATTR_SPECIAL_PRICE_COLOR,
                    $product,
                    $category,
                    $this->config->getDefaultColor($storeId)
                )
            ),
            $this->normalizer->normalize(
                $this->resolveAttributeFallback(
                    self::ATTR_SPECIAL_PRICE_BG_COLOR,
                    $product,
                    $category,
                    $this->config->getDefaultBackgroundColor($storeId)
                )
            )
        );
    }

    private function resolveLabel(
        ProductInterface $product,
        ?CategoryInterface $category,
        ?int $storeId
    ): ?string {
        return $this->resolveAttributeFallback(
            self::ATTR_SPECIAL_PRICE_LABEL,
            $product,
            $category,
            $this->config->getDefaultLabel($storeId)
        );
    }

    private function resolveAttributeFallback(
        string $attributeCode,
        ProductInterface $product,
        ?CategoryInterface $category,
        ?string $globalFallback
    ): ?string {
        $productValue = $this->getStringAttribute($product, $attributeCode);
        if ($productValue !== null && trim($productValue) !== '') {
            return trim($productValue);
        }

        if ($category !== null) {
            $categoryValue = $this->getStringAttribute($category, $attributeCode);
            if ($categoryValue !== null && trim($categoryValue) !== '') {
                return trim($categoryValue);
            }
        }

        return $globalFallback;
    }

    private function getStringAttribute(object $entity, string $attributeCode): ?string
    {
        if (method_exists($entity, 'getData')) {
            return $this->castToString($entity->getData($attributeCode));
        }

        if (method_exists($entity, 'getCustomAttribute')) {
            $attribute = $entity->getCustomAttribute($attributeCode);
            if ($attribute instanceof AttributeInterface) {
                return $this->castToString($attribute->getValue());
            }
        }

        return null;
    }

    /**
     * @param mixed $value
     */
    private function castToString($value): ?string
    {
        if ($value === null || is_array($value) || is_object($value)) {
            return null;
        }

        return (string) $value;
    }

    private function getAlPagarPrecio(ProductInterface $product): ?float
    {
        $raw = $this->getStringAttribute($product, self::ATTR_AL_PAGAR_PRECIO);
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $value = (float) $raw;

        return $value > 0.0 ? $value : null;
    }

    private function isSuperOferta(ProductInterface $product): bool
    {
        if (!method_exists($product, 'getData')) {
            return false;
        }

        return (bool) $product->getData(self::ATTR_SUPER_OFERTA);
    }

    private function hasActiveSpecialPrice(ProductInterface $product, ?int $storeId): bool
    {
        $specialPrice = $this->getStringAttribute($product, self::ATTR_SPECIAL_PRICE);
        if ($specialPrice === null || trim($specialPrice) === '' || (float) $specialPrice <= 0.0) {
            return false;
        }

        $fromDate = $this->getStringAttribute($product, self::ATTR_SPECIAL_FROM_DATE);
        $toDate   = $this->getStringAttribute($product, self::ATTR_SPECIAL_TO_DATE);

        return $this->timezone->isScopeDateInInterval($storeId, $fromDate, $toDate);
    }

    private function getProductTypeId(ProductInterface $product): string
    {
        if (method_exists($product, 'getTypeId')) {
            return (string) $product->getTypeId();
        }

        return '';
    }
}
