<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Search\Console\Commands;

use Illuminate\Console\Command;
use RefactorCircus\Keystone\Domains\Search\Contracts\SearchEngine;
use RefactorCircus\Keystone\Domains\Search\Services\ProductIndex;

final class ReindexProductsCommand extends Command
{
    protected $signature = 'keystone:search:reindex';

    protected $description = 'Recompute product completeness and rebuild the search index';

    public function handle(SearchEngine $engine, ProductIndex $index): int
    {
        $count = $index->rebuild();

        $this->components->info($engine->maintainsIndex()
            ? sprintf('Indexed %d products.', $count)
            : sprintf('Refreshed completeness for %d products; the database engine keeps no index.', $count));

        return self::SUCCESS;
    }
}
