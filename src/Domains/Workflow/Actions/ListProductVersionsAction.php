<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Workflow\Actions;

use Illuminate\Contracts\Pagination\CursorPaginator;
use JayI\Keystone\Domains\Product\Models\ProductModel;
use JayI\Keystone\Domains\Workflow\Events\ProductVersionsListedActionEvent;
use JayI\Keystone\Domains\Workflow\Events\ProductVersionsListingActionEvent;
use JayI\Keystone\Domains\Workflow\Models\VersionModel;

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
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:'.config('keystone.pagination.max_per_page', 100)],
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
            perPage: isset($filters['per_page']) ? (int) $filters['per_page'] : (int) config('keystone.pagination.per_page', 25),
            cursor: is_string($filters['cursor'] ?? null) ? $filters['cursor'] : null,
        );
    }
}
