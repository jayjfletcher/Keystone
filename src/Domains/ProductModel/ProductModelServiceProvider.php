<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\ProductModel;

use RefactorCircus\Keystone\Support\ServiceProvider;
use RefactorCircus\Showroom\Domains\ProductModel\Models\ProductModelModel;

class ProductModelServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Polymorphic columns store this short name, not the class name.
        $this->keepMorphAliases([
            'showroom_product_model' => ProductModelModel::class,
        ]);

        $this->loadApiRoutesFrom(__DIR__.'/routes.php');
    }
}
