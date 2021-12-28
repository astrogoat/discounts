<?php

namespace Astrogoat\Discounts\Settings;

use Astrogoat\Discounts\Casts\DiscountTiersCast;
use Astrogoat\Discounts\Casts\PayloadCast;
use Helix\Lego\Apps\Discounts\CalculateDiscount;
use Helix\Lego\Settings\AppSettings;

class DiscountsSettings extends AppSettings
{
    public $payload;
    public string $apiKey;

    protected array $rules = [
        'payload' => ['required'],
    ];

    public static function casts() : array
    {
        return [
            'payload' => PayloadCast::class,
        ];
    }

    public function description(): string
    {
        return 'Allows you to set discounts and discount tiers.';
    }
}
