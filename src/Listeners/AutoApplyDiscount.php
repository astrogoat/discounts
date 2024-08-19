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
     * @param ItemAddedToCart|ItemRemovedFromCart $event
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
            cart()->addDiscount(app(Discounts::class)->getCurrentType());
        }
    }
}
