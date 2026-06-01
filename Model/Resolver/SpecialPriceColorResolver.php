<?php

declare(strict_types=1);

namespace LeanCommerce\CustomSpecialPriceColor\Model\Resolver;

use LeanCommerce\CustomSpecialPriceColor\Api\SpecialPriceColorResolverInterface;
use LeanCommerce\CustomSpecialPriceColor\Model\Color\HexColorNormalizer;
use LeanCommerce\CustomSpecialPriceColor\Model\Config;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\AttributeInterface;

class SpecialPriceColorResolver implements SpecialPriceColorResolverInterface
{
    private const ATTRIBUTE_CODE = 'special_price_color';

    private Config $config;

    private HexColorNormalizer $normalizer;

    public function __construct(
        Config $config,
        HexColorNormalizer $normalizer
    ) {
        $this->config = $config;
        $this->normalizer = $normalizer;
    }

    public function resolve(
        ProductInterface $product,
        ?CategoryInterface $category = null,
        ?int $storeId = null
    ): ?string {
        if (!$this->config->isEnabled($storeId)) {
            return null;
        }

        $productColor = $this->normalizer->normalize($this->getAttributeValue($product));
        if ($productColor !== null) {
            return $productColor;
        }

        if ($category !== null) {
            $categoryColor = $this->normalizer->normalize($this->getAttributeValue($category));
            if ($categoryColor !== null) {
                return $categoryColor;
            }
        }

        return $this->normalizer->normalize($this->config->getDefaultColor($storeId));
    }

    private function getAttributeValue(object $entity): ?string
    {
        if (method_exists($entity, 'getData')) {
            $value = $entity->getData(self::ATTRIBUTE_CODE);

            return $this->normalizeAttributeValue($value);
        }

        if (method_exists($entity, 'getCustomAttribute')) {
            $attribute = $entity->getCustomAttribute(self::ATTRIBUTE_CODE);

            if ($attribute instanceof AttributeInterface) {
                $value = $attribute->getValue();

                return $this->normalizeAttributeValue($value);
            }
        }

        return null;
    }

    /**
     * @param mixed $value
     */
    private function normalizeAttributeValue($value): ?string
    {
        if ($value === null || is_array($value) || is_object($value)) {
            return null;
        }

        return (string) $value;
    }
}
