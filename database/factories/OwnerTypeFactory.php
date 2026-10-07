<?php

declare(strict_types=1);

namespace JayI\Keystone\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JayI\Keystone\Domains\Owner\Models\OwnerTypeModel;

/**
 * @extends Factory<OwnerTypeModel>
 */
final class OwnerTypeFactory extends Factory
{
    protected $model = OwnerTypeModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'type_'.fake()->unique()->lexify('??????'),
            'labels' => [],
            'restricts_parents' => false,
            'can_be_root' => true,
            'owns_products' => true,
            'sort_order' => 0,
        ];
    }
}
