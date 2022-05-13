<?php

namespace Astrogoat\Discounts\Traits;

use Astrogoat\Discounts\Settings\DiscountsSettings;

trait TitleDisplayType
{
    public $titleDisplayTypeAs;

    public static string $defaultTitleDisplayType = 'percentage';

    public function getTitleDisplayTypeOptions(): array
    {
        return [
            'percentage' => 'Percentage',
            'amount' => 'Amount',
        ];
    }

    public function setTitleDisplayType()
    {
        if (blank($this->titleDisplayTypeAs)) {
            $this->titleDisplayTypeAs = settings(DiscountsSettings::class, 'payload.value.titleDisplayType') ?: self::$defaultTitleDisplayType;
        }
    }

    public function updatingTitleDisplayTypeAs($value): void
    {
        data_set($this->payload['value'], 'titleDisplayType', $value);

        $this->updatedPayload();
    }

    private function getTitleDisplayType(): string
    {
        return $this->titleDisplayTypeAs ?: self::$defaultTitleDisplayType;
    }
}
