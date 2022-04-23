<?php

namespace Astrogoat\Discounts;

use Astrogoat\Discounts\Settings\DiscountsSettings;
use Astrogoat\Discounts\Types\DiscountType;
use Astrogoat\Discounts\Types\TieredFixedAmountType;
use Astrogoat\Discounts\Types\TieredPercentageType;

class Discounts
{
    protected array $types = [
        TieredFixedAmountType::class,
        TieredPercentageType::class,
    ];

    public function getDefaultType(): string
    {
        return TieredFixedAmountType::class;
    }

    public function getTypes(): array
    {
        return $this->types;
    }

    public function getType(string $type) : DiscountType
    {
        return new $type;
    }

    public function addType(string $type)
    {
        $this->types[] = $type;
    }

    public function getCurrentType() : DiscountType
    {
        $type = settings(DiscountsSettings::class, 'payload')['type'] ?? $this->getDefaultType();

        return $this->getType($type);
    }

    public function getPayload()
    {
        return settings(DiscountsSettings::class, 'payload');
    }
}
