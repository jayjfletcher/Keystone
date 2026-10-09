<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Channel\Actions;

use Illuminate\Contracts\Pagination\CursorPaginator;
use RefactorCircus\Showroom\Domains\Channel\Events\LocalesListedActionEvent;
use RefactorCircus\Showroom\Domains\Channel\Events\LocalesListingActionEvent;
use RefactorCircus\Showroom\Domains\Channel\Models\LocaleModel;

final class ListLocalesAction
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
     * @return CursorPaginator<int, LocaleModel>
     */
    public function execute(array $filters = []): CursorPaginator
    {
        LocalesListingActionEvent::dispatch($filters);

        $result = $this->perform($filters);

        LocalesListedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, LocaleModel>
     */
    private function perform(array $filters): CursorPaginator
    {
        $query = LocaleModel::query()->withCount('channels')->orderBy('code');

        if (is_string($filters['search'] ?? null) && $filters['search'] !== '') {
            $query->search($filters['search']);
        }

        return $query->cursorPaginate(
            perPage: isset($filters['per_page']) ? (int) $filters['per_page'] : (int) config('showroom.pagination.per_page', 25),
            cursor: is_string($filters['cursor'] ?? null) ? $filters['cursor'] : null,
        );
    }
}
