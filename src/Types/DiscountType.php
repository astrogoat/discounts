<?php

namespace Astrogoat\Discounts\Types;

use Astrogoat\Cart\Discount;
use Astrogoat\Cart\CartItem;
use Illuminate\Support\Str;
use Money\Money;

abstract class DiscountType
{
    abstract public function view(): string;

    abstract public static function getId(): string;

    abstract public function calculateDiscountAmount(Money $money): Money;

    abstract public function getValue(Money $money): int;

    abstract public function getDisplayValue(Money $money): mixed;

    abstract public function createCartDiscount(CartItem $cartItem): Discount|null;

    public function getName(): string
    {
        return Str::of(class_basename($this))->beforeLast('Type')->headline();
    }
}
