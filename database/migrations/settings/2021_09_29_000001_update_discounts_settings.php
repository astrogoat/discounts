<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class UpdateDiscountsSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('discounts.payload', '');
        $this->migrator->delete('discounts.tiers');
    }

    public function down()
    {
        $this->migrator->add('discounts.tiers', []);
        $this->migrator->delete('discounts.payload');
    }
}
