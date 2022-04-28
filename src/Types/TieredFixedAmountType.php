<?php

namespace Astrogoat\Discounts\Types;

use Astrogoat\Cart\CartItem;
use Astrogoat\Cart\Contracts\Buyable;
use Astrogoat\Discounts\Settings\DiscountsSettings;
use Astrogoat\Discounts\Traits\HasTiers;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use NumberFormatter;

class TieredFixedAmountType extends DiscountType
{
    use HasTiers;

    public const DEFAULT_BUYABLE_DISCOUNT_CALCULATION_RULE = 'itemsInCart';

    public $displayTiers;
    public $buyableDiscountCalculationRule;

    public function getId(): string
    {
        return 'tiered_fixed_amount';
    }

    public function getTitle(): string
    {
        $qualifyingItemsSubtotal = cart()->getQualifyingItemsForDiscount($this)->reduce(function (Money $carry, CartItem $cartItem) {
            return $carry->add($cartItem->getSubtotal());
        }, new Money(0, cart()->getCartCurrency()));

        $this->buyableDiscountCalculationRule = 'currentTier';

        return $this->getTitleBasedOnAmount($qualifyingItemsSubtotal);
    }

    public function getTitleBasedOnAmount(Money $amount): string
    {
        $amount = match ($this->getBuyableDiscountCalculationRule()) {
            'itemsInCart' => $this->calculateDiscountAmountBasedOnItemsInCart($amount),
            'currentTier' => $this->calculateDiscountAmountBasedOnCurrentTier($amount),
            'highestTier' => $this->calculateDiscountAmountBasedOnHighestTier($amount),
            default => $this->calculateDiscountAmountBasedOnItemsInCart($amount),
        };

        $amount = $amount->isZero() ? $amount : $amount->roundToUnit(1);
        $numberFormatter = new NumberFormatter(app('lego')->getLocale(), NumberFormatter::CURRENCY);
        $decimals = (substr($amount->getAmount(), -2) > 0) ? 2 : 0;
        $numberFormatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $decimals);
        $moneyFormatter = new IntlMoneyFormatter($numberFormatter, new ISOCurrencies());

        return $moneyFormatter->format($amount);
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

    public function calculateBuyableDiscountAmount(Buyable $buyable, int $quantity = 1): Money
    {
        if (! $this->canBeAppliedTo($buyable)) {
            return new Money(0, cart()->getCartCurrency());
        }

        return match ($this->getBuyableDiscountCalculationRule()) {
            'currentTier' => $this->calculateDiscountAmountBasedOnCurrentTier($buyable->getBuyablePrice(), $quantity),
            'highestTier' => $this->calculateDiscountAmountBasedOnHighestTier($buyable->getBuyablePrice(), $quantity),
            default => $this->calculateDiscountAmountBasedOnItemsInCart($buyable->getBuyablePrice()),
        };
    }

    public function mount()
    {
        if (! isset($this->payload['value']['tiers'])) {
            $this->payload['value']['tiers'] = [];
        }

        $this->displayTiers = array_map(function ($tier) {
            $tier['threshold'] = $tier['threshold'] / 100;
            $tier['value'] = $tier['value'] / 100;

            return $tier;
        }, $this->payload['value']['tiers']);

        $this->buyableDiscountCalculationRule = $this->payload['value']['buyableDiscountCalculationRule'] ?? self::DEFAULT_BUYABLE_DISCOUNT_CALCULATION_RULE;
    }

    public function updatingDisplayTiers($value, $property)
    {
        data_set($this->payload['value']['tiers'], $property, (int) $value * 100);

        $this->updatedPayload();
    }

    public function updatingbuyableDiscountCalculationRule($value)
    {
        data_set($this->payload['value'], 'buyableDiscountCalculationRule', $value);

        $this->updatedPayload();
    }

    public function addTier()
    {
        $this->displayTiers[] = [
            'threshold' => 0,
            'value' => 0,
        ];

        $this->payload['value']['tiers'][] = [
            'threshold' => 0,
            'value' => 0,
        ];

        $this->updatedPayload();
    }

    public function removeTier($index)
    {
        array_splice($this->displayTiers, $index, 1);
        array_splice($this->payload['value']['tiers'], $index, 1);

        $this->updatedPayload();
    }

    public function canBeAppliedTo(CartItem|Buyable $item): bool
    {
        if (! is_null($customCanBeApplied = $this->canBeApplied($item))) {
            return $customCanBeApplied;
        }

        if ($item instanceof CartItem) {
            return ! $item->getSubtotal()->isZero();
        }

        if ($item->getBuyablePrice()->isZero()) {
            return false;
        }

        return true;
    }

    public function render()
    {
        return view('discounts::settings.types.tiered-fixed-amount');
    }

    private function calculateDiscountAmountBasedOnItemsInCart(Money $amount): Money
    {
        $newTierDiscount = $this->findMatchingTier(cart()->getSubtotal()->add($amount));

        // == Scenarios ==
        // 1. New total price does not qualilfy for discount = $0
        if ($newTierDiscount['value'] == 0) {
            ray(1);

            return new Money(0, cart()->getCartCurrency());
        }

        // 2. Max discount has already been applied = $0
        if ($this->maxDiscountHasAlreadyBeenAppliedInCart()) {
            ray(2);

            return new Money(0, cart()->getCartCurrency());
        }

        $currentTierDiscount = $this->findMatchingTier(cart()->getSubtotal());
        $diffBetweenCurrentAndNewTier = new Money($newTierDiscount['value'] - $currentTierDiscount['value'], cart()->getCartCurrency());

        ray($currentTierDiscount, $diffBetweenCurrentAndNewTier)->green();

        // 3. Discount is bigger than buyable price.
        if ($amount->lessThan($diffBetweenCurrentAndNewTier)) {
            ray(3);
            $newTierThresholdAmount = new Money($newTierDiscount['threshold'], cart()->getCartCurrency());

            return $newTierThresholdAmount->subtract(cart()->getSubtotal());
        }

        // 4. Discount is available = diff between currently applied discount and new tier.
        ray(4);

        return $diffBetweenCurrentAndNewTier;
    }

    private function calculateDiscountAmountBasedOnCurrentTier(Money $amount)
    {
        return new Money($this->findMatchingTier($amount)['value'], $amount->getCurrency());
    }

    private function calculateDiscountAmountBasedOnHighestTier(Money $amount)
    {
        return new Money($this->getHighestValueTier()['value'], $amount->getCurrency());
    }

    private function getBuyableDiscountCalculationRule(): string
    {
        return $this->buyableDiscountCalculationRule
            ?: settings(DiscountsSettings::class, 'payload.value.buyableDiscountCalculationRule');
    }

    public function buyableDiscountCalculationRuleHelp()
    {
        return match ($this->buyableDiscountCalculationRule) {
            'itemsInCart' => 'The discount is calculated based on the items already added to the cart and the item about to be added. If by adding the item to the cart, it will bring the discount into a new tier, the difference between the current tier and the next will be calculated. This will tell the customer exactly what the item will cost if added.',
            'currentTier' => 'The discount is calculated soley on the price of the item about to be added and the tier that it falls under.',
            'highestTier' => 'The discount is calculated soley on the price of the item about to be added and the highest tier available. This will always show the max discount possible, eventhough the cart subtotal might not be applicable for it. Use with care.',
            default => '',
        };
    }
}
