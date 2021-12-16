<?php

namespace Astrogoat\Discounts\Settings;

use Astrogoat\Discounts\Actions\DiscountsAction;
use Helix\Lego\Settings\AppSettings;

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
