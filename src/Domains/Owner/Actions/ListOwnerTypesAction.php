<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Owner\Actions;

use Illuminate\Contracts\Pagination\CursorPaginator;
use RefactorCircus\Keystone\Domains\Owner\Events\OwnerTypesListedActionEvent;
use RefactorCircus\Keystone\Domains\Owner\Events\OwnerTypesListingActionEvent;
use RefactorCircus\Keystone\Domains\Owner\Models\OwnerTypeModel;

final class ListOwnerTypesAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'cursor' => ['sometimes', 'nullable', 'string'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:'.config('keystone.pagination.max_per_page', 100)],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, OwnerTypeModel>
     */
    public function execute(array $filters = []): CursorPaginator
    {
        OwnerTypesListingActionEvent::dispatch($filters);

        $result = $this->perform($filters);

        OwnerTypesListedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, OwnerTypeModel>
     */
    private function perform(array $filters): CursorPaginator
    {
        $query = OwnerTypeModel::query()
            ->with('parentTypes')
            ->withCount('owners')
            ->orderBy('sort_order')
            ->orderBy('code');

        if (is_string($filters['search'] ?? null) && $filters['search'] !== '') {
            $query->search($filters['search']);
        }

        return $query->cursorPaginate(
            perPage: isset($filters['per_page']) ? (int) $filters['per_page'] : (int) config('keystone.pagination.per_page', 25),
            cursor: is_string($filters['cursor'] ?? null) ? $filters['cursor'] : null,
        );
    }
}
