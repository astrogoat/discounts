<?php

namespace Astrogoat\Discounts\Traits;

use Astrogoat\Cart\CartItem;
use Astrogoat\Cart\Contracts\Buyable;
use Astrogoat\Discounts\Settings\DiscountsSettings;
use Money\Money;

trait HasTiers
{
    private function getHighestValueTier(): array
    {
        $highestTier = collect(settings(DiscountsSettings::class, 'payload.value.tiers'))
            ->sortByDesc
            ->threshold
            ->values()
            ->first();

        return $highestTier ?: [
            'value' => 0,
            'threshold' => 0,
        ];
    }

    private function findMatchingTier(Money $money, CartItem|Buyable|null $contextOverride = null): array
    {
        $tiers = collect(settings(DiscountsSettings::class, 'payload.value.tiers'))
            ->sortByDesc
            ->threshold
            ->values();

        $context = $contextOverride ?? $this->tierEligibilityContext ?? null;

        foreach ($tiers as $tier) {
            if ($money->getAmount() >= $tier['threshold']) {
                if (! $this->tierQualifiesForContext($tier, $money, $context)) {
                    continue;
                }

                return $tier;
            }
        }

        return [
            'value' => 0,
            'threshold' => 0,
        ];
    }

    public function countTiers(): int
    {
        return count(collect(settings(DiscountsSettings::class, 'payload.value.tiers')));
    }

    public function addTier()
    {
        $this->displayTiers[] = [
            'threshold' => 0,
            'value' => null,
        ];

        $this->payload['value']['tiers'][] = [
            'threshold' => 0,
            'value' => null,
        ];

        $this->updatedPayload();
    }

    public function removeTier($index): void
    {
        array_splice($this->displayTiers, $index, 1);
        array_splice($this->payload['value']['tiers'], $index, 1);

        $this->updatedPayload();
    }
}
