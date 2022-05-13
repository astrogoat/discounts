<?php

namespace Astrogoat\Discounts\Contracts;

use Money\Money;

interface CanCalculateBuyableDiscounts
{
    public function countTiers(): int;

    public function newSubtotalDoesNotQualifyForDiscount(Money $amount): bool;

    public function maxDiscountAmountHasAlreadyBeenAppliedInCart(Money $amount): bool;

    public function maxDiscountAmountForTierHasAlreadyBeenApplied(Money $amount): bool;

    public function calculateDiscountAmountBasedOnTier(array $tier, Money $amount): Money;

    public function getCurrentTier(Money $amount): array;

    public function getNewTier(Money $amount): array;

    public function calculateDifferenceBetweenCurrentAndNewTier(Money $amount): Money;
}
