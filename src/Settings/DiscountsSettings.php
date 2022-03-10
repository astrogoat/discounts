<?php

namespace Astrogoat\Discounts\Settings;

use Astrogoat\Discounts\Casts\Payload;
use Helix\Lego\Settings\AppSettings;

class DiscountsSettings extends AppSettings
{
    public $payload;
    public bool $auto_apply_discount;

    protected array $rules = [
        'payload' => ['required'],
        'auto_apply_discount' => ['nullable'],
    ];

    public static function casts(): array
    {
        return [
            'payload' => Payload::class,
        ];
    }

    public function help()
    {
        return [
            'auto_apply_discount' => 'When this is checked the discount will automatically be added when an item is added to the cart.'
        ];
    }

    public function description(): string
    {
        return 'Allows you to set discounts and discount tiers.';
    }
}
