<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Atrium\Support;

/**
 * The dashboard takes category codes as one comma-separated field.
 */
final class CategoryCodes
{
    /**
     * @return array<int, string>
     */
    public static function fromForm(mixed $input): array
    {
        $codes = array_map('trim', explode(',', is_string($input) ? $input : ''));

        return array_values(array_unique(array_filter($codes, fn (string $code): bool => $code !== '')));
    }
}
