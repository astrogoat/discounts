<?php

namespace Astrogoat\Discounts\Events;

use Astrogoat\Discounts\Settings\DiscountsSettings;

class DiscountSettingsSaved
{
    public DiscountsSettings $settings;

    public function __construct(DiscountsSettings $settings)
    {
        $this->settings = $settings;
    }
}
