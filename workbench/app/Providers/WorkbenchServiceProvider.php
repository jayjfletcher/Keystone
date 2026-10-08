<?php

namespace Workbench\App\Providers;

use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Workbench\App\Http\Middleware\SignInWorkbenchUser;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Serve demo assets from the public disk (linked by the build), with
        // host-relative URLs so they load on whatever port `serve` picks.
        config()->set('keystone.media.disk', 'public');
        config()->set('filesystems.disks.public.url', '/storage');

        // jayi/pennantplus's layered store: users who follow a feature's
        // global value store nothing, as in a real application.
        config()->set('pennant.default', 'pennantplus');
        config()->set('pennant.stores.pennantplus', [
            'driver' => 'pennantplus',
            'connection' => null,
            'table' => 'features',
        ]);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Keep the workbench user signed in whatever URL is opened first.
        $this->callAfterResolving(HttpKernel::class, function (HttpKernel $kernel): void {
            if ($kernel instanceof Kernel) {
                $kernel->appendMiddlewareToGroup('web', SignInWorkbenchUser::class);
            }
        });

        // Impex's dashboard only opens runs the viewer owns; the demo's
        // transfers belong to other users, so make the demo user an operator.
        config()->set('impex.atrium.show_all', true);

        // The workbench dashboard is open so `composer serve` is usable
        // without logging in. A real application defines a real gate.
        Gate::define('viewAtrium', fn ($user = null): bool => true);
    }
}
