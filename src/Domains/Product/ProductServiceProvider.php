<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Product;

use RefactorCircus\Keystone\Support\ServiceProvider;
use RefactorCircus\Showroom\Domains\Product\Models\ProductModel;

class ProductServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Polymorphic columns store this short name, not the class name.
        $this->keepMorphAliases([
            'showroom_product' => ProductModel::class,
        ]);

        $this->loadApiRoutesFrom(__DIR__.'/routes.php');
    }
}
