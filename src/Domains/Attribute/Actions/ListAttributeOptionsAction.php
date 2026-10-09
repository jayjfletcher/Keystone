<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Actions;

use Illuminate\Contracts\Pagination\CursorPaginator;
use RefactorCircus\Keystone\Domains\Attribute\Events\AttributeOptionsListedActionEvent;
use RefactorCircus\Keystone\Domains\Attribute\Events\AttributeOptionsListingActionEvent;
use RefactorCircus\Keystone\Domains\Attribute\Exceptions\AttributeHasNoOptionsException;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeModel;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeOptionModel;

final class ListAttributeOptionsAction
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
     * @return CursorPaginator<int, AttributeOptionModel>
     */
    public function execute(AttributeModel $attribute, array $filters = []): CursorPaginator
    {
        AttributeOptionsListingActionEvent::dispatch($attribute, $filters);

        $result = $this->perform($attribute, $filters);

        AttributeOptionsListedActionEvent::dispatch($attribute, $result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, AttributeOptionModel>
     */
    private function perform(AttributeModel $attribute, array $filters): CursorPaginator
    {
        if (! $attribute->type->hasOptions()) {
            throw AttributeHasNoOptionsException::for($attribute);
        }

        $query = $attribute->options()->getQuery();

        if (is_string($filters['search'] ?? null) && $filters['search'] !== '') {
            $query->search($filters['search']);
        }

        return $query->cursorPaginate(
            perPage: isset($filters['per_page']) ? (int) $filters['per_page'] : (int) config('keystone.pagination.per_page', 25),
            cursor: is_string($filters['cursor'] ?? null) ? $filters['cursor'] : null,
        );
    }
}
