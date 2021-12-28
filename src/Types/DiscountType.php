<?php

namespace Astrogoat\Discounts\Types;

use Illuminate\Support\Str;

abstract class DiscountType
{
    abstract public function view(): string;

    abstract public static function getId(): string;

    public function getName(): string
    {
        return Str::of(class_basename($this))->beforeLast('Type')->headline();
    }
}
