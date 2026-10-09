<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Family;

use RefactorCircus\Foundation\Audit\AuditHooks;
use RefactorCircus\Foundation\Support\ServiceProvider;
use RefactorCircus\Showroom\Domains\Family\Models\FamilyModel;
use RefactorCircus\Showroom\Domains\Family\Models\FamilyVariantModel;

class FamilyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // How the audit log (refactor-circus/keen) names them: the current locale's label, else the code.
        $this->app->make(AuditHooks::class)
            ->label(FamilyModel::class, fn (FamilyModel $family): string => $family->label())
            ->label(FamilyVariantModel::class, fn (FamilyVariantModel $familyVariant): string => $familyVariant->label());

        $this->loadApiRoutesFrom(__DIR__.'/routes.php');
    }
}
