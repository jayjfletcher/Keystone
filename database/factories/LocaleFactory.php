<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use RefactorCircus\Showroom\Domains\Channel\Models\LocaleModel;

/**
 * @extends Factory<LocaleModel>
 */
final class LocaleFactory extends Factory
{
    protected $model = LocaleModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->lexify('??_??'),
            'labels' => [],
        ];
    }
}
