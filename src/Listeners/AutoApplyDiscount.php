<?php

namespace Astrogoat\Discounts\Listeners;

use Astrogoat\Cart\Events\ItemAddedToCart;
use Astrogoat\Discounts\Discounts;
use Astrogoat\Discounts\Settings\DiscountsSettings;
use Astrogoat\Shopify\Apps\Discounts\Types\ShopifyTieredDiscountCodeType;
use Astrogoat\Shopify\Models\DiscountCode;

class AutoApplyDiscount
{
    /**
     * Handle the event.
     *
     * @param \Astrogoat\Cart\Events\ItemAddedToCart $event
     *
     * @return void
     */
    public function handle(ItemAddedToCart $event) : void
    {
        if (app(DiscountsSettings::class)->auto_apply_discount !== true) {
            return;
        }

        cart()->addDiscount(app(Discounts::class)->getCurrentType()->createCartDiscount());
    }
}
