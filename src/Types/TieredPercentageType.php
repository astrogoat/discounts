<?php

namespace Astrogoat\Discounts\Types;

use Astrogoat\Cart\CartItem;
use Astrogoat\Cart\Contracts\Buyable;
use Astrogoat\Discounts\Contracts\CanCalculateBuyableDiscounts;
use Astrogoat\Discounts\Traits\BuyableDiscountCalculations;
use Astrogoat\Discounts\Traits\HasTiers;
use Astrogoat\Discounts\Traits\TitleDisplayType;
use Illuminate\Support\Str;
use Money\Money;

class TieredPercentageType extends DiscountType implements CanCalculateBuyableDiscounts
{
    use HasTiers;
    use BuyableDiscountCalculations;
    use TitleDisplayType;

    public $displayTiers;

    public function mount()
    {
        if (! isset($this->payload['value']['tiers'])) {
            $this->payload['value'] = [];
        }

        $this->displayTiers = array_map(function ($tier) {
            $tier['threshold'] = $tier['threshold'] / 100;

            return $tier;
        }, $this->payload['value']['tiers'] ?? []);

        $this->setBuyableDiscountCalculationRule();
        $this->setTitleDisplayType();
    }

    public function getId(): string
    {
        return 'tiered_percentage';
    }

    public function getTitle(string $type = 'default'): string
    {
        $qualifyingItemsSubtotal = cart()->getQualifyingItemsForDiscount($this)->subtotal();

        $this->buyableDiscountCalculationRule = 'currentTier';

        return $this->getTitleBasedOnAmount($qualifyingItemsSubtotal);
    }

    public function getTitleBasedOnAmount(Money $amount, string $type = 'default'): string
    {
        $this->setTitleDisplayType();

        if ($type !== 'percentage' && ($this->getTitleDisplayType() == 'amount' || $type == 'amount')) {
            $amount = match ($this->getBuyableDiscountCalculationRule()) {
                'itemsInCart' => $this->calculateDiscountAmountBasedOnItemsInCart($amount),
                'currentTier' => $this->calculateDiscountAmountBasedOnCurrentTier($amount),
                'highestTier' => $this->calculateDiscountAmountBasedOnHighestTier($amount),
            };

            return $this->formatMoney($amount);
        }

        // If not "amount" then it is "percentage".
        $value = match ($this->getBuyableDiscountCalculationRule()) {
            'itemsInCart' => $this->calculateDiscountPercentageBasedOnItemsInCart($amount),
            'currentTier' => $this->calculateDiscountPercentageBasedOnCurrentTier($amount),
            'highestTier' => $this->calculateDiscountPercentageBasedOnHighestTier($amount),
        };

        return $value . '%';
    }

    public function calculateCartItemDiscountAmount(CartItem $cartItem): Money
    {
        if (! $this->canBeAppliedTo($cartItem)) {
            return new Money(0, $cartItem->price->getCurrency());
        }

        $percentage = $this->findMatchingTier(cart()->getSubtotal())['value'];

        return $cartItem->getSubtotal()->divide(100)->multiply($percentage);
    }

    public function updatingDisplayTiers($value, $property)
    {
        $value = Str::contains($property, 'threshold') ? (int) $value * 100 : (int) $value;

        data_set($this->payload['value']['tiers'], $property, $value);

        $this->updatedPayload();
    }

    public function canBeAppliedTo(CartItem|Buyable $item): bool
    {
        if ($item instanceof CartItem) {
            return ! $item->getSubtotal()->isZero();
        }

        return ! $item->getBuyablePrice()->isZero();
    }

    public function render()
    {
        return view('discounts::settings.types.tiered-percentage');
    }

    // private function calculateDiscountAmountBasedOnItemsInCart(Money $amount): Money
    // {
    //     $qualifyingCartSubtotal = cart()->getQualifyingItemsForDiscount($this)->subtotal();
    //     $newTierDiscount = $this->findMatchingTier($qualifyingCartSubtotal->add($amount));
    //
    //     // == Scenarios ==
    //     // 1. New total price does not qualilfy for discount = $0
    //     if ($newTierDiscount['value'] == 0) {
    //         return new Money(0, $amount->getCurrency());
    //     }
    //
    //     // 2. Max discount has already been applied = $0
    //     if ($this->maxDiscountHasAlreadyBeenAppliedInCart()) {
    //         return new Money(0, $amount->getCurrency());
    //     }
    //
    //     $currentTierDiscount = $this->findMatchingTier($qualifyingCartSubtotal);
    //     $diffBetweenCurrentAndNewTierInPercentage = $newTierDiscount['value'] - $currentTierDiscount['value'];
    //     $diffBetweenCurrentAndNewTierAmount = new Money($diffBetweenCurrentAndNewTierInPercentage / 100 * $amount->getAmount(), $amount->getCurrency());
    //
    //     // 3. New tier and current tier is the same. Only tier defined.
    //     if ($currentTierDiscount['value'] === $newTierDiscount['value']) {
    //         ray(3);
    //
    //         // @TODO
    //         return new Money(0, $amount->getCurrency());
    //         // return new Money($currentTierDiscount['value'], $amount->getCurrency());
    //     }
    //
    //     // 4. Discount is bigger than buyable price.
    //     if ($amount->lessThan($diffBetweenCurrentAndNewTierAmount)) {
    //         return $amount;
    //     }
    //
    //     // 5. Discount is available = diff between currently applied discount and new tier.
    //     return $diffBetweenCurrentAndNewTierAmount;
    // }

