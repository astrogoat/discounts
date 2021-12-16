<?php

namespace Astrogoat\Discounts\Actions;

use Helix\Lego\Apps\Actions\Action;

class DiscountsAction extends Action
{
    public static function actionName(): string
    {
        return 'Discounts action name';
    }

    public static function run(): mixed
    {
        return redirect()->back();
    }
}
