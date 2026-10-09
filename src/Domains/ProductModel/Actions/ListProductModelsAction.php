<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\ProductModel\Actions;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;
use RefactorCircus\Showroom\Domains\ProductModel\Events\ProductModelsListedActionEvent;
use RefactorCircus\Showroom\Domains\ProductModel\Events\ProductModelsListingActionEvent;
use RefactorCircus\Showroom\Domains\ProductModel\Models\ProductModelModel;

final class ListProductModelsAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'family_variant' => ['sometimes', 'nullable', 'string', 'max:100'],
            'parent' => ['sometimes', 'nullable', 'string', 'max:191'],
            'roots' => ['sometimes', 'boolean'],
            'search' => ['sometimes', 'nullable', 'string', 'max:191'],
            'cursor' => ['sometimes', 'nullable', 'string'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:'.config('showroom.pagination.max_per_page', 100)],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, ProductModelModel>
     */
    public function execute(array $filters = []): CursorPaginator
    {
        ProductModelsListingActionEvent::dispatch($filters);

        $result = $this->perform($filters);

        ProductModelsListedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, ProductModelModel>
     */
    private function perform(array $filters): CursorPaginator
    {
        $query = ProductModelModel::query()
            ->with(['familyVariant', 'parent'])
            ->orderBy('code');

        if (is_string($filters['family_variant'] ?? null) && $filters['family_variant'] !== '') {
            $variant = $filters['family_variant'];

            $query->whereHas('familyVariant', fn (Builder $variants): Builder => $variants->where('code', $variant));
        }

        if (is_string($filters['parent'] ?? null) && $filters['parent'] !== '') {
            $parent = $filters['parent'];

            $query->whereHas('parent', fn (Builder $parents): Builder => $parents->where('code', $parent));
        }

        if (filter_var($filters['roots'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $query->whereNull('parent_id');
        }

        if (is_string($filters['search'] ?? null) && $filters['search'] !== '') {
            $query->where('code', 'like', '%'.$filters['search'].'%');
        }

        return $query->cursorPaginate(
            perPage: isset($filters['per_page']) ? (int) $filters['per_page'] : (int) config('showroom.pagination.per_page', 25),
            cursor: is_string($filters['cursor'] ?? null) ? $filters['cursor'] : null,
        );
    }
}
