<?php

namespace Astrogoat\Discounts\Http\Livewire\Types;

use Livewire\Component;

abstract class Type extends Component
{
    public function updatedPayload()
    {
        $this->emitTo('astrogoat.discounts.casts.payload', 'payloadHasBeenUpdated', $this->payload);
    }
}
