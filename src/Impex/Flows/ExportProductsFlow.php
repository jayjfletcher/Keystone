<?php

declare(strict_types=1);

namespace JayI\Keystone\Impex\Flows;

use JayI\Impex\Domains\Flow\Support\Flow;
use JayI\Keystone\Impex\Actions\ExportProductPages;
use JayI\Keystone\Impex\Actions\JoinProductExport;

/**
 * `keystone:export-products` — the products a search matches, to a `.jsonl`
 * or `.csv` asset. With `published`, each product's live version is written
 * instead of its working copy, and unpublished products are left out.
 */
final class ExportProductsFlow extends Flow
{
    /**
     * @param  array<string, mixed>  $query  ListProductsAction filters, plus `scope` and `locales`.
     * @return array{asset: string, count: int, format: string}
     */
    public function handle(array $query = [], string $format = 'jsonl', ?string $code = null, bool $published = false): array
    {
        $this->tag('keystone', 'export');

        $runId = $this->context()->run->id;

        $pages = $this->action(ExportProductPages::class, $query, $runId, $published)->run();

        return $this->action(
            JoinProductExport::class,
            $runId,
            (int) $pages['parts'],
            (int) $pages['count'],
            $format,
            $code ?? 'export-'.strtolower($runId),
        )->run();
    }
}
