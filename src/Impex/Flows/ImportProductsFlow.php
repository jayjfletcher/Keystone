<?php

declare(strict_types=1);

namespace JayI\Keystone\Impex\Flows;

use JayI\Impex\Domains\Flow\Support\Flow;
use JayI\Keystone\Impex\Actions\ImportProductRow;
use JayI\Keystone\Impex\Sources\FileProductSource;

/**
 * `keystone:import-products` — a `.csv` or `.jsonl` asset into products.
 *
 * One batch step however many rows: rows retry on their own, and a failed
 * row is counted and kept rather than failing the run — unless more fail
 * than `keystone.impex.allow_failures` tolerates.
 */
final class ImportProductsFlow extends Flow
{
    /**
     * @return array<string, mixed>
     */
    public function handle(string $asset, string $format, string $mode = 'upsert'): array
    {
        $this->tag('keystone', 'import');

        return $this->batch(FileProductSource::class, $asset, $format, $mode)
            ->using(ImportProductRow::class)
            ->chunk((int) config('keystone.impex.chunk', 500))
            ->allowFailures((float) config('keystone.impex.allow_failures', 1.0))
            ->tries((int) config('keystone.impex.tries', 1))
            ->run();
    }
}
