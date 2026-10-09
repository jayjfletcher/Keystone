<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Actions;

use RefactorCircus\Keystone\Domains\Attribute\Events\AttributeGroupCreatedActionEvent;
use RefactorCircus\Keystone\Domains\Attribute\Events\AttributeGroupCreatingActionEvent;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeGroupModel;

final class CreateAttributeGroupAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/', 'unique:keystone_attribute_groups,code'],
            'labels' => ['sometimes', 'nullable', 'array'],
            'labels.*' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): AttributeGroupModel
    {
        AttributeGroupCreatingActionEvent::dispatch($data);

        $result = $this->perform($data);

        AttributeGroupCreatedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(array $data): AttributeGroupModel
    {
        return AttributeGroupModel::query()->create([
            'code' => $data['code'],
            'labels' => $data['labels'] ?? [],
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
    }
}
