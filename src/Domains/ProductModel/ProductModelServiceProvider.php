<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\ProductModel;

use JayI\Foundation\Support\ServiceProvider;
use JayI\Keystone\Domains\ProductModel\Models\ProductModelModel;

class ProductModelServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->keepMorphAliases([
            'keystone_product_model' => ProductModelModel::class,
            'JayI\Keystone\Models\ProductModel' => ProductModelModel::class,
        ]);

        $this->loadApiRoutesFrom(__DIR__.'/routes.php');
    }
}
