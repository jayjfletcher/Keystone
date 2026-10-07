<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Owner;

use JayI\Foundation\Audit\AuditHooks;
use JayI\Foundation\Support\ServiceProvider;
use JayI\Keystone\Domains\Owner\Models\OwnerModel;
use JayI\Keystone\Domains\Owner\Models\OwnerTypeModel;

class OwnerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->keepMorphAliases([
            'keystone_owner' => OwnerModel::class,
            'JayI\Keystone\Models\Owner' => OwnerModel::class,
            'JayI\Keystone\Models\OwnerType' => OwnerTypeModel::class,
        ]);

        // How the audit log (jayi/keen) names them: the current locale's label, else the code.
        $this->app->make(AuditHooks::class)
            ->label(OwnerModel::class, fn (OwnerModel $owner): string => $owner->label())
            ->label(OwnerTypeModel::class, fn (OwnerTypeModel $ownerType): string => $ownerType->label());

        $this->loadApiRoutesFrom(__DIR__.'/routes.php');
    }
}
