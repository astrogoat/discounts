<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreateSkeletonSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('skeleton.enabled', false);
        // $this->migrator->add('skeleton.url', '');
        // $this->migrator->addEncrypted('skeleton.access_token', '');
    }

    public function down()
    {
        $this->migrator->delete('skeleton.enabled');
        // $this->migrator->delete('skeleton.url');
        // $this->migrator->delete('skeleton.access_token');
    }
}
