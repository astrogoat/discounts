<?php

namespace Astrogoat\Discounts;

use Illuminate\Support\Collection;
use Money\Money;

class CalculateDiscount
{
    protected Collection $tiers;

    public function __construct(protected string $type, array $tiers)
    {
        $this->tiers = collect($tiers);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTiers(): Collection
    {
        return $this->tiers;
    }

    public function getValue(Money $money): int
    {
        return $this->findTier($money)['value'];
    }

    public function getDiscountedAmount(Money $money): Money
    {
        return $money->subtract($this->getDiscountAmount($money));
    }

    public function getDiscountAmount(Money $money): Money
    {
        $tier = $this->findTier($money);

        if ($this->type === 'fixed_amount') {
            return new Money($tier['value'], $money->getCurrency());
        }

        if ($this->type === 'percentage') {
            $amount = $money->getAmount() * $tier['value'] / 100;

            return new Money($amount, $money->getCurrency());
        }

        throw new \Exception("Discount type \"{$this->type}\" is not supported.");

        return $money->getAmount();
    }

    private function findTier(Money $money)
    {
        $tiers = $this->tiers->sortByDesc->threshold->values();

        foreach ($tiers as $tier) {
            if ($money->getAmount() >= $tier['threshold']) {
                return $tier;
            }
        }

        return [
            'value' => 0,
            'threshold' => 0,
        ];
    }

    public function toArray()
    {
        return [
            'type' => $this->getType(),
            'tiers' => $this->getTiers(),
        ];
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }
}
