<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Atrium\Support;

use Illuminate\Support\Collection;
use RefactorCircus\Keystone\Domains\Attribute\Enums\AttributeType;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeModel;

/**
 * Turns the dashboard's value inputs into the standard values shape.
 *
 * The form edits one slot per attribute: the picked locale for localizable
 * attributes and the picked channel for scopable ones. An attribute whose
 * locale or channel cannot be picked — none exists yet — is left alone. An
 * empty input clears its slot.
 */
final class ValueForm
{
    /**
     * @param  array<string, mixed>  $input  `v[code]` from the form.
     * @param  Collection<int, AttributeModel>  $attributes  The attributes the form showed.
     * @return array<string, array<int, array{locale: string|null, scope: string|null, data: mixed}>>
     */
    public static function toValues(array $input, Collection $attributes, EditingSlot $slot): array
    {
        $values = [];

        foreach ($attributes as $attribute) {
            if (($attribute->is_localizable && $slot->locale === null) || ($attribute->is_scopable && $slot->channel === null)) {
                continue;
            }

            $values[$attribute->code] = [[
                'locale' => $attribute->is_localizable ? $slot->locale : null,
                'scope' => $attribute->is_scopable ? $slot->channel : null,
                'data' => self::data($attribute, $input[$attribute->code] ?? null),
            ]];
        }

        return $values;
    }

    private static function data(AttributeModel $attribute, mixed $raw): mixed
    {
        if ($raw === null || $raw === '' || $raw === []) {
            return null;
        }

        return match ($attribute->type) {
            AttributeType::Boolean => $raw === '1',
            AttributeType::Multiselect => array_values(array_filter((array) $raw, fn (mixed $code): bool => is_string($code) && $code !== '')) ?: null,
            AttributeType::Metric => is_array($raw) && ($raw['amount'] ?? '') !== ''
                ? ['amount' => $raw['amount'], 'unit' => $raw['unit'] ?? '']
                : null,
            AttributeType::Price => self::prices($raw),
            default => $raw,
        };
    }

    /**
     * @return array<int, array{amount: mixed, currency: string}>|null
     */
    private static function prices(mixed $raw): ?array
    {
        $prices = [];

        foreach (is_array($raw) ? $raw : [] as $currency => $amount) {
            if (is_string($currency) && $amount !== null && $amount !== '') {
                $prices[] = ['amount' => $amount, 'currency' => $currency];
            }
        }

        return $prices === [] ? null : $prices;
    }
}
