<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Family\Concerns;

use Illuminate\Validation\ValidationException;
use RefactorCircus\Showroom\Domains\Attribute\Enums\AttributeType;
use RefactorCircus\Showroom\Domains\Attribute\Models\AttributeModel;
use RefactorCircus\Showroom\Domains\Family\Models\FamilyModel;

/**
 * Membership and label rules shared by creating and updating a family.
 */
trait WritesFamilyAttributes
{
    /**
     * @return array<string, mixed>
     */
    private static function familyRules(): array
    {
        return [
            'labels' => ['sometimes', 'nullable', 'array'],
            'labels.*' => ['nullable', 'string', 'max:255'],
            'label_attribute' => ['sometimes', 'nullable', 'string', 'exists:showroom_attributes,code'],
            'attributes' => ['sometimes', 'array'],
            'attributes.*' => ['array'],
            'attributes.*.attribute' => ['required', 'string', 'distinct', 'exists:showroom_attributes,code'],
            'attributes.*.is_required' => ['sometimes', 'boolean'],
            'attributes.*.sort_order' => ['sometimes', 'integer', 'min:0'],
            // Narrow a requirement to some channels; without it, a required
            // attribute is required on every channel.
            'attributes.*.required_channels' => ['sometimes', 'nullable', 'array', 'list'],
            'attributes.*.required_channels.*' => ['string', 'distinct', 'exists:showroom_channels,code'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    /**
     * Replace the family's attributes with the given list, in its order
     * unless each entry sets its own `sort_order`.
     *
     * @param  array<int, mixed>  $members
     */
    private function replaceAttributes(FamilyModel $family, array $members): void
    {
        $codes = [];

        foreach ($members as $member) {
            if (is_array($member) && is_string($member['attribute'] ?? null)) {
                $codes[] = $member['attribute'];
            }
        }

        $ids = AttributeModel::query()->whereIn('code', $codes)->pluck('id', 'code');

        $sync = [];

        foreach (array_values($members) as $position => $member) {
            if (! is_array($member) || ! is_string($member['attribute'] ?? null) || ! $ids->has($member['attribute'])) {
                continue;
            }

            $sync[(string) $ids->get($member['attribute'])] = [
                'is_required' => (bool) ($member['is_required'] ?? false),
                'sort_order' => isset($member['sort_order']) ? (int) $member['sort_order'] : $position,
                'required_channels' => is_array($member['required_channels'] ?? null)
                    ? json_encode(array_values($member['required_channels']), JSON_THROW_ON_ERROR)
                    : null,
            ];
        }

        $family->familyAttributes()->sync($sync);
    }

    /**
     * Refuse to drop an attribute a family variant places on a level.
     *
     * @param  array<int, mixed>  $members
     *
     * @throws ValidationException
     */
    private function keepVariantAttributes(FamilyModel $family, array $members): void
    {
        $kept = [];

        foreach ($members as $member) {
            if (is_array($member) && is_string($member['attribute'] ?? null)) {
                $kept[] = $member['attribute'];
            }
        }

        foreach ($family->variants()->with('variantAttributes')->get() as $variant) {
            foreach ($variant->variantAttributes as $attribute) {
                if (! in_array($attribute->code, $kept, true)) {
                    throw ValidationException::withMessages([
                        'attributes' => sprintf('Attribute "%s" is placed in family variant "%s", so it must stay in the family.', $attribute->code, $variant->code),
                    ]);
                }
            }
        }
    }

    /**
     * Point the family at its label attribute, which must be one of its own
     * text attributes, or at none.
     *
     * @throws ValidationException
     */
    private function assignLabelAttribute(FamilyModel $family, ?string $code): void
    {
        if ($code === null) {
            $family->label_attribute_id = null;

            return;
        }

        $attribute = $family->familyAttributes()->where('code', $code)->first();

        if ($attribute === null) {
            throw ValidationException::withMessages([
                'label_attribute' => sprintf('Attribute "%s" is not in family "%s". Add it to the family\'s attributes first.', $code, $family->code),
            ]);
        }

        if ($attribute->type !== AttributeType::Text) {
            throw ValidationException::withMessages([
                'label_attribute' => sprintf('The label attribute must be a text attribute; "%s" is %s.', $code, $attribute->type->value),
            ]);
        }

        $family->label_attribute_id = $attribute->id;
    }
}
