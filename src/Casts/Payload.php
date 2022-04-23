<?php

namespace Astrogoat\Discounts\Casts;

use Astrogoat\Discounts\Discounts;
use Astrogoat\Discounts\Types\DiscountType;
use Helix\Lego\Apps\Contracts\SettingsCast;
use Helix\Lego\Settings\AppSettings;

class Payload extends SettingsCast
{
    public array $payload;
    public array $payloadCaches = [];
    public int $childViewNumber = 1;

    protected $listeners = [
        'payloadHasBeenUpdated',
    ];

    public function get($payload)
    {
        return $payload;
    }

    public function set($payload)
    {
        return $payload;
    }

    public function mount(AppSettings $settings)
    {
        parent::mount($settings);

        $this->payload = blank($settings->payload)
            ? ['type' => app(Discounts::class)->getDefaultType()]
            : $settings->payload;
    }

    public function payloadHasBeenUpdated($payload)
    {
        $this->payload = $payload;
        $this->updated('payload', $payload);
    }

    public function updatingPayloadType($value)
    {
        if (isset($this->payloadCaches[$value])) {
            // Saving current payload to cache.
            $this->payloadCaches[$this->payload['type']] = $this->payload;

            // Retrieving saved payload.
            $this->payload = $this->payloadCaches[$value];

            return;
        }

        // Saving current payload to cache.
        $this->payloadCaches[$this->payload['type']] = $this->payload;

        // Resetting payload.
        $this->payload = [
            'type' => $value,
        ];
    }

    public function getTypes(): array
    {
        return collect(app(Discounts::class)->getTypes())->map(fn ($type) => new $type())->toArray();
    }

    public function getSelectedType()
    {
        return app(Discounts::class)->getType($this->payload['type']);
    }

    /**
     * A little hack to force re-rendering type views when changing the type.
     *
     * @return string
     */
    public function getSelectedTypeView(): string
    {
        $this->childViewNumber = ($this->childViewNumber === 1) ? 2 : 1;

        return 'discounts::settings.types.child-' . $this->childViewNumber;
    }

    public function render()
    {
        return view('discounts::settings.types.index');
    }
}
