<?php

namespace Astrogoat\Discounts\Types;

use Astrogoat\Discounts\Traits\HasTiers;
use Money\Money;

class TieredFixedAmountType extends DiscountType
{
    use HasTiers;

    public function view(): string
    {
        return 'discounts::settings.types.tiered-fixed-amount';
    }

    public static function getId() : string
    {
        return 'tiered_fixed_amount';
    }

    public function calculateDiscountAmount(Money $money) : Money
    {
        $amount = $this->findMatchingTier($money)['value'];

        return new Money($amount, $money->getCurrency());
    }

    public function getValue(Money $money) : int
    {
        return $this->findMatchingTier($money)['value'];
    }

    public function getDisplayValue(Money $money) : mixed
    {
        return $this->calculateDiscountAmount($money);
    }
}
