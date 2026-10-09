<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Channel\Actions;

use Illuminate\Contracts\Pagination\CursorPaginator;
use RefactorCircus\Keystone\Domains\Channel\Events\ChannelsListedActionEvent;
use RefactorCircus\Keystone\Domains\Channel\Events\ChannelsListingActionEvent;
use RefactorCircus\Keystone\Domains\Channel\Models\ChannelModel;

final class ListChannelsAction
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
     * @return CursorPaginator<int, ChannelModel>
     */
    public function execute(array $filters = []): CursorPaginator
    {
        ChannelsListingActionEvent::dispatch($filters);

        $result = $this->perform($filters);

        ChannelsListedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, ChannelModel>
     */
    private function perform(array $filters): CursorPaginator
    {
        $query = ChannelModel::query()->with(['locales', 'categoryTree'])->orderBy('code');

        if (is_string($filters['search'] ?? null) && $filters['search'] !== '') {
            $query->search($filters['search']);
        }

        return $query->cursorPaginate(
            perPage: isset($filters['per_page']) ? (int) $filters['per_page'] : (int) config('keystone.pagination.per_page', 25),
            cursor: is_string($filters['cursor'] ?? null) ? $filters['cursor'] : null,
        );
    }
}
