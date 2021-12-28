<?php

namespace Astrogoat\Discounts\Http\Livewire\Types;

use Livewire\Component;

abstract class Type extends Component
{
    public function updatedPayload()
    {
        $this->emitTo('astrogoat.discounts.payload', 'newPayload', $this->payload);
        $this->emitTo('helix.lego.apps.livewire.app-edit', 'settingKeyUpdated', ['key' => 'payload', 'value' => $this->payload]);
    }
}
