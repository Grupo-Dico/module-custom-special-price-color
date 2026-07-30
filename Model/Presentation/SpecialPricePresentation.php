<?php

declare(strict_types=1);

namespace LeanCommerce\CustomSpecialPriceColor\Model\Presentation;

/**
 * Value object describing how a special price presentation should be rendered.
 */
class SpecialPricePresentation
{
    public const MODE_NONE         = 'none';
    public const MODE_NORMAL       = 'normal';
    public const MODE_SUPER_OFERTA = 'super_oferta';
    public const MODE_THIRD_PRICE  = 'third_price';

    /** @var string */
    private $mode;

    /** @var string|null */
    private $label;

    /** @var string|null */
    private $labelColor;

    /** @var string|null */
    private $priceColor;

    /** @var string|null */
    private $priceBackgroundColor;

    /**
     * @var float|null Informative reference amount rendered independently of the
     * visual mode selected for special_price. NOT transactional.
     */
    private $thirdPriceAmount;

    public function __construct(
        string $mode,
        ?string $label = null,
        ?string $labelColor = null,
        ?string $priceColor = null,
        ?string $priceBackgroundColor = null,
        ?float $thirdPriceAmount = null
    ) {
        $this->mode                 = $mode;
        $this->label                = $label;
        $this->labelColor           = $labelColor;
        $this->priceColor           = $priceColor;
        $this->priceBackgroundColor = $priceBackgroundColor;
        $this->thirdPriceAmount     = $thirdPriceAmount;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getLabelColor(): ?string
    {
        return $this->labelColor;
    }

    public function getPriceColor(): ?string
    {
        return $this->priceColor;
    }

    public function getPriceBackgroundColor(): ?string
    {
        return $this->priceBackgroundColor;
    }

    public function getTextColor(): ?string
    {
        return $this->getPriceColor();
    }

    public function getBackgroundColor(): ?string
    {
        return $this->getPriceBackgroundColor();
    }

    public function getThirdPriceAmount(): ?float
    {
        return $this->thirdPriceAmount;
    }

    public function hasCustomPresentation(): bool
    {
        return $this->mode !== self::MODE_NONE
            && ($this->label !== null
                || $this->labelColor !== null
                || $this->priceColor !== null
                || $this->priceBackgroundColor !== null
                || $this->thirdPriceAmount !== null);
    }

    public function toCacheKeyPart(): string
    {
        return implode('|', [
            $this->mode,
            $this->label ?? '',
            $this->labelColor ?? '',
            $this->priceColor ?? '',
            $this->priceBackgroundColor ?? '',
            $this->thirdPriceAmount !== null ? (string) $this->thirdPriceAmount : '',
        ]);
    }
}
