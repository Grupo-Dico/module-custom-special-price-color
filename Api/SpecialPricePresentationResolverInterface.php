<?php

declare(strict_types=1);

namespace LeanCommerce\CustomSpecialPriceColor\Api;

use LeanCommerce\CustomSpecialPriceColor\Model\Presentation\SpecialPricePresentation;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Resolves the full visual presentation for a product's special price.
 *
 * Priority order (highest to lowest):
     *   1. super_oferta = 1 + active special_price => MODE_SUPER_OFERTA
     *   2. al_pagar_precio > 0                     => MODE_THIRD_PRICE
     *   3. active special_price                    => MODE_NORMAL (product > category > global > theme)
 *   4. no special_price                 => MODE_NONE
 */
interface SpecialPricePresentationResolverInterface
{
    public function resolve(
        ProductInterface $product,
        ?CategoryInterface $category = null,
        ?int $storeId = null
    ): SpecialPricePresentation;
}
