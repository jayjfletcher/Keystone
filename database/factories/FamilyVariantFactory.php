<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use RefactorCircus\Showroom\Domains\Family\Models\FamilyModel;
use RefactorCircus\Showroom\Domains\Family\Models\FamilyVariantModel;

/**
 * @extends Factory<FamilyVariantModel>
 */
final class FamilyVariantFactory extends Factory
{
    protected $model = FamilyVariantModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'family_id' => FamilyModel::factory(),
            'code' => 'variant_'.fake()->unique()->lexify('??????'),
            'labels' => [],
            'levels' => 1,
        ];
    }
}
