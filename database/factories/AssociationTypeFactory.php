<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use RefactorCircus\Showroom\Domains\Association\Models\AssociationTypeModel;

/**
 * @extends Factory<AssociationTypeModel>
 */
final class AssociationTypeFactory extends Factory
{
    protected $model = AssociationTypeModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'type_'.fake()->unique()->lexify('??????'),
            'labels' => [],
            'is_two_way' => false,
            'is_quantified' => false,
        ];
    }
}
