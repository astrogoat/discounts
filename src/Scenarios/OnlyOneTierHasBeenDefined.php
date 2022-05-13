<?php

namespace Astrogoat\Discounts\Scenarios;

use Astrogoat\Discounts\Contracts\CanCalculateBuyableDiscounts;
use Closure;
use Money\Money;

class OnlyOneTierHasBeenDefined extends Scenario
{
    public function calculate(CanCalculateBuyableDiscounts $discountType, Money $amount, Closure $next)
    {
        if ($discountType->countTiers() === 1) {
            ray('// Only one tier has been defined.');

            $tier = $discountType->getNewTier($amount);

            return $discountType->calculateDiscountAmountBasedOnTier($tier, $amount);
        }

        return $next([
            'type' => $discountType,
            'amount' => $amount,
        ]);
    }
}
