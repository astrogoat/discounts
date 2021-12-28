<?php

namespace Astrogoat\Discounts\Types;

class TieredPercentageType extends DiscountType
{
    public function view(): string
    {
        return 'discounts::settings.types.tiered-percentage';
    }

    public static function getId(): string
    {
        return 'tiered_percentage';
    }
}
