<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Impex\Flows;

use RefactorCircus\Impex\Domains\Flow\Support\Flow;
use RefactorCircus\Showroom\Impex\Actions\ImportProductRow;
use RefactorCircus\Showroom\Impex\Sources\FileProductSource;

/**
 * `showroom:import-products` — a `.csv` or `.jsonl` asset into products.
 *
 * One batch step however many rows: rows retry on their own, and a failed
 * row is counted and kept rather than failing the run — unless more fail
 * than `showroom.impex.allow_failures` tolerates.
 */
final class ImportProductsFlow extends Flow
{
    /**
     * @return array<string, mixed>
     */
    public function handle(string $asset, string $format, string $mode = 'upsert'): array
    {
        $this->tag('showroom', 'import');

        return $this->batch(FileProductSource::class, $asset, $format, $mode)
            ->using(ImportProductRow::class)
            ->chunk((int) config('showroom.impex.chunk', 500))
            ->allowFailures((float) config('showroom.impex.allow_failures', 1.0))
            ->tries((int) config('showroom.impex.tries', 1))
            ->run();
    }
}
