<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Actions;

use Illuminate\Contracts\Pagination\CursorPaginator;
use JayI\Keystone\Domains\Attribute\Events\AttributeGroupsListedActionEvent;
use JayI\Keystone\Domains\Attribute\Events\AttributeGroupsListingActionEvent;
use JayI\Keystone\Domains\Attribute\Models\AttributeGroupModel;

final class ListAttributeGroupsAction
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
     * @return CursorPaginator<int, AttributeGroupModel>
     */
    public function execute(array $filters = []): CursorPaginator
    {
        AttributeGroupsListingActionEvent::dispatch($filters);

        $result = $this->perform($filters);

        AttributeGroupsListedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, AttributeGroupModel>
     */
    private function perform(array $filters): CursorPaginator
    {
        $query = AttributeGroupModel::query()
            ->withCount('groupedAttributes')
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
