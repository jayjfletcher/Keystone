<?php

declare(strict_types=1);

namespace JayI\Keystone\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JayI\Keystone\Domains\Channel\Models\LocaleModel;

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
