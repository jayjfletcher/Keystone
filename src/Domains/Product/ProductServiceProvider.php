<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Product;

use JayI\Foundation\Support\ServiceProvider;
use JayI\Keystone\Domains\Product\Models\ProductModel;

class ProductServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->keepMorphAliases([
            'keystone_product' => ProductModel::class,
            'JayI\Keystone\Models\Product' => ProductModel::class,
        ]);

        $this->loadApiRoutesFrom(__DIR__.'/routes.php');
    }
}
