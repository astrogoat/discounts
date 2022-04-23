<?php

namespace Astrogoat\Discounts\Types;

use Astrogoat\Cart\Contracts\DiscountType as CartDiscountType;
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
}
