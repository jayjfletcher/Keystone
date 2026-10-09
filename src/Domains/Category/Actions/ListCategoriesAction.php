<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Category\Actions;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;
use RefactorCircus\Keystone\Domains\Category\Events\CategoriesListedActionEvent;
use RefactorCircus\Keystone\Domains\Category\Events\CategoriesListingActionEvent;
use RefactorCircus\Keystone\Domains\Category\Models\CategoryModel;

final class ListCategoriesAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            // Only tree roots: the list of category trees.
            'roots' => ['sometimes', 'boolean'],
            // Direct children of this category code.
            'parent' => ['sometimes', 'nullable', 'string', 'max:100'],
            // Everything beneath this category code, at any depth.
            'under' => ['sometimes', 'nullable', 'string', 'max:100'],
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'cursor' => ['sometimes', 'nullable', 'string'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:'.config('keystone.pagination.max_per_page', 100)],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, CategoryModel>
     */
    public function execute(array $filters = []): CursorPaginator
    {
        CategoriesListingActionEvent::dispatch($filters);

        $result = $this->perform($filters);

        CategoriesListedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, CategoryModel>
     */
    private function perform(array $filters): CursorPaginator
    {
        $query = CategoryModel::query()
            ->with('parent')
            ->withCount('children')
            ->orderBy('sort_order')
            ->orderBy('code');

        if (filter_var($filters['roots'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $query->whereNull('parent_id');
        }

        if (is_string($filters['parent'] ?? null) && $filters['parent'] !== '') {
            $parent = $filters['parent'];

            $query->whereHas('parent', fn (Builder $parents): Builder => $parents->where('code', $parent));
        }

        if (is_string($filters['under'] ?? null) && $filters['under'] !== '') {
            $ancestor = CategoryModel::query()->where('code', $filters['under'])->first();

            $ancestor === null
                ? $query->whereRaw('1 = 0')
                : $query->subtreeOf($ancestor)->whereKeyNot($ancestor->getKey());
        }

        if (is_string($filters['search'] ?? null) && $filters['search'] !== '') {
            $query->search($filters['search']);
        }

        return $query->cursorPaginate(
            perPage: isset($filters['per_page']) ? (int) $filters['per_page'] : (int) config('keystone.pagination.per_page', 25),
            cursor: is_string($filters['cursor'] ?? null) ? $filters['cursor'] : null,
        );
    }
}
