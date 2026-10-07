<?php

declare(strict_types=1);

namespace JayI\Keystone\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JayI\Keystone\Domains\Attribute\Enums\AttributeType;
use JayI\Keystone\Domains\Attribute\Models\AttributeModel;

/**
 * @extends Factory<AttributeModel>
 */
final class AttributeFactory extends Factory
{
    protected $model = AttributeModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = 'attr_'.fake()->unique()->lexify('??????');

        return [
            'code' => $code,
            'type' => AttributeType::Text,
            'labels' => ['en' => str($code)->headline()->toString()],
            'is_unique' => false,
            'is_localizable' => false,
            'is_scopable' => false,
            'settings' => [],
            'sort_order' => 0,
        ];
    }

    public function type(AttributeType $type): self
    {
        return $this->state(fn (): array => ['type' => $type]);
    }

    public function select(): self
    {
        return $this->type(AttributeType::Select);
    }
}
