<?php

namespace Astrogoat\Discounts;

use Astrogoat\Cart\Events\ItemAddedToCart;
use Astrogoat\Cart\Events\ItemRemovedFromCart;
use Astrogoat\Discounts\Casts\Payload;
use Astrogoat\Discounts\Listeners\AutoApplyDiscount;
use Astrogoat\Discounts\Settings\DiscountsSettings;
use Astrogoat\Discounts\Types\TieredFixedAmountType;
use Astrogoat\Discounts\Types\TieredPercentageType;
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
        Livewire::component('astrogoat.discounts.types.tiered-fixed-amount-type', TieredFixedAmountType::class);
        Livewire::component('astrogoat.discounts.types.tiered-percentage-type', TieredPercentageType::class);
    }

    public function configurePackage(Package $package): void
    {
        $package->name('discounts')->hasViews();
    }
}
