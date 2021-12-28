<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreateDiscountsSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('discounts.enabled', false);
        $this->migrator->add('discounts.tiers', []);
    }

    public function down()
    {
        $this->migrator->delete('discounts.enabled');
        $this->migrator->delete('discounts.tiers');
    }
}
