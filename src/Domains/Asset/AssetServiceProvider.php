<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Asset;

use JayI\Foundation\Audit\AuditHooks;
use JayI\Foundation\Support\ServiceProvider;
use JayI\Keystone\Domains\Asset\Models\AssetModel;

class AssetServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->keepMorphAliases([
            'JayI\Keystone\Models\Asset' => AssetModel::class,
        ]);

        // How the audit log (jayi/keen) names them: the current locale's label, else the code.
        $this->app->make(AuditHooks::class)
            ->label(AssetModel::class, fn (AssetModel $asset): string => $asset->label());

        $this->loadApiRoutesFrom(__DIR__.'/routes.php');
    }
}
