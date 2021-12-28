<?php

namespace Astrogoat\Discounts\Settings;

use Astrogoat\Discounts\Casts\PayloadCast;
use Helix\Lego\Settings\AppSettings;

class DiscountsSettings extends AppSettings
{
    public $payload;

    protected array $rules = [
        'payload' => ['required'],
    ];

    public static function casts(): array
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
