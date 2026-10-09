<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone;

use Illuminate\Support\Facades\Blade;
use RefactorCircus\Foundation\Packages\Package;
use RefactorCircus\Foundation\Support\PackageServiceProvider;
use RefactorCircus\Keystone\Atrium\KeystonePlugin;
use RefactorCircus\Keystone\Atrium\ScreenAccess;
use RefactorCircus\Keystone\Domains\DomainServiceProvider;
use RefactorCircus\Keystone\Impex\ImpexIntegration;
use RefactorCircus\Keystone\Mcp\KeystoneServer;

class KeystoneServiceProvider extends PackageServiceProvider
{
    /**
     * Describe Keystone to the shared refactor-circus/foundation runtime. Every base
     * class finds the package through it, and the helpers below read the
     * `keystone.*` config keys it names.
     */
    protected function definition(): Package
    {
        return Package::make('keystone', 'RefactorCircus\\Keystone')
            ->label('Keystone')
            ->server(KeystoneServer::class)
            ->authorization();
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/keystone.php', 'keystone');

        $this->registerPackage();

        $this->app->singleton(Keystone::class);

        $this->app->register(DomainServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Cortex is optional: agents get the Keystone tools only when it is loaded.
        $this->registerCortex();

        // Impex is optional too: imports, exports and feeds are its flows.
        $this->app->make(ImpexIntegration::class)->register();

        $this->registerPolicies();

        $this->registerAtriumPlugin(KeystonePlugin::class);

        $this->registerMcpServer();

        // GET {prefix}/history: Keystone's audit entries, with refactor-circus/keen.
        $this->loadHistoryRoutes();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'keystone');

        // @keystoneCan('update', $product) ... @endkeystoneCan: the screens'
        // own check, so a control shows only when its action is allowed.
        Blade::if('keystoneCan', ScreenAccess::allows(...));

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'keystone');

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/keystone.php' => config_path('keystone.php'),
        ], ['keystone', 'keystone-config']);

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/keystone'),
        ], ['keystone', 'keystone-views']);

        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/keystone'),
        ], ['keystone', 'keystone-lang']);

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], ['keystone', 'keystone-migrations']);
    }
}
