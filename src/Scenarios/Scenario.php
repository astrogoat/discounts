<?php

namespace Astrogoat\Discounts\Scenarios;

use Astrogoat\Discounts\Contracts\CanCalculateBuyableDiscounts;
use Closure;
use Money\Money;

abstract class Scenario
{
    public function handle(array $content, Closure $next)
    {
        return $this->calculate($content['type'], $content['amount'], $next);
    }

    abstract public function calculate(CanCalculateBuyableDiscounts $discountType, Money $amount, Closure $next);
}
