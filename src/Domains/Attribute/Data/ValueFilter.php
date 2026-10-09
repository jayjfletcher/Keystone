<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Data;

use RefactorCircus\Keystone\Domains\Attribute\Services\Values;

/**
 * Narrows the values a read returns to one channel and some locales, as an
 * export to a single storefront needs. Channel- and locale-independent
 * values always stay.
 */
final readonly class ValueFilter
{
    /**
     * @param  array<int, string>|null  $locales
     */
    public function __construct(
        public ?string $scope = null,
        public ?array $locales = null,
    ) {}

    /**
     * The request rules that describe a filter.
     *
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'scope' => ['sometimes', 'nullable', 'string', 'exists:keystone_channels,code'],
            'locales' => ['sometimes', 'nullable', 'array', 'list'],
            'locales.*' => ['string', 'exists:keystone_locales,code'],
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public static function fromArray(array $input): self
    {
        $locales = is_array($input['locales'] ?? null)
            ? array_values(array_filter($input['locales'], 'is_string'))
            : null;

        return new self(
            scope: is_string($input['scope'] ?? null) ? $input['scope'] : null,
            locales: $locales,
        );
    }

    public function isEmpty(): bool
    {
        return $this->scope === null && $this->locales === null;
    }

    /**
     * @param  array<string, array<string, array<string, mixed>>>  $values
     * @return array<string, array<string, array<string, mixed>>>
     */
    public function apply(array $values): array
    {
        if ($this->isEmpty()) {
            return $values;
        }

        foreach ($values as $code => $channels) {
            foreach ($channels as $channel => $locales) {
                if ($this->scope !== null && $channel !== Values::ALL_CHANNELS && $channel !== $this->scope) {
                    unset($values[$code][$channel]);

                    continue;
                }

                foreach (array_keys($locales) as $locale) {
                    if ($this->locales !== null && $locale !== Values::ALL_LOCALES && ! in_array($locale, $this->locales, true)) {
                        unset($values[$code][$channel][$locale]);
                    }
                }

                if (($values[$code][$channel] ?? null) === []) {
                    unset($values[$code][$channel]);
                }
            }

            if (($values[$code] ?? null) === []) {
                unset($values[$code]);
            }
        }

        return $values;
    }
}
