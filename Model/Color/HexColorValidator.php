<?php

declare(strict_types=1);

namespace LeanCommerce\CustomSpecialPriceColor\Model\Color;

class HexColorValidator
{
    private const HEX_COLOR_PATTERN = '/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/';

    public function isValid(?string $color): bool
    {
        if ($color === null || $color === '') {
            return false;
        }

        return preg_match(self::HEX_COLOR_PATTERN, $color) === 1;
    }
}
