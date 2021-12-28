<?php

namespace Astrogoat\Discounts\Traits;

use Astrogoat\Discounts\Settings\DiscountsSettings;
use Money\Money;

trait HasTiers
{
    private function findMatchingTier(Money $money)
    {
        $tiers = collect(settings(DiscountsSettings::class, 'payload.value'))
            ->sortByDesc
            ->threshold
            ->values();

        foreach ($tiers as $tier) {
            if ($money->getAmount() >= $tier['threshold']) {
                return $tier;
            }
        }

        return [
            'value' => 0,
            'threshold' => 0,
        ];
    }
}
