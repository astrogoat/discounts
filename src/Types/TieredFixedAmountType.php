<?php

namespace Astrogoat\Discounts\Types;

use Astrogoat\Cart\CartItem;
use Astrogoat\Cart\Contracts\Buyable;
use Astrogoat\Discounts\Contracts\CanCalculateBuyableDiscounts;
use Astrogoat\Discounts\Traits\BuyableDiscountCalculations;
use Astrogoat\Discounts\Traits\HasTiers;
use Illuminate\Contracts\View\View;
use Money\Money;

class TieredFixedAmountType extends DiscountType implements CanCalculateBuyableDiscounts
{
    use HasTiers;
    use BuyableDiscountCalculations;

    public $displayTiers;

    public function getId(): string
    {
        return 'tiered_fixed_amount';
    }

    public function getTitle(string $type = 'default'): string
    {
        $qualifyingItemsSubtotal = cart()->getQualifyingItemsForDiscount($this)->reduce(function (Money $carry, CartItem $cartItem) {
            return $carry->add($cartItem->getSubtotal());
        }, new Money(0, cart()->getCartCurrency()));

        $this->buyableDiscountCalculationRule = 'currentTier';

        return $this->getTitleBasedOnAmount($qualifyingItemsSubtotal);
    }

    public function getTitleBasedOnAmount(Money $amount, string $type = 'default'): string
    {
        $amount = match ($this->getBuyableDiscountCalculationRule()) {
            'itemsInCart' => $this->calculateDiscountAmountBasedOnItemsInCart($amount),
            'currentTier' => $this->calculateDiscountAmountBasedOnCurrentTier($amount),
            'highestTier' => $this->calculateDiscountAmountBasedOnHighestTier($amount),
        };

        return $this->formatMoney($amount);
    }

    public function calculateCartItemDiscountAmount(CartItem $cartItem): Money
    {
        if (! $this->canBeAppliedTo($cartItem)) {
            return new Money(0, $cartItem->price->getCurrency());
        }

        $qualifyingCartItems = $cartItem
            ->getCart()
            ->getQualifyingItemsForDiscount($this);

        $amount = $qualifyingCartItems
            ->reduce(function (Money $carry, CartItem $cartItem) {
                return $carry->add($cartItem->getSubtotal());
            }, new Money(0, cart()->getCartCurrency()));

        $discountAmount = $this->findMatchingTier($amount)['value'];

        $totalQualifyingCartQuantity = $qualifyingCartItems->sum(fn (CartItem $collectionCartItem) => $collectionCartItem->getQuantity());
        $discountedAmount = $discountAmount / $totalQualifyingCartQuantity * $cartItem->getQuantity();

        return new Money($discountedAmount, cart()->getCartCurrency());
    }

    /**
     * Mount the settings Livewire component
     */
    public function mount()
    {
        if (! isset($this->payload['value']['tiers'])) {
            $this->payload['value']['tiers'] = [];
        }

        $this->displayTiers = array_map(function ($tier) {
            $tier['threshold'] = $tier['threshold'] / 100;
            $tier['value'] = $tier['value'] / 100;

            return $tier;
        }, $this->payload['value']['tiers'] ?? []);

        $this->setBuyableDiscountCalculationRule();
    }

    public function updatingDisplayTiers($value, $property): void
    {
        data_set($this->payload['value']['tiers'], $property, (int) $value * 100);

        $this->updatedPayload();
    }

    public function canBeAppliedTo(CartItem|Buyable $item): bool
    {
        if ($this->hasCustomCanBeAppliedConstaint()) {
            return $this->customCanBeApplied($item);
        }

        if ($item instanceof CartItem) {
            return ! $item->getSubtotal()->isZero();
        }

        if ($item->getBuyablePrice()->isZero()) {
            return false;
        }

        return true;
    }

    // private function calculateDiscountAmountBasedOnItemsInCart(Money $amount): Money
    // {
    //     $scenarios = [
    //         NoTiersHaveBeenDefined::class,
    //         NewSubtotalDoesNotQualifyForDiscount::class,
    //         MaxDiscountHasAlreadyBeenAppliedInCart::class,
    //         MaxDiscountAmountForTierHasAlreadyBeenApplied::class,
    //         OnlyOneTierHasBeenDefined::class,
    //         DiscountIsBiggerThanAmount::class,
    //         DiscountIsAvailable::class,
    //     ];
    //
    //     return app(Pipeline::class)
    //         ->send([
    //             'type' => $this,
    //             'amount' => $amount,
    //         ])
    //         ->through($scenarios)
    //         ->then(function ($discountAmount) {
    //             return $discountAmount;
    //         });
    //
    //     // // No tiers have been defined.
    //     // if ($this->countTiers() === 0) {
    //     //     ray('// No tiers have been defined');
    //     //     return new Money(0, $amount->getCurrency());
    //     // }
    //
    //     // == Scenarios ==
    //     // $newTierDiscount = $this->getNewTier($amount);
    //     //
    //     // // New total price does not qualilfy for discount.
    //     // if ($newTierDiscount['value'] == 0) {
    //     //     return new Money(0, cart()->getCartCurrency());
    //     // }
    //
    //     // // Max discount has already been applied = $0
    //     // if ($this->maxDiscountHasAlreadyBeenAppliedInCart()) {
    //     //     return new Money(0, cart()->getCartCurrency());
    //     // }
    //
    //     // Only one tier has been defined.
    //     // if ($this->countTiers() === 1) {
    //     //     ray('// Only one tier has been defined.');
    //     //     return $this->calculateTieredDiscountAmount($amount);
    //     // }
    //
    //     // $currentTierDiscount = $this->getCurrentTier($amount);
    //     // $diffBetweenCurrentAndNewTier = new Money($newTierDiscount['value'] - $currentTierDiscount['value'], cart()->getCartCurrency());
    //     //
    //     // // 4. Discount is bigger than buyable price.
    //     // if ($amount->lessThan($diffBetweenCurrentAndNewTier)) {
    //     //     $newTierThresholdAmount = new Money($newTierDiscount['threshold'], cart()->getCartCurrency());
    //     //
    //     //     return $newTierThresholdAmount->subtract(cart()->getSubtotal());
    //     // }
    //
    //     // 5. Discount is available = diff between currently applied discount and new tier.
    //     // return $diffBetweenCurrentAndNewTier;
    // }

    public function calculateDiscountAmountBasedOnTier(array $tier, Money $amount): Money
    {
        return new Money($tier['value'], $amount->getCurrency());
    }

    public function calculateDifferenceBetweenCurrentAndNewTier(Money $amount): Money
    {
        return new Money($this->getNewTier($amount)['value'] - $this->getCurrentTier($amount)['value'], cart()->getCartCurrency());
    }

    public function maxDiscountAmountHasAlreadyBeenAppliedInCart(Money $amount): bool
    {
        return $this->getHighestValueTier()['value'] == cart()->getDiscountAmount()->getAmount();
    }

    public function maxDiscountAmountForTierHasAlreadyBeenApplied(Money $amount): bool
    {
        $newTier = $this->getNewTier($amount);
        $currentTier = $this->getCurrentTier($amount);

        if ($newTier['value'] !== $currentTier['value']) {
            return false;
        }

        return $newTier['value'] == cart()->getDiscountAmount()->getAmount();
    }

    /**
     * Render the settings view.
     */
    public function render(): View
    {
        return view('discounts::settings.types.tiered-fixed-amount');
    }
}
