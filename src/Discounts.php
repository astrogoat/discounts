<?php

namespace Astrogoat\Discounts;

use Astrogoat\Discounts\Settings\DiscountsSettings;
use Astrogoat\Discounts\Types\DiscountType;
use Astrogoat\Discounts\Types\TieredFixedAmountType;
use Astrogoat\Discounts\Types\TieredPercentageType;
use Closure;
use Illuminate\Support\Facades\Event;
use Stancl\Tenancy\Events\TenancyBootstrapped;

class Discounts
{
    protected array $types = [
        TieredFixedAmountType::class,
        TieredPercentageType::class,
    ];

    protected array $canBeApplied = [];

    public function getDefaultType(): string
    {
        return TieredFixedAmountType::class;
    }

    public function getTypes(): array
    {
        return $this->types;
    }

    public function getType(string $type): DiscountType
    {
        if (! class_exists($type)) {
            $type = $this->getDefaultType();
        }

        return new $type();
    }

    public function addType(string $type)
    {
        $this->types[] = $type;
    }

    public function getCurrentType(): DiscountType
    {
        $type = settings(DiscountsSettings::class, 'payload')['type'] ?? $this->getDefaultType();

        return $this->getType($type);
    }

    public function getPayload()
    {
        return settings(DiscountsSettings::class, 'payload');
    }

    public static function canBeApplied(Closure $callback, string $type = null)
    {
        Event::listen(TenancyBootstrapped::class, function (TenancyBootstrapped $event) use ($callback, $type) {
            app(Discounts::class)->setCanBeAppliedConstraint($callback, $type);
        });
    }

    public function setCanBeAppliedConstraint(Closure $callback, string $type = null): void
    {
        $this->canBeApplied[$type ?? $this->getCurrentType()::class] = $callback;
    }

    public function getCanBeAppliedConstraint(string $type): ?Closure
    {
        return $this->canBeApplied[$type ?? $this->getCurrentType()::class] ?? null;
    }
}
