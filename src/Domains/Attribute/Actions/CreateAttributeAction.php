<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Actions;

use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use JayI\Keystone\Domains\Attribute\Enums\AttributeType;
use JayI\Keystone\Domains\Attribute\Events\AttributeCreatedActionEvent;
use JayI\Keystone\Domains\Attribute\Events\AttributeCreatingActionEvent;
use JayI\Keystone\Domains\Attribute\Models\AttributeGroupModel;
use JayI\Keystone\Domains\Attribute\Models\AttributeModel;

final class CreateAttributeAction
{
    /**
     * Type-specific settings are checked against the type in `execute()`,
     * since the rules for them depend on the value of `type`.
     *
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/', 'unique:keystone_attributes,code'],
            'type' => ['required', Rule::enum(AttributeType::class)],
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
    public function execute(array $data): AttributeModel
    {
        AttributeCreatingActionEvent::dispatch($data);

        $result = $this->perform($data);

        AttributeCreatedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(array $data): AttributeModel
    {
        $type = $data['type'] instanceof AttributeType
            ? $data['type']
            : AttributeType::from(is_string($data['type']) ? $data['type'] : '');

        $unique = (bool) ($data['is_unique'] ?? false);

        if ($unique && ! $type->canBeUnique()) {
            throw ValidationException::withMessages([
                'is_unique' => sprintf('A %s attribute cannot be unique.', $type->value),
            ]);
        }

        if ($unique && ((bool) ($data['is_localizable'] ?? false) || (bool) ($data['is_scopable'] ?? false))) {
            throw ValidationException::withMessages([
                'is_unique' => 'A unique attribute cannot be localizable or scopable: it holds one value per product.',
            ]);
        }

        /** @var array<string, mixed> $settings */
        $settings = is_array($data['settings'] ?? null) ? $data['settings'] : [];

        return AttributeModel::query()->create([
            'code' => $data['code'],
            'type' => $type,
            'attribute_group_id' => is_string($data['group'] ?? null)
                ? AttributeGroupModel::query()->where('code', $data['group'])->value('id')
                : null,
            'labels' => $data['labels'] ?? [],
            'is_unique' => $unique,
            'is_localizable' => (bool) ($data['is_localizable'] ?? false),
            'is_scopable' => (bool) ($data['is_scopable'] ?? false),
            'settings' => $type->validateSettings($settings),
            'sort_order' => $data['sort_order'] ?? 0,
        ])->load('group');
    }
}
