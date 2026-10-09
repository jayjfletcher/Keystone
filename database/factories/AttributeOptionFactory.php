<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use RefactorCircus\Showroom\Domains\Attribute\Models\AttributeModel;
use RefactorCircus\Showroom\Domains\Attribute\Models\AttributeOptionModel;

/**
 * @extends Factory<AttributeOptionModel>
 */
final class AttributeOptionFactory extends Factory
{
    protected $model = AttributeOptionModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = 'option_'.fake()->unique()->lexify('??????');

        return [
            'attribute_id' => AttributeModel::factory()->select(),
            'code' => $code,
            'labels' => ['en' => str($code)->headline()->toString()],
            'sort_order' => 0,
        ];
    }
}
