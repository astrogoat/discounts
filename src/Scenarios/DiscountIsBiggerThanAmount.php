<?php

namespace Astrogoat\Discounts\Scenarios;

use Astrogoat\Discounts\Contracts\CanCalculateBuyableDiscounts;
use Closure;
use Money\Money;

class DiscountIsBiggerThanAmount extends Scenario
{
    public function calculate(CanCalculateBuyableDiscounts $discountType, Money $amount, Closure $next)
    {
        if ($amount->lessThan($discountType->calculateDifferenceBetweenCurrentAndNewTier($amount))) {
            $newTierDiscount = $discountType->getNewTier($amount);

            $newTierThresholdAmount = new Money($newTierDiscount['threshold'], $amount->getCurrency());

            return $newTierThresholdAmount->subtract(cart()->getSubtotal());
        }

        return $next([
            'type' => $discountType,
            'amount' => $amount,
        ]);
    }
}
