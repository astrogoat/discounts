<?php

namespace Astrogoat\Discounts\Types;

use Astrogoat\Cart\CartItem;
use Astrogoat\Discounts\Traits\HasTiers;
use Illuminate\Support\Str;
use Money\Money;

class TieredPercentageType extends DiscountType
{
    use HasTiers;

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

    public function getId(): string
    {
        return 'tiered_percentage';
    }

    public function getTitle() : string
    {
        $percentage = $this->findMatchingTier(cart()->getSubtotal())['value'];

        return "{$percentage}% off";
    }

    public function calculateCartItemDiscountAmount(CartItem $cartItem) : Money
    {
        $percentage = $this->findMatchingTier(cart()->getSubtotal())['value'];

        return $cartItem->getSubtotal()->divide(100)->multiply($percentage);
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

    public function canBeAppliedTo(CartItem $cartItem) : bool
    {
        return true;
    }

    public function render()
    {
        return view('discounts::settings.types.tiered-percentage');
    }
}
