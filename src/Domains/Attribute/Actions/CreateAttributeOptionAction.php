<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Actions;

use Illuminate\Validation\ValidationException;
use JayI\Keystone\Domains\Attribute\Events\AttributeOptionCreatedActionEvent;
use JayI\Keystone\Domains\Attribute\Events\AttributeOptionCreatingActionEvent;
use JayI\Keystone\Domains\Attribute\Exceptions\AttributeHasNoOptionsException;
use JayI\Keystone\Domains\Attribute\Models\AttributeModel;
use JayI\Keystone\Domains\Attribute\Models\AttributeOptionModel;

final class CreateAttributeOptionAction
{
    /**
     * The code is unique within its attribute, which a static rule cannot
     * express; `execute()` checks it.
     *
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9][a-z0-9_]*$/'],
            'labels' => ['sometimes', 'nullable', 'array'],
            'labels.*' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function execute(AttributeModel $attribute, array $data): AttributeOptionModel
    {
        AttributeOptionCreatingActionEvent::dispatch($attribute, $data);

        $result = $this->perform($attribute, $data);

        AttributeOptionCreatedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(AttributeModel $attribute, array $data): AttributeOptionModel
    {
        if (! $attribute->type->hasOptions()) {
            throw AttributeHasNoOptionsException::for($attribute);
        }

        if ($attribute->options()->where('code', $data['code'])->exists()) {
            throw ValidationException::withMessages([
                'code' => sprintf('Attribute "%s" already has an option with this code.', $attribute->code),
            ]);
        }

        /** @var AttributeOptionModel $option */
        $option = $attribute->options()->create([
            'code' => $data['code'],
            'labels' => $data['labels'] ?? [],
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return $option;
    }
}
