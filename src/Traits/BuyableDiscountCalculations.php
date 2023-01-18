<?php

namespace Astrogoat\Discounts\Traits;

use Astrogoat\Cart\Contracts\Buyable;
use Astrogoat\Discounts\Scenarios\DiscountIsAvailable;
use Astrogoat\Discounts\Scenarios\DiscountIsBiggerThanAmount;
use Astrogoat\Discounts\Scenarios\MaxDiscountAmountForTierHasAlreadyBeenApplied;
use Astrogoat\Discounts\Scenarios\MaxDiscountHasAlreadyBeenAppliedInCart;
use Astrogoat\Discounts\Scenarios\NewSubtotalDoesNotQualifyForDiscount;
use Astrogoat\Discounts\Scenarios\NoTiersHaveBeenDefined;
use Astrogoat\Discounts\Scenarios\OnlyOneTierHasBeenDefined;
use Astrogoat\Discounts\Settings\DiscountsSettings;
use Illuminate\Pipeline\Pipeline;
use Money\Money;

trait BuyableDiscountCalculations
{
    public static string $defaultBuyableDiscountCalculationRule = 'itemsInCart';
    public $buyableDiscountCalculationRule;

    private function setBuyableDiscountCalculationRule(): void
    {
        $this->buyableDiscountCalculationRule = $this->payload['value']['buyableDiscountCalculationRule']
            ?? self::$defaultBuyableDiscountCalculationRule;
    }

    private function getBuyableDiscountCalculationRule(): string
    {
        $rule = $this->buyableDiscountCalculationRule
            ?: settings(DiscountsSettings::class, 'payload.value.buyableDiscountCalculationRule');

        return $rule ?: self::$defaultBuyableDiscountCalculationRule;
    }

    // I want to create a function that outputs the percent discount that the user would have entered
    // in the discounts setting field. the input gets nested in a payload because it's expecting a tier of discounts per dollar amount.
    // Below , I created a bad function that will extract the value from the settings but it does so poorly.
    // the red flag here is that there is no way for it to understand pricing tiers so it just seeks the first value.
    // HELP
    public function getSettingsInputValue()
    {
        return settings(DiscountsSettings::class, 'payload.value.tiers.0.value');
    }

    public function updatingBuyableDiscountCalculationRule($value): void
    {
        data_set($this->payload['value'], 'buyableDiscountCalculationRule', $value);

        $this->updatedPayload();
    }

    public function getCurrentTier(Money $amount): array
    {
        $subtotal = cart()->getQualifyingItemsForDiscount($this)->subtotal();

        return $this->findMatchingTier($subtotal);
    }

    public function getNewTier(Money $amount): array
    {
        $subtotal = cart()->getQualifyingItemsForDiscount($this)->subtotal()->add($amount);

        return $this->findMatchingTier($subtotal);
    }

    public function buyableDiscountCalculationRuleHelp(): string
    {
        return match ($this->buyableDiscountCalculationRule) {
            'itemsInCart' => 'The discount is calculated based on the items already added to the cart and the item about to be added. If by adding the item to the cart, it will bring the discount into a new tier, the difference between the current tier and the next will be calculated. This will tell the customer exactly what the item will cost if added.',
            'currentTier' => 'The discount is calculated soleley on the price of the item about to be added and the tier that it falls under.',
            'highestTier' => 'The discount is calculated soleley on the price of the item about to be added and the highest tier available. This will always show the max discount possible, eventhough the cart subtotal might not be applicable for it. Use with care.',
            default => '',
        };
    }

    public function calculateBuyableDiscountAmount(Buyable $buyable, int $quantity = 1): Money
    {
        if (! $this->canBeAppliedTo($buyable)) {
            return new Money(0, cart()->getCartCurrency());
        }

        return match ($this->getBuyableDiscountCalculationRule()) {
            'itemsInCart' => $this->calculateDiscountAmountBasedOnItemsInCart($buyable->getBuyablePrice()),
            'currentTier' => $this->calculateDiscountAmountBasedOnCurrentTier($buyable->getBuyablePrice()),
            'highestTier' => $this->calculateDiscountAmountBasedOnHighestTier($buyable->getBuyablePrice()),
        };
    }

    private function calculateDiscountAmountBasedOnCurrentTier(Money $amount): Money
    {
        return $this->calculateDiscountAmountBasedOnTier($this->findMatchingTier($amount), $amount);
    }

    private function calculateDiscountAmountBasedOnHighestTier(Money $amount): Money
    {
        return $this->calculateDiscountAmountBasedOnTier($this->getHighestValueTier(), $amount);
    }

    private function calculateDiscountAmountBasedOnItemsInCart(Money $amount): Money
    {
        $scenarios = [
            NoTiersHaveBeenDefined::class,
            NewSubtotalDoesNotQualifyForDiscount::class,
            MaxDiscountHasAlreadyBeenAppliedInCart::class,
            MaxDiscountAmountForTierHasAlreadyBeenApplied::class,
            OnlyOneTierHasBeenDefined::class,
            DiscountIsBiggerThanAmount::class,
            DiscountIsAvailable::class,
        ];

        return app(Pipeline::class)->send([
            'type' => $this,
            'amount' => $amount,
        ])->through($scenarios)->then(function ($discountAmount) {
            return $discountAmount;
        });
    }

    public function newSubtotalDoesNotQualifyForDiscount(Money $amount): bool
    {
        return $this->getNewTier($amount)['value'] == 0;
    }
}
