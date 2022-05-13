<?php

namespace Astrogoat\Discounts\Scenarios;

use Astrogoat\Discounts\Contracts\CanCalculateBuyableDiscounts;
use Closure;
use Money\Money;

class NewSubtotalDoesNotQualifyForDiscount extends Scenario
{
    public function calculate(CanCalculateBuyableDiscounts $discountType, Money $amount, Closure $next)
    {
        if ($discountType->newSubtotalDoesNotQualifyForDiscount($amount)) {
            ray('New total price does not qualilfy for discount.');

            return new Money(0, $amount->getCurrency());
        }

        return $next([
            'type' => $discountType,
            'amount' => $amount,
        ]);
    }
}
