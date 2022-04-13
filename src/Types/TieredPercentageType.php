<?php

namespace Astrogoat\Discounts\Types;

use Astrogoat\Cart\CartItem;
use Astrogoat\Cart\Discount;
use Astrogoat\Discounts\Traits\HasTiers;
use Money\Money;

class TieredPercentageType extends DiscountType
{
    use HasTiers;

    public function view(): string
    {
        return 'discounts::settings.types.tiered-percentage';
    }

    public static function getId(): string
    {
        return 'tiered_percentage';
    }

    public function calculateDiscountAmount(Money $money): Money
    {
        $percentage = $this->findMatchingTier($money)['value'];

        $amount = $money->getAmount() * $percentage / 100;

        return new Money($amount, $money->getCurrency());
    }

    public function getValue(Money $money): int
    {
        return $this->findMatchingTier($money)['value'];
    }

    public function getDisplayValue(Money $money): mixed
    {
        return $this->getValue($money) . '%';
    }

    public function createCartDiscount(CartItem $cartItem): Discount
    {
        return Discount::percentage($this->getValue(cart()->getSubtotal()));
    }
}
