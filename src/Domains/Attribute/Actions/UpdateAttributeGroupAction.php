<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Actions;

use RefactorCircus\Keystone\Domains\Attribute\Events\AttributeGroupUpdatedActionEvent;
use RefactorCircus\Keystone\Domains\Attribute\Events\AttributeGroupUpdatingActionEvent;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeGroupModel;

final class UpdateAttributeGroupAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            // A code is an identifier other systems hold on to; it never changes.
            'code' => ['prohibited'],
            'labels' => ['sometimes', 'nullable', 'array'],
            'labels.*' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(AttributeGroupModel $group, array $data): AttributeGroupModel
    {
        AttributeGroupUpdatingActionEvent::dispatch($group, $data);

        $result = $this->perform($group, $data);

        AttributeGroupUpdatedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(AttributeGroupModel $group, array $data): AttributeGroupModel
    {
        if (array_key_exists('labels', $data)) {
            $group->labels = is_array($data['labels']) ? $data['labels'] : [];
        }

        if (array_key_exists('sort_order', $data)) {
            $group->sort_order = (int) $data['sort_order'];
        }

        $group->save();

        return $group;
    }
}
