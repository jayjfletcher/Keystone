<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\ProductModel;

use RefactorCircus\Foundation\Support\ServiceProvider;
use RefactorCircus\Keystone\Domains\ProductModel\Models\ProductModelModel;

class ProductModelServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Polymorphic columns store this short name, not the class name.
        $this->keepMorphAliases([
            'keystone_product_model' => ProductModelModel::class,
        ]);

        $this->loadApiRoutesFrom(__DIR__.'/routes.php');
    }
}
