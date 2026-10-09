<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Impex\Sources;

use RefactorCircus\Impex\Domains\Batch\Contracts\BatchSource;
use RefactorCircus\Impex\Domains\Batch\Data\BatchChunk;
use RefactorCircus\Impex\Domains\Batch\Data\BatchChunkItem;

/**
 * Product records passed in with the run — an ERP push, an inbound webhook.
 * The records are part of the run's input, so their positions are stable.
 */
final class InlineProductSource implements BatchSource
{
    /**
     * @param  array<int, mixed>  $products
     */
    public function __construct(
        private readonly array $products,
        private readonly string $mode,
    ) {}

    public function chunk(?string $cursor, int $size): BatchChunk
    {
        $start = (int) ($cursor ?? 0);
        $page = array_slice($this->products, $start, $size, true);

        $items = [];

        foreach ($page as $index => $record) {
            $items[] = new BatchChunkItem((string) $index, ['mode' => $this->mode, 'record' => is_array($record) ? $record : ['_invalid' => $record]]);
        }

        $next = $start + count($page);

        return $next >= count($this->products)
            ? BatchChunk::last($items)
            : BatchChunk::of($items, (string) $next);
    }
}
