<?php

namespace Astrogoat\Discounts\Types;

use Astrogoat\Cart\CartItem;
use Astrogoat\Cart\Contracts\Buyable;
use Astrogoat\Cart\Contracts\DiscountType as CartDiscountType;
use Astrogoat\Discounts\Discounts;
use Illuminate\Support\Str;
use Livewire\Component;
use Money\Money;

abstract class DiscountType extends Component implements CartDiscountType
{
    public $payload;

    public function updatedPayload()
    {
        $this->emitTo('astrogoat.discounts.casts.payload', 'payloadHasBeenUpdated', $this->payload);
    }

    public function getTypeName(): string
    {
        return Str::of(class_basename($this))->beforeLast('Type')->headline();
    }

    public function customCanBeApplied(CartItem|Buyable $item): bool|null
    {
        if (! $this->hasCustomCanBeAppliedConstraint()) {
            return null;
        }

        return call_user_func(app(Discounts::class)->getCanBeAppliedConstraint(static::class), $item);
    }

    public function hasCustomCanBeAppliedConstraint(): bool
    {
        return is_callable(app(Discounts::class)->getCanBeAppliedConstraint(static::class));
    }

    public function formatMoney(Money $money): string
    {
        return call_user_func(app(Discounts::class)->getMoneyFormatter(), $money);
    }
}
