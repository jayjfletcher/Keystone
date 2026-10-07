<?php

declare(strict_types=1);

namespace JayI\Keystone\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JayI\Keystone\Domains\Product\Models\ProductModel;

/**
 * @extends Factory<ProductModel>
 */
final class ProductFactory extends Factory
{
    protected $model = ProductModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'identifier' => 'SKU-'.fake()->unique()->numerify('######'),
            'enabled' => true,
            'values' => [],
        ];
    }
}
