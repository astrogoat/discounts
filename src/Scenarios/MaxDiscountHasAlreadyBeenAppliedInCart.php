<?php

namespace Astrogoat\Discounts\Scenarios;

use Astrogoat\Discounts\Contracts\CanCalculateBuyableDiscounts;
use Closure;
use Money\Money;

class MaxDiscountHasAlreadyBeenAppliedInCart extends Scenario
{
    public function calculate(CanCalculateBuyableDiscounts $discountType, Money $amount, Closure $next)
    {
        if ($discountType->maxDiscountAmountHasAlreadyBeenAppliedInCart($amount)) {
            return new Money(0, cart()->getCurrency());
        }

        return $next([
            'type' => $discountType,
            'amount' => $amount,
        ]);
    }
}
