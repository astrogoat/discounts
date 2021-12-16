<?php

namespace Astrogoat\Discounts;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Astrogoat\Discounts\Discounts
 */
class DiscountsFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'discounts';
    }
}
