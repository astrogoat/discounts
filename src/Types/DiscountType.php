<?php

namespace Astrogoat\Discounts\Types;

use Astrogoat\Cart\CartItem;
use Astrogoat\Cart\Contracts\Buyable;
use Astrogoat\Cart\Contracts\DiscountType as CartDiscountType;
use Astrogoat\Discounts\Discounts;
use Illuminate\Support\Str;
use Livewire\Component;

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

    public function canBeApplied(CartItem|Buyable $item) : ?bool
    {
        $discounts = app(Discounts::class);

        $callback = $discounts->getCanBeAppliedConstraint(static::class);

        if ($callback) {
            return call_user_func($callback, $item);
        }

        return null;
    }
}
