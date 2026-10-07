<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Enums;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

enum AttributeType: string
{
    case Text = 'text';
    case Textarea = 'textarea';
    case Number = 'number';
    case Decimal = 'decimal';
    case Boolean = 'boolean';
    case Date = 'date';
    case Select = 'select';
    case Multiselect = 'multiselect';
    case Price = 'price';
    case Metric = 'metric';

    /**
     * Whether values of this type are chosen from the attribute's options.
     */
    public function hasOptions(): bool
    {
        return match ($this) {
            self::Select, self::Multiselect => true,
            default => false,
        };
    }

    /**
     * Whether values of this type can be required to be unique across
     * products. Only scalar identifiers can; a unique boolean or price has no
     * meaning.
     */
    public function canBeUnique(): bool
    {
        return match ($this) {
            self::Text, self::Number, self::Decimal, self::Date => true,
            default => false,
        };
    }

    /**
     * Validation rules for the type's `settings`, keyed by setting name.
     *
     * Keys a type does not declare here are dropped, so the stored settings
     * only ever hold what the type understands.
     *
     * @return array<string, array<int, mixed>>
     */
    public function settingsRules(): array
    {
        return match ($this) {
            self::Text => [
                'max_length' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:255'],
                'regex' => ['sometimes', 'nullable', 'string', 'max:255'],
            ],
            self::Textarea => [
                'max_length' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:65535'],
                'rich_text' => ['sometimes', 'boolean'],
            ],
            self::Number => [
                'min' => ['sometimes', 'nullable', 'integer'],
                'max' => ['sometimes', 'nullable', 'integer'],
            ],
            self::Decimal => [
                'min' => ['sometimes', 'nullable', 'numeric'],
                'max' => ['sometimes', 'nullable', 'numeric'],
                'decimals' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:10'],
            ],
            self::Date => [
                'min' => ['sometimes', 'nullable', 'date'],
                'max' => ['sometimes', 'nullable', 'date'],
            ],
            self::Price => [
                'currencies' => ['sometimes', 'array'],
                'currencies.*' => ['string', 'size:3', 'regex:/^[A-Z]{3}$/'],
                'decimals' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:4'],
            ],
            self::Metric => [
                'metric_family' => ['required', 'string', 'max:100'],
                'default_unit' => ['required', 'string', 'max:100'],
            ],
            self::Boolean, self::Select, self::Multiselect => [],
        };
    }

    /**
     * Validate settings for this type, keeping only the keys it understands.
     *
     * Errors are reported under `settings.*`, the same keys the caller sent.
     *
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    public function validateSettings(array $settings): array
    {
        $rules = [];

        foreach ($this->settingsRules() as $key => $rule) {
            $rules['settings.'.$key] = $rule;
        }

        /** @var array{settings?: array<string, mixed>} $validated */
        $validated = Validator::validate(['settings' => $settings], $rules);

        return $validated['settings'] ?? [];
    }
}
