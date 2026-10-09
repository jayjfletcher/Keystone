<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Actions;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use RefactorCircus\Keystone\Domains\Attribute\Enums\AttributeType;
use RefactorCircus\Keystone\Domains\Attribute\Events\AttributesListedActionEvent;
use RefactorCircus\Keystone\Domains\Attribute\Events\AttributesListingActionEvent;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeModel;

final class ListAttributesAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'type' => ['sometimes', 'nullable', Rule::enum(AttributeType::class)],
            'group' => ['sometimes', 'nullable', 'string', 'max:100'],
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'cursor' => ['sometimes', 'nullable', 'string'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:'.config('keystone.pagination.max_per_page', 100)],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, AttributeModel>
     */
    public function execute(array $filters = []): CursorPaginator
    {
        AttributesListingActionEvent::dispatch($filters);

        $result = $this->perform($filters);

        AttributesListedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, AttributeModel>
     */
    private function perform(array $filters): CursorPaginator
    {
        $query = AttributeModel::query()
            ->with('group')
            ->orderBy('sort_order')
            ->orderBy('code');

        if (is_string($filters['type'] ?? null) && $filters['type'] !== '') {
            $query->where('type', $filters['type']);
        }

        if (is_string($filters['group'] ?? null) && $filters['group'] !== '') {
            $group = $filters['group'];

            $query->whereHas('group', fn (Builder $groups): Builder => $groups->where('code', $group));
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
