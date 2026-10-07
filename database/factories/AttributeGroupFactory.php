<?php

declare(strict_types=1);

namespace JayI\Keystone\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JayI\Keystone\Domains\Attribute\Models\AttributeGroupModel;

/**
 * @extends Factory<AttributeGroupModel>
 */
final class AttributeGroupFactory extends Factory
{
    protected $model = AttributeGroupModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = 'group_'.fake()->unique()->lexify('??????');

        return [
            'code' => $code,
            'labels' => ['en' => str($code)->headline()->toString()],
            'sort_order' => 0,
        ];
    }
}
