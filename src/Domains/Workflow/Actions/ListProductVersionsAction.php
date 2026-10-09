<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Workflow\Actions;

use Illuminate\Contracts\Pagination\CursorPaginator;
use RefactorCircus\Showroom\Domains\Product\Models\ProductModel;
use RefactorCircus\Showroom\Domains\Workflow\Events\ProductVersionsListedActionEvent;
use RefactorCircus\Showroom\Domains\Workflow\Events\ProductVersionsListingActionEvent;
use RefactorCircus\Showroom\Domains\Workflow\Models\VersionModel;

final class ListProductVersionsAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'action' => ['sometimes', 'nullable', 'string', 'max:32'],
            'cursor' => ['sometimes', 'nullable', 'string'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:'.config('showroom.pagination.max_per_page', 100)],
        ];
    }

    /**
     * The product's history, newest first.
     *
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, VersionModel>
     */
    public function execute(ProductModel $product, array $filters = []): CursorPaginator
    {
        ProductVersionsListingActionEvent::dispatch($product, $filters);

        $result = $this->perform($product, $filters);

        ProductVersionsListedActionEvent::dispatch($product, $result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, VersionModel>
     */
    private function perform(ProductModel $product, array $filters): CursorPaginator
    {
        $query = $product->versions()->getQuery()->with('author');

        if (is_string($filters['action'] ?? null) && $filters['action'] !== '') {
            $query->where('action', $filters['action']);
        }

        return $query->cursorPaginate(
            perPage: isset($filters['per_page']) ? (int) $filters['per_page'] : (int) config('showroom.pagination.per_page', 25),
            cursor: is_string($filters['cursor'] ?? null) ? $filters['cursor'] : null,
        );
    }
}
