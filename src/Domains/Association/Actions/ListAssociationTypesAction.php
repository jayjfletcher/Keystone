<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Association\Actions;

use Illuminate\Contracts\Pagination\CursorPaginator;
use RefactorCircus\Showroom\Domains\Association\Events\AssociationTypesListedActionEvent;
use RefactorCircus\Showroom\Domains\Association\Events\AssociationTypesListingActionEvent;
use RefactorCircus\Showroom\Domains\Association\Models\AssociationTypeModel;

final class ListAssociationTypesAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'cursor' => ['sometimes', 'nullable', 'string'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:'.config('showroom.pagination.max_per_page', 100)],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, AssociationTypeModel>
     */
    public function execute(array $filters = []): CursorPaginator
    {
        AssociationTypesListingActionEvent::dispatch($filters);

        $result = $this->perform($filters);

        AssociationTypesListedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, AssociationTypeModel>
     */
    private function perform(array $filters): CursorPaginator
    {
        $query = AssociationTypeModel::query()->withCount('associations')->orderBy('code');

        if (is_string($filters['search'] ?? null) && $filters['search'] !== '') {
            $query->search($filters['search']);
        }

        return $query->cursorPaginate(
            perPage: isset($filters['per_page']) ? (int) $filters['per_page'] : (int) config('showroom.pagination.per_page', 25),
            cursor: is_string($filters['cursor'] ?? null) ? $filters['cursor'] : null,
        );
    }
}
