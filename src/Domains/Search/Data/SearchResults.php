<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Search\Data;

/**
 * What an engine found: one page of product ids, the total, and any facets.
 */
final readonly class SearchResults
{
    /**
     * @param  array<int, string>  $ids  In result order.
     * @param  array<string, array<string, int>>  $facets  Attribute code => value => count.
     */
    public function __construct(
        public array $ids,
        public int $total,
        public array $facets = [],
    ) {}
}
