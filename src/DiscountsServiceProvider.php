<?php

namespace Astrogoat\Discounts;

use Astrogoat\Discounts\Http\Livewire\Payload;
use Astrogoat\Discounts\Http\Livewire\Types\TieredFixedAmount;
use Astrogoat\Discounts\Http\Livewire\Types\TieredPercentage;
use Helix\Lego\Apps\App;
use Helix\Lego\LegoManager;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Astrogoat\Discounts\Settings\DiscountsSettings;

class DiscountsServiceProvider extends PackageServiceProvider
{
    public function registerApp(App $app)
    {
        return $app
            ->name('discounts')
            ->settings(DiscountsSettings::class)
            ->migrations([
                __DIR__ . '/../database/migrations',
                __DIR__ . '/../database/migrations/settings',
            ])
            ->backendRoutes(__DIR__.'/../routes/backend.php')
            ->frontendRoutes(__DIR__.'/../routes/frontend.php');
    }

    public function registeringPackage()
    {
        $this->app->singleton(Discounts::class, fn () => new Discounts());

        $this->callAfterResolving('lego', function (LegoManager $lego) {
            $lego->registerApp(fn (App $app) => $this->registerApp($app));
        });
    }

    public function bootingPackage()
    {
        Livewire::component('astrogoat.discounts.payload', Payload::class);
        Livewire::component('astrogoat.discounts.types.tiered-fixed-amount', TieredFixedAmount::class);
        Livewire::component('astrogoat.discounts.types.tiered-percentage', TieredPercentage::class);
    }

    public function configurePackage(Package $package): void
    {
        $package->name('discounts')->hasViews();
    }
}
