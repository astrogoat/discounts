<?php

namespace Astrogoat\Discounts\Types;

use Astrogoat\Cart\CartItem;
use Astrogoat\Cart\Contracts\Buyable;
use Astrogoat\Discounts\Traits\HasTiers;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use NumberFormatter;

class TieredFixedAmountType extends DiscountType
{
    use HasTiers;

    public $displayTiers;

    public function getId(): string
    {
        return 'tiered_fixed_amount';
    }

    public function getTitle(): string
    {
        $amount = new Money($this->findMatchingTier(cart()->getTotalDiscountAmountFor($this))['value'], cart()->getCartCurrency());
        ray($amount)->red();

        return $this->getTitleBasedOnAmount($amount);
    }

    public function getTitleBasedOnAmount(Money $amount) : string
    {
        $amount = new Money($this->findMatchingTier($amount)['value'], cart()->getCartCurrency());
        ray($amount)->red();
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
        // ** EXAMPLE **
        // Item 1
        // - Quantity: 2
        // - Unit price: $100
        // - Subtotal: $200
        // - Discount: (100 / 3) * 2 = 66.67
        // - Total: 200 - 66.67 = 133.33

        // Item 2
        // - Quantity: 1
        // - Unit price: $50
        // - Subtotal: $50
        // - Discount: (100 / 3) * 1 = 33.33
        // - Total: 50 - 33.33 = 16.67

        // SUBTOTAL: 250
        // DISCOUNT: 100
        // TOTAL: 150
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

        $newTierDiscount = $this->findMatchingTier(cart()->getSubtotal()->add($buyable->getBuyablePrice()));

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

        // 3. Discount is bigger than buyable price.
        if ($buyable->getBuyablePrice()->lessThan($diffBetweenCurrentAndNewTier)) {
            ray(3);
            $newTierThresholdAmount = new Money($newTierDiscount['threshold'], cart()->getCartCurrency());

            return $newTierThresholdAmount->subtract(cart()->getSubtotal());
        }

        // 4. Discount is available = diff between currently applied discount and new tier.
        ray(4);

        return $diffBetweenCurrentAndNewTier;
    }

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

    public function canBeAppliedTo(CartItem|Buyable $item): bool
    {
        $customCanBeApplied = $this->canBeApplied($item);
        ray($item, $customCanBeApplied);
        if (! is_null($customCanBeApplied)) {
            ray(0);
            return $customCanBeApplied;
        }
        ray(1, $item);
        if ($item instanceof CartItem) {
            return ! $item->getSubtotal()->isZero();
        }

        ray(2, $item);
        if ($item->getBuyablePrice()->isZero()) {
            return false;
        }


        ray(3, $item);

        return true;
    }

    public function render()
    {
        return view('discounts::settings.types.tiered-fixed-amount');
    }
}
