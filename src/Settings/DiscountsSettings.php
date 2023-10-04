<?php

namespace Astrogoat\Discounts\Settings;

use Astrogoat\Discounts\Casts\Payload;
use Astrogoat\Discounts\Events\DiscountSettingsSaved;
use Helix\Lego\Settings\AppSettings;
use Spatie\LaravelSettings\Settings;

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
            'auto_apply_discount' => 'When this is checked the discount will automatically be added when an item is added to the cart.',
        ];
    }

    public function description(): string
    {
        return 'Allows you to set discounts and discount tiers.';
    }

    public function save(): Settings
    {
        $parent = parent::save();

        event(new DiscountSettingsSaved($this));

        return $parent;
    }
}
