<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Family\Actions;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;
use RefactorCircus\Keystone\Domains\Family\Events\FamiliesListedActionEvent;
use RefactorCircus\Keystone\Domains\Family\Events\FamiliesListingActionEvent;
use RefactorCircus\Keystone\Domains\Family\Models\FamilyModel;

final class ListFamiliesAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'attribute' => ['sometimes', 'nullable', 'string', 'max:100'],
            'cursor' => ['sometimes', 'nullable', 'string'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:'.config('keystone.pagination.max_per_page', 100)],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, FamilyModel>
     */
    public function execute(array $filters = []): CursorPaginator
    {
        FamiliesListingActionEvent::dispatch($filters);

        $result = $this->perform($filters);

        FamiliesListedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, FamilyModel>
     */
    private function perform(array $filters): CursorPaginator
    {
        $query = FamilyModel::query()
            ->with('labelAttribute')
            ->withCount('familyAttributes')
            ->orderBy('sort_order')
            ->orderBy('code');

        if (is_string($filters['search'] ?? null) && $filters['search'] !== '') {
            $query->search($filters['search']);
        }

        // Which families use an attribute: what changes if the attribute does.
        if (is_string($filters['attribute'] ?? null) && $filters['attribute'] !== '') {
            $attribute = $filters['attribute'];

            $query->whereHas('familyAttributes', fn (Builder $attributes): Builder => $attributes->where('code', $attribute));
        }

        return $query->cursorPaginate(
            perPage: isset($filters['per_page']) ? (int) $filters['per_page'] : (int) config('keystone.pagination.per_page', 25),
            cursor: is_string($filters['cursor'] ?? null) ? $filters['cursor'] : null,
        );
    }
}
