<?php

namespace Astrogoat\Discounts\Scenarios;

use Astrogoat\Discounts\Contracts\CanCalculateBuyableDiscounts;
use Closure;
use Money\Money;

class DiscountIsAvailable extends Scenario
{
    public function calculate(CanCalculateBuyableDiscounts $discountType, Money $amount, Closure $next)
    {
        return $discountType->calculateDifferenceBetweenCurrentAndNewTier($amount);
    }
}
