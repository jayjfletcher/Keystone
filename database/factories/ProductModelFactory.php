<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use RefactorCircus\Showroom\Domains\Family\Models\FamilyVariantModel;
use RefactorCircus\Showroom\Domains\ProductModel\Models\ProductModelModel;

/**
 * @extends Factory<ProductModelModel>
 */
final class ProductModelFactory extends Factory
{
    protected $model = ProductModelModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'model_'.fake()->unique()->lexify('??????'),
            'family_variant_id' => FamilyVariantModel::factory(),
            'values' => [],
        ];
    }
}
