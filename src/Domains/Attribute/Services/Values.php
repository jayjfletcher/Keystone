<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Services;

/**
 * Converts attribute values between the two shapes Keystone uses.
 *
 * - **Standard** (API, MCP, Actions): per attribute, a list of slots —
 *   `{"name": [{"locale": "en", "scope": null, "data": "Shoe"}]}`.
 * - **Storage** (the `values` JSON column): per attribute, channel, then
 *   locale — `{"name": {"<all_channels>": {"en": "Shoe"}}}` — so a database
 *   can address any slot with a plain JSON path.
 */
final class Values
{
    public const string ALL_CHANNELS = '<all_channels>';

    public const string ALL_LOCALES = '<all_locales>';

    /**
     * @param  array<string, array<string, array<string, mixed>>>  $storage
     * @return array<string, array<int, array{locale: string|null, scope: string|null, data: mixed}>>
     */
    public static function toStandard(array $storage): array
    {
        $standard = [];

        ksort($storage);

        foreach ($storage as $code => $channels) {
            foreach ($channels as $channel => $locales) {
                foreach ($locales as $locale => $data) {
                    $standard[$code][] = [
                        'locale' => $locale === self::ALL_LOCALES ? null : (string) $locale,
                        'scope' => $channel === self::ALL_CHANNELS ? null : (string) $channel,
                        'data' => $data,
                    ];
                }
            }
        }

        return $standard;
    }

    /**
     * Apply a patch of slots to stored values. A slot whose data is null is
     * removed, and an attribute left with no slots disappears.
     *
     * @param  array<string, array<string, array<string, mixed>>>  $storage
     * @param  array<string, array<string, array<string, mixed>>>  $patch
     * @return array<string, array<string, array<string, mixed>>>
     */
    public static function apply(array $storage, array $patch): array
    {
        foreach ($patch as $code => $channels) {
            foreach ($channels as $channel => $locales) {
                foreach ($locales as $locale => $data) {
                    if ($data === null) {
                        unset($storage[$code][$channel][$locale]);

                        if (($storage[$code][$channel] ?? null) === []) {
                            unset($storage[$code][$channel]);
                        }

                        if (($storage[$code] ?? null) === []) {
                            unset($storage[$code]);
                        }

                        continue;
                    }

                    $storage[$code][$channel][$locale] = $data;
                }
            }
        }

        return $storage;
    }

    /**
     * The data in one slot, or null.
     *
     * @param  array<string, array<string, array<string, mixed>>>  $storage
     */
    public static function get(array $storage, string $code, ?string $scope = null, ?string $locale = null): mixed
    {
        return $storage[$code][$scope ?? self::ALL_CHANNELS][$locale ?? self::ALL_LOCALES] ?? null;
    }

    /**
     * Drop every slot of the given attributes.
     *
     * @param  array<string, array<string, array<string, mixed>>>  $storage
     * @param  array<int, string>  $codes
     * @return array<string, array<string, array<string, mixed>>>
     */
    public static function without(array $storage, array $codes): array
    {
        return array_diff_key($storage, array_flip($codes));
    }
}
