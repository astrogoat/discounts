<?php

namespace Astrogoat\Discounts\Types;

use Astrogoat\Cart\CartItem;
use Astrogoat\Cart\Contracts\Buyable;
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

    public function getTitle(): string
    {
        $amount = $amount ?? cart()->getSubtotal();

        $percentage = $this->findMatchingTier($amount)['value'];

        return "{$percentage}%";
    }

    public function getTitleBasedOnAmount(Money $amount): string
    {
        // @TODO
        return '';
    }

    public function calculateCartItemDiscountAmount(CartItem $cartItem): Money
    {
        if (! $this->canBeAppliedTo($cartItem)) {
            return new Money(0, $cartItem->price->getCurrency());
        }

        $percentage = $this->findMatchingTier(cart()->getSubtotal())['value'];

        return $cartItem->getSubtotal()->divide(100)->multiply($percentage);
    }

    public function calculateBuyableDiscountAmount(Buyable $buyable): Money
    {
        $newTierDiscount = $this->findMatchingTier(cart()->getSubtotal()->add($buyable->getBuyablePrice()));

        // == Scenarios ==
        // 1. New total price does not qualilfy for discount = $0
        if ($newTierDiscount['value'] == 0) {
            return new Money(0, cart()->getCartCurrency());
        }

        // 2. Max discount has already been applied = $0
        if ($this->maxDiscountHasAlreadyBeenAppliedInCart()) {
            return new Money(0, cart()->getCartCurrency());
        }

        // 3. Discount is available = diff between currently applied discount and new tier.
        $currentTierDiscount = $this->findMatchingTier(cart()->getSubtotal());
        $diffBetweenCurrentAndNewTierInPercentage = $newTierDiscount['value'] - $currentTierDiscount['value'];

        return new Money($diffBetweenCurrentAndNewTierInPercentage / 100 * $buyable->getBuyablePrice()->getAmount(), cart()->getCartCurrency());

        return $buyable->getBuyablePrice()->subtract($discountedAmount);
        ray($currentTierDiscount, $newTierDiscount, $diffBetweenCurrentAndNewTierInPercentage);

        return new Money($diffBetweenCurrentAndNewTierInPercentage, cart()->getCartCurrency());
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

    public function canBeAppliedTo(CartItem|Buyable $item): bool
    {
        if ($item instanceof CartItem) {
            return ! $item->getSubtotal()->isZero();
        }

        return $item->getBuyablePrice()->isZero();
    }

    public function render()
    {
        return view('discounts::settings.types.tiered-percentage');
    }
}
