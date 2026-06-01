<?php

declare(strict_types=1);

namespace LeanCommerce\CustomSpecialPriceColor\Model\Color;

class HexColorNormalizer
{
    private HexColorValidator $validator;

    public function __construct(HexColorValidator $validator)
    {
        $this->validator = $validator;
    }

    public function normalize(?string $color): ?string
    {
        if ($color === null) {
            return null;
        }

        $color = trim($color);

        if ($color === '' || !$this->validator->isValid($color)) {
            return null;
        }

        return strtoupper($color);
    }
}
