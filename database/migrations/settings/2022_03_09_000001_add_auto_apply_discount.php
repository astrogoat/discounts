<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class AddAutoApplyDiscount extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('discounts.auto_apply_discount', true);
    }

    public function down()
    {
        $this->migrator->delete('discounts.auto_apply_discount');
    }
}
