<?php

namespace Astrogoat\Discounts;

use Astrogoat\Discounts\Settings\DiscountsSettings;
use Astrogoat\Discounts\Types\DiscountType;
use Astrogoat\Discounts\Types\TieredFixedAmountType;
use Astrogoat\Discounts\Types\TieredPercentageType;
use Closure;
use Illuminate\Support\Facades\Event;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use Stancl\Tenancy\Events\TenancyBootstrapped;

class Discounts
{
    protected array $types = [
        TieredFixedAmountType::class,
        TieredPercentageType::class,
    ];

    protected array $canBeApplied = [];

    private Closure $moneyFormatter;

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

    public static function canBeApplied(Closure $callback, string $type = null): void
    {
        Event::listen(TenancyBootstrapped::class, function (TenancyBootstrapped $event) use ($callback, $type) {
            app(Discounts::class)->canBeApplied[$type ?? app(Discounts::class)->getCurrentType()::class] = $callback;
        });
    }

    public function getCanBeAppliedConstraint(string $type): ?Closure
    {
        return $this->canBeApplied[$type ?? $this->getCurrentType()::class] ?? null;
    }

    public static function moneyFormatter(Closure $callback): void
    {
        Event::listen(TenancyBootstrapped::class, function (TenancyBootstrapped $event) use ($callback) {
            app(Discounts::class)->moneyFormatter = $callback;
        });
    }

    public function getMoneyFormatter(): Closure
    {
        $defaultFormatter = function (Money $money) {
            $money = $money->isZero() ? $money : $money->roundToUnit(1);

            $numberFormatter = new NumberFormatter(app('lego')->getLocale(), NumberFormatter::CURRENCY);
            $decimals = (substr($money->getAmount(), -2) > 0) ? 2 : 0;
            $numberFormatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $decimals);
            $moneyFormatter = new IntlMoneyFormatter($numberFormatter, new ISOCurrencies());

            return $moneyFormatter->format($money);
        };

        return $this->moneyFormatter ?? $defaultFormatter;
    }

    public function __call(string $name, array $arguments)
    {
        return $this->getCurrentType()->$name(...$arguments);
    }
}
