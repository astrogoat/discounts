<?php

namespace Astrogoat\Discounts\Scenarios;

use Astrogoat\Discounts\Contracts\CanCalculateBuyableDiscounts;
use Closure;
use Money\Money;

class MaxDiscountAmountForTierHasAlreadyBeenApplied extends Scenario
{
    public function calculate(CanCalculateBuyableDiscounts $discountType, Money $amount, Closure $next)
    {
        if ($discountType->maxDiscountAmountForTierHasAlreadyBeenApplied($amount)) {
            ray('TIER max discount amount has already been applied');

            return new Money(0, $amount->getCurrency());
        }

        return $next([
            'type' => $discountType,
            'amount' => $amount,
        ]);
    }
}
