<?php

namespace Astrogoat\Discounts\Settings;

use Helix\Lego\Settings\AppSettings;
use Astrogoat\Discounts\Actions\DiscountsAction;

class DiscountsSettings extends AppSettings
{
    // public string $url;
    // public string $access_token;

    protected array $rules = [
        // 'url' => ['required', 'url'],
        // 'access_token' => ['required'],
    ];

    protected static array $actions = [
        // DiscountsAction::class,
    ];

    // public static function encrypted(): array
    // {
    //     return ['access_token'];
    // }

    public function description(): string
    {
        return 'Interact with Discounts.';
    }
}
