<?php

namespace Astrogoat\Discounts\Types;

class TieredFixedAmountType extends DiscountType
{
    public function view(): string
    {
        return 'discounts::settings.types.tiered-fixed-amount';
    }

    public static function getId(): string
    {
        return 'tiered_fixed_amount';
    }
}
