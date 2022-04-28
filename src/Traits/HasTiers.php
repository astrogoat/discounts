<?php

namespace Astrogoat\Discounts\Traits;

use Astrogoat\Discounts\Settings\DiscountsSettings;
use Money\Money;

trait HasTiers
{
    private function getHighestValueTier() : array
    {
        $highestTier = collect(settings(DiscountsSettings::class, 'payload.value'))
            ->sortByDesc
            ->value
            ->values()
            ->first();

        return $highestTier ?: [
            'value' => 0,
            'threshold' => 0,
        ];
    }

    private function maxDiscountHasAlreadyBeenAppliedInCart() : bool
    {
        return $this->getHighestValueTier()['value'] == cart()->getDiscountAmount()->getAmount();
    }

    private function findMatchingTier(Money $money) : array
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
