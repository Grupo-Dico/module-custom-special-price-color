<?php

declare(strict_types=1);

namespace LeanCommerce\CustomSpecialPriceColor\Api;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;

interface SpecialPriceColorResolverInterface
{
    public function resolve(
        ProductInterface $product,
        ?CategoryInterface $category = null,
        ?int $storeId = null
    ): ?string;
}
