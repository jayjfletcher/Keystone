<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use RefactorCircus\Keystone\Domains\Family\Models\FamilyVariantModel;
use RefactorCircus\Keystone\Domains\ProductModel\Models\ProductModelModel;

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
