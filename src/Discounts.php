<?php

namespace Astrogoat\Discounts;

use Astrogoat\Discounts\Settings\DiscountsSettings;
use Astrogoat\Discounts\Types\TieredFixedAmountType;
use Astrogoat\Discounts\Types\TieredPercentageType;
use Money\Money;

class Discounts
{
    protected array $types = [
        'tiered_fixed_amount' => TieredFixedAmountType::class,
        'tiered_percentage' => TieredPercentageType::class,
    ];

    public function getDefaultType(): string
    {
        return 'tiered_fixed_amount';
    }

    public function getTypes(): array
    {
        return $this->types;
    }

    public function getType(string $type)
    {
        return new ($this->types[$type]);
    }

    public function addType(string $key, string $type)
    {
        $this->types[$key] = $type;
    }

    public function getCurrentType()
    {
        $type = settings(DiscountsSettings::class, 'payload')['type'] ?? $this->getDefaultType();

        return $this->getType($type);
    }

    public function getPayload()
    {
        return settings(DiscountsSettings::class, 'payload');
    }

    public function calculateDiscountAmount(Money $money): Money
    {
        $type = $this->getCurrentType();

        return (new $type())->calculateDiscountAmount($money);
    }

    public function getDisplayValue(Money $money): mixed
    {
        $type = $this->getCurrentType();

        return (new $type())->getDisplayValue($money);
    }

    public function getDiscountedAmount(Money $money): Money
    {
        return $money->subtract($this->calculateDiscountAmount($money));
    }
}
