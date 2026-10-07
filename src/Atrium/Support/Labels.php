<?php

declare(strict_types=1);

namespace JayI\Keystone\Atrium\Support;

/**
 * The dashboard edits one label — the current locale's — while the Actions
 * replace the whole map. Merging here keeps every other locale's label.
 */
final class Labels
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, string>|null  $existing
     * @return array<string, mixed>
     */
    public static function fromForm(array $data, ?array $existing = null): array
    {
        if (! array_key_exists('labels', $data)) {
            return $data;
        }

        /** @var array<string, string|null> $submitted */
        $submitted = is_array($data['labels']) ? $data['labels'] : [];

        $data['labels'] = array_filter(
            array_merge($existing ?? [], $submitted),
            fn (?string $label): bool => $label !== null && $label !== '',
        );

        return $data;
    }
}
