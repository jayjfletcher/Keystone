<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Family\Actions;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;
use RefactorCircus\Keystone\Domains\Family\Events\FamilyVariantsListedActionEvent;
use RefactorCircus\Keystone\Domains\Family\Events\FamilyVariantsListingActionEvent;
use RefactorCircus\Keystone\Domains\Family\Models\FamilyVariantModel;

final class ListFamilyVariantsAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'family' => ['sometimes', 'nullable', 'string', 'max:100'],
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'cursor' => ['sometimes', 'nullable', 'string'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:'.config('keystone.pagination.max_per_page', 100)],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, FamilyVariantModel>
     */
    public function execute(array $filters = []): CursorPaginator
    {
        FamilyVariantsListingActionEvent::dispatch($filters);

        $result = $this->perform($filters);

        FamilyVariantsListedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, FamilyVariantModel>
     */
    private function perform(array $filters): CursorPaginator
    {
        $query = FamilyVariantModel::query()
            ->with(['family.familyAttributes', 'variantAttributes'])
            ->orderBy('code');

        if (is_string($filters['family'] ?? null) && $filters['family'] !== '') {
            $family = $filters['family'];

            $query->whereHas('family', fn (Builder $families): Builder => $families->where('code', $family));
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
