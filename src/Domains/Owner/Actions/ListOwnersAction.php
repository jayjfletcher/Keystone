<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Owner\Actions;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;
use RefactorCircus\Keystone\Domains\Owner\Events\OwnersListedActionEvent;
use RefactorCircus\Keystone\Domains\Owner\Events\OwnersListingActionEvent;
use RefactorCircus\Keystone\Domains\Owner\Models\OwnerModel;

final class ListOwnersAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'type' => ['sometimes', 'nullable', 'string', 'max:100'],
            // Direct children of this owner code.
            'parent' => ['sometimes', 'nullable', 'string', 'max:191'],
            // Everything beneath this owner code, at any depth.
            'under' => ['sometimes', 'nullable', 'string', 'max:191'],
            'roots' => ['sometimes', 'boolean'],
            'search' => ['sometimes', 'nullable', 'string', 'max:191'],
            'cursor' => ['sometimes', 'nullable', 'string'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:'.config('keystone.pagination.max_per_page', 100)],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, OwnerModel>
     */
    public function execute(array $filters = []): CursorPaginator
    {
        OwnersListingActionEvent::dispatch($filters);

        $result = $this->perform($filters);

        OwnersListedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, OwnerModel>
     */
    private function perform(array $filters): CursorPaginator
    {
        $query = OwnerModel::query()
            ->with(['type', 'parent'])
            ->withCount('children')
            ->orderBy('code');

        if (is_string($filters['type'] ?? null) && $filters['type'] !== '') {
            $type = $filters['type'];

            $query->whereHas('type', fn (Builder $types): Builder => $types->where('code', $type));
        }

        if (is_string($filters['parent'] ?? null) && $filters['parent'] !== '') {
            $parent = $filters['parent'];

            $query->whereHas('parent', fn (Builder $parents): Builder => $parents->where('code', $parent));
        }

        if (is_string($filters['under'] ?? null) && $filters['under'] !== '') {
            $ancestor = OwnerModel::query()->where('code', $filters['under'])->first();

            $ancestor === null
                ? $query->whereRaw('1 = 0')
                : $query->subtreeOf($ancestor)->whereKeyNot($ancestor->getKey());
        }

        if (filter_var($filters['roots'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $query->whereNull('parent_id');
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
