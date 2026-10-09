<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Product;

use RefactorCircus\Foundation\Support\ServiceProvider;
use RefactorCircus\Keystone\Domains\Product\Models\ProductModel;

class ProductServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Polymorphic columns store this short name, not the class name.
        $this->keepMorphAliases([
            'keystone_product' => ProductModel::class,
        ]);

        $this->loadApiRoutesFrom(__DIR__.'/routes.php');
    }
}
