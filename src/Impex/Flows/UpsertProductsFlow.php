<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Impex\Flows;

use RefactorCircus\Impex\Domains\Flow\Support\Flow;
use RefactorCircus\Showroom\Impex\Actions\ImportProductRow;
use RefactorCircus\Showroom\Impex\Sources\InlineProductSource;

/**
 * `showroom:upsert-products` — product records passed in with the run: an
 * ERP push through an Impex inbound channel, or a call from code.
 *
 * Accepts a list of records, or `{"products": [...]}` as a webhook body.
 */
final class UpsertProductsFlow extends Flow
{
    /**
     * @param  array<int|string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function handle(array $payload, string $mode = 'upsert'): array
    {
        $this->tag('showroom', 'upsert');

        $products = array_is_list($payload) ? $payload : (array) ($payload['products'] ?? []);

        return $this->batch(InlineProductSource::class, array_values($products), $mode)
            ->using(ImportProductRow::class)
            ->chunk((int) config('showroom.impex.chunk', 500))
            ->allowFailures((float) config('showroom.impex.allow_failures', 1.0))
            ->tries((int) config('showroom.impex.tries', 1))
            ->run();
    }
}
