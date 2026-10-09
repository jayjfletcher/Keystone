<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Asset;

use RefactorCircus\Foundation\Audit\AuditHooks;
use RefactorCircus\Foundation\Support\ServiceProvider;
use RefactorCircus\Keystone\Domains\Asset\Models\AssetModel;

class AssetServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // How the audit log (refactor-circus/keen) names them: the current locale's label, else the code.
        $this->app->make(AuditHooks::class)
            ->label(AssetModel::class, fn (AssetModel $asset): string => $asset->label());

        $this->loadApiRoutesFrom(__DIR__.'/routes.php');
    }
}
