<?php

namespace Astrogoat\Discounts\Http\Livewire;

use Astrogoat\Discounts\Discounts;
use Astrogoat\Promobar\Promobar;
use Helix\Lego\Settings\AppSettings;
use Livewire\Component;

class Payload extends Component
{
    public array $payload;
    protected AppSettings $settings;
    public array $payloadCaches = [];

    protected $listeners = [
        'newPayload',
    ];

    public function mount(AppSettings $settings)
    {
        $this->settings = $settings;
        $this->payload = blank($settings->payload)
            ? ['type' => app(Discounts::class)->getDefaultType()]
            : $settings->payload;
    }

    public function newPayload($payload)
    {
        $this->payload = $payload;
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
        $type = new (app(Discounts::class)->getTypes()[$this->payload['type']]);

        return $type->view();
    }

    public function render()
    {
        return view('discounts::settings.types.index');
    }
}
