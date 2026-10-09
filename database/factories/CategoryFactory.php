<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use RefactorCircus\Showroom\Domains\Category\Models\CategoryModel;

/**
 * A root category. Build trees through CreateCategoryAction, which keeps paths.
 *
 * @extends Factory<CategoryModel>
 */
final class CategoryFactory extends Factory
{
    protected $model = CategoryModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'category_'.fake()->unique()->lexify('??????'),
            'labels' => [],
            'sort_order' => 0,
            'path' => '/',
            'depth' => 0,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (CategoryModel $category): void {
            $category->forceFill(['path' => $category->pathUnder(null)])->save();
        });
    }
}
