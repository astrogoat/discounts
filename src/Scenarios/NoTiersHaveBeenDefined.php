<?php

namespace Astrogoat\Discounts\Scenarios;

use Astrogoat\Discounts\Contracts\CanCalculateBuyableDiscounts;
use Closure;
use Money\Money;

class NoTiersHaveBeenDefined extends Scenario
{
    public function calculate(CanCalculateBuyableDiscounts $discountType, Money $amount, Closure $next)
    {
        if ($discountType->countTiers() === 0) {
            return new Money(0, $amount->getCurrency());
        }

        return $next([
            'type' => $discountType,
            'amount' => $amount,
        ]);
    }
}
