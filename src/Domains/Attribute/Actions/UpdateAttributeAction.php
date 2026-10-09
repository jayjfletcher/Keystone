<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Actions;

use Illuminate\Validation\ValidationException;
use RefactorCircus\Keystone\Domains\Attribute\Events\AttributeUpdatedActionEvent;
use RefactorCircus\Keystone\Domains\Attribute\Events\AttributeUpdatingActionEvent;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeGroupModel;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeModel;

final class UpdateAttributeAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            // Every stored value depends on the code and type, so neither
            // changes once the attribute exists.
            'code' => ['prohibited'],
            'type' => ['prohibited'],
            'group' => ['sometimes', 'nullable', 'string', 'exists:keystone_attribute_groups,code'],
            'labels' => ['sometimes', 'nullable', 'array'],
            'labels.*' => ['nullable', 'string', 'max:255'],
            'is_unique' => ['sometimes', 'boolean'],
            'is_localizable' => ['sometimes', 'boolean'],
            'is_scopable' => ['sometimes', 'boolean'],
            'settings' => ['sometimes', 'nullable', 'array'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function execute(AttributeModel $attribute, array $data): AttributeModel
    {
        AttributeUpdatingActionEvent::dispatch($attribute, $data);

        $result = $this->perform($attribute, $data);

        AttributeUpdatedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(AttributeModel $attribute, array $data): AttributeModel
    {
        if (array_key_exists('is_unique', $data)) {
            $unique = (bool) $data['is_unique'];

            if ($unique && ! $attribute->type->canBeUnique()) {
                throw ValidationException::withMessages([
                    'is_unique' => sprintf('A %s attribute cannot be unique.', $attribute->type->value),
                ]);
            }

            $attribute->is_unique = $unique;
        }

        if (array_key_exists('group', $data)) {
            $attribute->attribute_group_id = is_string($data['group'])
                ? AttributeGroupModel::query()->where('code', $data['group'])->value('id')
                : null;
        }

        if (array_key_exists('labels', $data)) {
            $attribute->labels = is_array($data['labels']) ? $data['labels'] : [];
        }

        foreach (['is_localizable', 'is_scopable'] as $flag) {
            if (array_key_exists($flag, $data)) {
                $attribute->{$flag} = (bool) $data[$flag];
            }
        }

        // Settings are replaced whole, so a caller never has to know which
        // keys were set before to get a predictable result.
        if (array_key_exists('settings', $data)) {
            /** @var array<string, mixed> $settings */
            $settings = is_array($data['settings']) ? $data['settings'] : [];

            $attribute->settings = $attribute->type->validateSettings($settings);
        }

        if (array_key_exists('sort_order', $data)) {
            $attribute->sort_order = (int) $data['sort_order'];
        }

        if ($attribute->is_unique && ($attribute->is_localizable || $attribute->is_scopable)) {
            throw ValidationException::withMessages([
                'is_unique' => 'A unique attribute cannot be localizable or scopable: it holds one value per product.',
            ]);
        }

        $attribute->save();

        return $attribute->load('group');
    }
}
