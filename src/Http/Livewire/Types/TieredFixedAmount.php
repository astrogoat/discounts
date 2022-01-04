<?php

namespace Astrogoat\Discounts\Http\Livewire\Types;

class TieredFixedAmount extends Type
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
            $tier['value'] = $tier['value'] / 100;

            return $tier;
        }, $this->payload['value']);
    }

    public function updatingDisplayTiers($value, $property)
    {
        data_set($this->payload['value'], $property, (int) $value * 100);

        $this->updatedPayload();
    }

    public function addTier()
    {
        $this->displayTiers[] = [
            'threshold' => 0,
            'value' => 0,
        ];

        $this->payload['value'][] = [
            'threshold' => 0,
            'value' => 0,
        ];

        $this->updatedPayload();
    }

    public function removeTier($index)
    {
        array_splice($this->displayTiers, $index, 1);
        array_splice($this->payload['value'], $index, 1);

        $this->updatedPayload();
    }

    public function render()
    {
        return view('discounts::components.types.tiered-fixed-amount');
    }
}
