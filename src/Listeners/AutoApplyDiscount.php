<?php

namespace Astrogoat\Discounts\Listeners;

use Astrogoat\Cart\Events\ItemAddedToCart;
use Astrogoat\Cart\Events\ItemRemovedFromCart;
use Astrogoat\Discounts\Discounts;
use Astrogoat\Discounts\Settings\DiscountsSettings;

class AutoApplyDiscount
{
    /**
     * Handle the event.
     *
     * @param \Astrogoat\Cart\Events\ItemAddedToCart $event
     *
     * @return void
     */
    public function handle(ItemAddedToCart|ItemRemovedFromCart $event): void
    {
        $settings = app(DiscountsSettings::class);

        if (! $settings->isEnabled()) {
            return;
        }

        if ($event instanceof ItemAddedToCart) {
            if (app(DiscountsSettings::class)->auto_apply_discount !== true) {
                return;
            }

            if (cart()->getTotalDiscountAmountFor(app(Discounts::class)->getCurrentType())->isZero()) {
                return;
            }

            cart()->addDiscount(app(Discounts::class)->getCurrentType());
        }

        if ($event instanceof ItemRemovedFromCart) {
            $discount = app(Discounts::class)->getCurrentType();

            if (cart()->getTotalDiscountAmountFor($discount)->isZero()) {
                cart()->removeDiscount($discount->getId());
            }
        }
    }
}
