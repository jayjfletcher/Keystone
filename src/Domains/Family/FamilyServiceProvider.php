<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family;

use JayI\Foundation\Audit\AuditHooks;
use JayI\Foundation\Support\ServiceProvider;
use JayI\Keystone\Domains\Family\Models\FamilyModel;
use JayI\Keystone\Domains\Family\Models\FamilyVariantModel;

class FamilyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->keepMorphAliases([
            'JayI\Keystone\Models\Family' => FamilyModel::class,
            'JayI\Keystone\Models\FamilyVariant' => FamilyVariantModel::class,
        ]);

        // How the audit log (jayi/keen) names them: the current locale's label, else the code.
        $this->app->make(AuditHooks::class)
            ->label(FamilyModel::class, fn (FamilyModel $family): string => $family->label())
            ->label(FamilyVariantModel::class, fn (FamilyVariantModel $familyVariant): string => $familyVariant->label());

        $this->loadApiRoutesFrom(__DIR__.'/routes.php');
    }
}
