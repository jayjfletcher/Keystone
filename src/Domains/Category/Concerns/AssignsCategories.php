<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Category\Concerns;

use RefactorCircus\Keystone\Domains\Category\Models\CategoryModel;
use RefactorCircus\Keystone\Domains\Product\Models\ProductModel;
use RefactorCircus\Keystone\Domains\ProductModel\Models\ProductModelModel;

/**
 * Category assignment shared by the product and product model Actions.
 */
trait AssignsCategories
{
    /**
     * @return array<string, mixed>
     */
    private static function categoryRules(): array
    {
        return [
            // The whole list of category codes, from any trees; replaced on update.
            'categories' => ['sometimes', 'nullable', 'array', 'list'],
            'categories.*' => ['string', 'distinct', 'exists:keystone_categories,code'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assignCategories(ProductModel|ProductModelModel $record, array $data): void
    {
        if (! array_key_exists('categories', $data)) {
            return;
        }

        $codes = is_array($data['categories']) ? $data['categories'] : [];

        $record->categories()->sync(CategoryModel::query()->whereIn('code', $codes)->pluck('id')->all());
    }
}
