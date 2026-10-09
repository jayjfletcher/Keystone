<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use RefactorCircus\Showroom\Domains\Product\Models\ProductModel;

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
