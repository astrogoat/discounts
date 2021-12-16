<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreateDiscountsSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('discounts.enabled', false);
        // $this->migrator->add('discounts.url', '');
        // $this->migrator->addEncrypted('discounts.access_token', '');
    }

    public function down()
    {
        $this->migrator->delete('discounts.enabled');
        // $this->migrator->delete('discounts.url');
        // $this->migrator->delete('discounts.access_token');
    }
}
