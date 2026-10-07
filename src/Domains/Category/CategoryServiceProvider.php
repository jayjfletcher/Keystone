<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Category;

use JayI\Foundation\Audit\AuditHooks;
use JayI\Foundation\Support\ServiceProvider;
use JayI\Keystone\Domains\Category\Models\CategoryModel;

class CategoryServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->keepMorphAliases([
            'JayI\Keystone\Models\Category' => CategoryModel::class,
        ]);

        // How the audit log (jayi/keen) names them: the current locale's label, else the code.
        $this->app->make(AuditHooks::class)
            ->label(CategoryModel::class, fn (CategoryModel $category): string => $category->label());

        $this->loadApiRoutesFrom(__DIR__.'/routes.php');
    }
}
