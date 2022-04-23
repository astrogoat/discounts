<?php

namespace Astrogoat\Discounts\Types;

use Astrogoat\Cart\CartItem;
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
        $numberFormatter = new NumberFormatter(app('lego')->getLocale(), NumberFormatter::CURRENCY);
        $moneyFormatter = new IntlMoneyFormatter($numberFormatter, new ISOCurrencies());
        $discountAmount = new Money($this->findMatchingTier(cart()->getSubtotal())['value'], cart()->getCartCurrency());

        return $moneyFormatter->format($discountAmount) . ' off';
    }

    public function calculateCartItemDiscountAmount(CartItem $cartItem): Money
    {
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
        $discountAmount = $this->findMatchingTier(cart()->getSubtotal())['value'];
        $discountedAmount = $discountAmount / cart()->getTotalQuantity() * $cartItem->getQuantity();

        return new Money($discountedAmount, cart()->getCartCurrency());
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

    public function canBeAppliedTo(CartItem $cartItem): bool
    {
        return true;
    }

    public function render()
    {
        return view('discounts::settings.types.tiered-fixed-amount');
    }
}
