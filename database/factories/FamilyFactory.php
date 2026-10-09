<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use RefactorCircus\Keystone\Domains\Family\Models\FamilyModel;

/**
 * @extends Factory<FamilyModel>
 */
final class FamilyFactory extends Factory
{
    protected $model = FamilyModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = 'family_'.fake()->unique()->lexify('??????');

        return [
            'code' => $code,
            'labels' => ['en' => str($code)->headline()->toString()],
            'sort_order' => 0,
        ];
    }
}
