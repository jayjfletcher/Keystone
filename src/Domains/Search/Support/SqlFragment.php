<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Search\Support;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Grammar;

/**
 * A column expression built from grammar-wrapped identifiers only, never
 * from input, so it can stand where a column name would.
 */
final readonly class SqlFragment implements Expression
{
    public function __construct(private string $sql) {}

    public function getValue(Grammar $grammar): string
    {
        return $this->sql;
    }
}
