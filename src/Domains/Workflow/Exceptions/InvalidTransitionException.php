<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Workflow\Exceptions;

use RefactorCircus\Keystone\Domains\Product\Enums\ProductStatus;
use RefactorCircus\Keystone\Domains\Workflow\Enums\Transition;
use RefactorCircus\Keystone\Exceptions\KeystoneException;

final class InvalidTransitionException extends KeystoneException
{
    /**
     * @param  array<int, ProductStatus>  $from
     */
    public static function from(string $product, Transition $transition, ProductStatus $status, array $from): self
    {
        return new self(sprintf(
            'Product "%s" is %s, so it cannot %s. It must be %s.',
            $product,
            str_replace('_', ' ', $status->value),
            $transition->value,
            implode(' or ', array_map(fn (ProductStatus $status): string => str_replace('_', ' ', $status->value), $from)),
        ));
    }

    /**
     * @param  array<int, string>  $incomplete
     */
    public static function incomplete(string $product, array $incomplete): self
    {
        return new self(sprintf(
            'Product "%s" is not complete for %s, so it cannot be submitted.',
            $product,
            $incomplete === [] ? 'any channel: it has no family' : implode(', ', $incomplete),
        ));
    }
}
