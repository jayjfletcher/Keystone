<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Actions;

use RefactorCircus\Keystone\Domains\Attribute\Events\AttributeOptionUpdatedActionEvent;
use RefactorCircus\Keystone\Domains\Attribute\Events\AttributeOptionUpdatingActionEvent;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeOptionModel;

final class UpdateAttributeOptionAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            // Product values store the option code, so it never changes.
            'code' => ['prohibited'],
            'labels' => ['sometimes', 'nullable', 'array'],
            'labels.*' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(AttributeOptionModel $option, array $data): AttributeOptionModel
    {
        AttributeOptionUpdatingActionEvent::dispatch($option, $data);

        $result = $this->perform($option, $data);

        AttributeOptionUpdatedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(AttributeOptionModel $option, array $data): AttributeOptionModel
    {
        if (array_key_exists('labels', $data)) {
            $option->labels = is_array($data['labels']) ? $data['labels'] : [];
        }

        if (array_key_exists('sort_order', $data)) {
            $option->sort_order = (int) $data['sort_order'];
        }

        $option->save();

        return $option;
    }
}
