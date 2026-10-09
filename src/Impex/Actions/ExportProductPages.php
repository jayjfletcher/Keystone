<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Impex\Actions;

use Illuminate\Support\Facades\Storage;
use RefactorCircus\Impex\Domains\Flow\Support\ResumableAction;
use RefactorCircus\Impex\Domains\Run\Data\Resume;
use RefactorCircus\Showroom\Domains\Asset\Services\AssetStorage;
use RefactorCircus\Showroom\Domains\Attribute\Data\ValueFilter;
use RefactorCircus\Showroom\Domains\Product\Actions\ListProductsAction;
use RefactorCircus\Showroom\Impex\ExportRecord;

/**
 * Writes the products a search matches as JSONL part files, a page each.
 *
 * Object stores cannot append, and a Lambda invocation cannot outlive its
 * ceiling, so each page is its own file and the action yields between pages
 * when time runs short; JoinProductExport stitches the parts together.
 */
final class ExportProductPages extends ResumableAction
{
    /**
     * @param  array<string, mixed>  $query  ListProductsAction filters, plus `scope` and `locales`.
     * @return array{parts: int, count: int}|Resume
     */
    public function execute(array $query, string $runId, bool $published = false): array|Resume
    {
        $state = json_decode((string) ($this->cursor() ?? '{"page":1,"count":0}'), true);
        $page = (int) ($state['page'] ?? 1);
        $count = (int) ($state['count'] ?? 0);

        $storage = app(AssetStorage::class);
        $disk = Storage::disk($storage->diskName());
        $filter = ValueFilter::fromArray($query);
        $perPage = (int) config('showroom.impex.export_page_size', 500);

        while (true) {
            $products = app(ListProductsAction::class)->execute(
                ['sort' => 'identifier', 'page' => $page, 'per_page' => $perPage] + $query + ($published ? ['published' => true] : []),
            );

            $lines = [];

            foreach ($products as $product) {
                $record = $published ? ExportRecord::published($product, $filter) : ExportRecord::of($product, $filter);

                if ($record !== null) {
                    $lines[] = json_encode($record, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            }

            if ($lines !== []) {
                $disk->put(self::part($runId, $page), implode("\n", $lines)."\n");
                $count += count($lines);
            }

            if ($products->count() < $perPage || ! $products->hasMorePages()) {
                return ['parts' => $page, 'count' => $count];
            }

            $page++;

            if ($this->shouldYield()) {
                return $this->yieldTo(json_encode(['page' => $page, 'count' => $count], JSON_THROW_ON_ERROR));
            }
        }
    }

    /**
     * Where one page of an export is written.
     */
    public static function part(string $runId, int $page): string
    {
        return trim((string) config('showroom.impex.export_path', 'showroom/exports'), '/').'/'.$runId.'/part-'.str_pad((string) $page, 6, '0', STR_PAD_LEFT).'.jsonl';
    }
}
