<?php

namespace Astrogoat\Discounts\Http\Livewire\Types;

use Illuminate\Support\Str;
use Livewire\Component;

class TieredPercentage extends Type
{
    public $payload;
    public $displayTiers;

    public function mount()
    {
        if (! isset($this->payload['value'])) {
            $this->payload['value'] = [];
        }

        $this->displayTiers = array_map(function ($tier) {
            $tier['threshold'] = $tier['threshold'] / 100;

            return $tier;
        }, $this->payload['value']);
    }

    public function updatingDisplayTiers($value, $property)
    {
        $value = Str::contains($property, 'threshold') ? (int) $value * 100 : (int) $value;

        data_set($this->payload['value'], $property, $value);

        $this->updatedPayload();
    }

    public function addTier()
    {
        $this->displayTiers[] = [
            'threshold' => 0,
            'value' => 0,
        ];

        $this->payload['value'][]  = [
            'threshold' => 0,
            'value' => 0,
        ];

        $this->updatedPayload();
    }

    public function removeTier($index)
    {
        array_splice($this->displayTiers, $index, 1);
        array_splice($this->payload['value'], $index, 1);
    }

    public function render()
    {
        return view('discounts::components.types.tiered-percentage');
    }
}
