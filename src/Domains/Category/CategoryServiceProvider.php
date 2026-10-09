<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Category;

use RefactorCircus\Keystone\Audit\AuditHooks;
use RefactorCircus\Keystone\Support\ServiceProvider;
use RefactorCircus\Showroom\Domains\Category\Models\CategoryModel;

class CategoryServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // How the audit log (refactor-circus/keen) names them: the current locale's label, else the code.
        $this->app->make(AuditHooks::class)
            ->label(CategoryModel::class, fn (CategoryModel $category): string => $category->label());

        $this->loadApiRoutesFrom(__DIR__.'/routes.php');
    }
}
