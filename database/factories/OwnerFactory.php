<?php

declare(strict_types=1);

namespace JayI\Keystone\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JayI\Keystone\Domains\Owner\Models\OwnerModel;
use JayI\Keystone\Domains\Owner\Models\OwnerTypeModel;

/**
 * A root owner. Build chains through CreateOwnerAction, which keeps paths.
 *
 * @extends Factory<OwnerModel>
 */
final class OwnerFactory extends Factory
{
    protected $model = OwnerModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_type_id' => OwnerTypeModel::factory(),
            'code' => 'owner-'.fake()->unique()->lexify('??????'),
            'labels' => [],
            'path' => '/',
            'depth' => 0,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (OwnerModel $owner): void {
            $owner->forceFill(['path' => $owner->pathUnder(null)])->save();
        });
    }
}
