<?php

namespace Astrogoat\Discounts\Casts;

use Astrogoat\Discounts\Discounts;
use Helix\Lego\Apps\Contracts\SettingsCast;
use Helix\Lego\Settings\AppSettings;

class Payload extends SettingsCast
{
    public array $payload;
    protected AppSettings $settings;
    public array $payloadCaches = [];

    protected $listeners = [
        'updatedPayload',
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
        $this->settings = $settings;
        $this->payload = blank($settings->payload)
            ? ['type' => app(Discounts::class)->getDefaultType()]
            : $settings->payload;
    }

    public function updatedPayload($payload)
    {
        $this->payload = $payload;
        $this->emitTo('helix.lego.apps.livewire.app-edit', 'settingKeyUpdated', ['key' => 'payload', 'value' => $this->payload]);
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
        return collect(app(Discounts::class)->getTypes())->mapWithKeys(fn ($type, $key) => [$key => new $type()])->toArray();
    }

    public function getSelectedTypeInclude(): string
    {
        return (new (app(Discounts::class)->getCurrentType()))->view();
    }

    public function render()
    {
        return view('discounts::settings.types.index');
    }
}
