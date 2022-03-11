<?php

namespace Astrogoat\Discounts;

use Astrogoat\Cart\Events\ItemAddedToCart;
use Astrogoat\Cart\Events\ItemRemovedFromCart;
use Astrogoat\Discounts\Casts\Payload;
use Astrogoat\Discounts\Http\Livewire\Types\TieredFixedAmount;
use Astrogoat\Discounts\Http\Livewire\Types\TieredPercentage;
use Astrogoat\Discounts\Listeners\AutoApplyDiscount;
use Astrogoat\Discounts\Settings\DiscountsSettings;
use Helix\Lego\Apps\App;
use Helix\Lego\LegoManager;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
        Event::listen(ItemAddedToCart::class, AutoApplyDiscount::class);
        Event::listen(ItemRemovedFromCart::class, AutoApplyDiscount::class);

        Livewire::component('astrogoat.discounts.casts.payload', Payload::class);
        Livewire::component('astrogoat.discounts.types.tiered-fixed-amount', TieredFixedAmount::class);
        Livewire::component('astrogoat.discounts.types.tiered-percentage', TieredPercentage::class);
    }

    public function configurePackage(Package $package): void
    {
        $package->name('discounts')->hasViews();
    }
}
