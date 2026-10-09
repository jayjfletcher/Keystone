<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Owner\Concerns;

use RefactorCircus\Keystone\Domains\Owner\Models\OwnerTypeModel;

/**
 * Parent-type rules shared by creating and updating an owner type.
 */
trait WritesOwnerTypes
{
    /**
     * @return array<string, mixed>
     */
    private static function typeRules(): array
    {
        return [
            'labels' => ['sometimes', 'nullable', 'array'],
            'labels.*' => ['nullable', 'string', 'max:255'],
            // null: any type may be the parent; a list: only these, [] for none.
            'parent_types' => ['sometimes', 'nullable', 'array', 'list'],
            'parent_types.*' => ['string', 'distinct', 'exists:keystone_owner_types,code'],
            'can_be_root' => ['sometimes', 'boolean'],
            'owns_products' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    /**
     * @param  array<int, string>|null  $codes
     */
    private function setParentTypes(OwnerTypeModel $type, ?array $codes): void
    {
        $type->restricts_parents = $codes !== null;
        $type->save();

        $type->parentTypes()->sync(
            $codes === null ? [] : OwnerTypeModel::query()->whereIn('code', $codes)->pluck('id')->all(),
        );
    }
}