    // private function calculateDiscountAmountBasedOnCurrentTier(Money $amount): Money
    // {
    //     return new Money($this->findMatchingTier($amount)['value'] / 100 * $amount->getAmount(), $amount->getCurrency());
    // }
    //
    // private function calculateDiscountAmountBasedOnHighestTier(Money $amount): Money
    // {
    //     return new Money($this->getHighestValueTier()['value'] / 100 * $amount->getAmount(), $amount->getCurrency());
    // }

    // private function calculateDiscountPercentageBasedOnItemsInCart(Money $amount): int
    // {
    //     $newTierDiscount = $this->findMatchingTier(cart()->getSubtotal()->add($amount));
    //
    //     // == Scenarios ==
    //     // 1. New total price does not qualilfy for discount = $0
    //     if ($newTierDiscount['value'] == 0) {
    //         // ray(1);
    //         return 0;
    //     }
    //
    //     // 2. Max discount has already been applied = $0
    //     if ($this->maxDiscountHasAlreadyBeenAppliedInCart()) {
    //         // ray(2);
    //         return 0;
    //     }
    //
    //     $currentTierDiscount = $this->findMatchingTier(cart()->getSubtotal());
    //     $diffBetweenCurrentAndNewTierInPercentage = $newTierDiscount['value'] - $currentTierDiscount['value'];
    //     $diffBetweenCurrentAndNewTierAmount = new Money($diffBetweenCurrentAndNewTierInPercentage / 100 * $amount->getAmount(), $amount->getCurrency());
    //
    //     // 3. Discount is bigger than buyable price.
    //     if ($amount->lessThan($diffBetweenCurrentAndNewTierAmount)) {
    //         return 100;
    //     }
    //
    //     // ray(4);
    //     // 4. Discount is available = diff between currently applied discount and new tier.
    //     return $diffBetweenCurrentAndNewTierInPercentage;
    // }

    private function calculateDiscountPercentageBasedOnCurrentTier(Money $amount): int
    {
        return $this->findMatchingTier($amount)['value'];
    }

    private function calculateDiscountPercentageBasedOnHighestTier(Money $amount): int
    {
        return $this->getHighestValueTier()['value'];
    }

    public function calculateDiscountAmountBasedOnTier(array $tier, Money $amount): Money
    {
        return new Money((int) number_format($tier['value'] / 100 * $amount->getAmount(), 0, '.', ''), $amount->getCurrency());
    }

    public function maxDiscountAmountHasAlreadyBeenAppliedInCart(Money $amount): bool
    {
        $discountAmount = $this->calculateDiscountAmountBasedOnTier($this->getHighestValueTier(), $amount);

        return cart()->getDiscountAmount()->greaterThanOrEqual($discountAmount);
    }

    public function maxDiscountAmountForTierHasAlreadyBeenApplied(Money $amount): bool
    {
        $newTier = $this->getNewTier($amount);
        $currentTier = $this->getCurrentTier($amount);

        if ($newTier['value'] !== $currentTier['value']) {
            return false;
        }

        $discountAmount = $this->calculateDiscountAmountBasedOnTier($this->getHighestValueTier(), $amount);

        return cart()->getDiscountAmount()->greaterThanOrEqual($discountAmount);
    }

    public function calculateDifferenceBetweenCurrentAndNewTier(Money $amount): Money
    {
        $currentTier = $this->getCurrentTier($amount);
        $newTier = $this->getNewTier($amount);
        $diffBetweenCurrentAndNewTierInPercentage = $newTier['value'] - $currentTier['value'];

        return new Money($diffBetweenCurrentAndNewTierInPercentage / 100 * $amount->getAmount(), $amount->getCurrency());
    }
}
