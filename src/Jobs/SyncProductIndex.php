<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use RefactorCircus\Keystone\Domains\Search\Services\ProductIndex;

/**
 * Write a batch of products to the search index. Carries ids only, so a
 * message stays small however large the products are.
 */
final class SyncProductIndex implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    /**
     * @param  array<int, string>  $ids
     */
    public function __construct(public array $ids) {}

    public function handle(ProductIndex $index): void
    {
        $index->sync($this->ids);
    }
}
