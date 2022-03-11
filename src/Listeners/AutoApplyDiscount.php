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
        if (app(DiscountsSettings::class)->auto_apply_discount !== true) {
            return;
        }

        cart()->addDiscount(app(Discounts::class)->getCurrentType()->createCartDiscount());
    }
}
