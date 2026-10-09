<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Impex\Sources;

use Illuminate\Support\Facades\Storage;
use RefactorCircus\Impex\Domains\Batch\Contracts\BatchSource;
use RefactorCircus\Impex\Domains\Batch\Data\BatchChunk;
use RefactorCircus\Impex\Domains\Batch\Data\BatchChunkItem;
use RefactorCircus\Showroom\Domains\Asset\Models\AssetModel;
use RefactorCircus\Showroom\Impex\ProductRows;
use RuntimeException;

/**
 * Streams product records out of a `.csv` or `.jsonl` asset, a page at a
 * time. The cursor is a byte offset and each row's offset is its key: the
 * file does not change under a run, so a redelivered page maps to the same
 * rows.
 */
final class FileProductSource implements BatchSource
{
    public function __construct(
        private readonly string $asset,
        private readonly string $format,
        private readonly string $mode,
    ) {}

    public function chunk(?string $cursor, int $size): BatchChunk
    {
        $asset = AssetModel::query()->where('code', $this->asset)->firstOrFail();
        $stream = Storage::disk($asset->disk)->readStream($asset->path);

        if (! is_resource($stream)) {
            throw new RuntimeException(sprintf('The file of asset "%s" cannot be read.', $asset->code));
        }

        try {
            $header = $this->format === 'csv' ? $this->header($stream) : null;
            $this->skipTo($stream, $cursor === null ? null : (int) $cursor);

            $items = [];

            while (count($items) < $size) {
                $offset = (int) ftell($stream);
                $record = $this->next($stream, $header);

                if ($record === false) {
                    return BatchChunk::last($items);
                }

                if ($record === null) {
                    continue;
                }

                $items[] = new BatchChunkItem((string) $offset, ['mode' => $this->mode, 'record' => $record]);
            }

            return BatchChunk::of($items, (string) ftell($stream));
        } finally {
            fclose($stream);
        }
    }

    /**
     * @param  resource  $stream
     * @return array<int, string>
     */
    private function header(mixed $stream): array
    {
        $header = fgetcsv($stream, escape: '\\');

        if ($header === false) {
            return [];
        }

        // A UTF-8 byte order mark would stick to the first column's name.
        $header[0] = ltrim((string) $header[0], "\u{FEFF}");

        return array_map(fn (?string $column): string => trim((string) $column), $header);
    }

    /**
     * Move to the cursor. Object stores often hand out streams that cannot
     * seek, so those are read forward instead.
     *
     * @param  resource  $stream
     */
    private function skipTo(mixed $stream, ?int $offset): void
    {
        if ($offset === null || $offset <= (int) ftell($stream)) {
            return;
        }

        if (stream_get_meta_data($stream)['seekable']) {
            fseek($stream, $offset);

            return;
        }

        while ((int) ftell($stream) < $offset && ! feof($stream)) {
            fread($stream, max(1, min(1048576, $offset - (int) ftell($stream))));
        }
    }

    /**
     * The next record, null for a blank line, false at the end.
     *
     * @param  resource  $stream
     * @param  array<int, string>|null  $header
     * @return array<string, mixed>|false|null
     */
    private function next(mixed $stream, ?array $header): array|false|null
    {
        if ($header !== null) {
            $cells = fgetcsv($stream, escape: '\\');

            if ($cells === false) {
                return false;
            }

            if ($cells === [null]) {
                return null;
            }

            $row = [];

            foreach ($header as $index => $column) {
                $row[$column] = $cells[$index] ?? null;
            }

            return app(ProductRows::class)->fromCsv($row);
        }

        $line = fgets($stream);

        if ($line === false) {
            return false;
        }

        $line = trim($line);

        if ($line === '') {
            return null;
        }

        $record = json_decode($line, true);

        // A malformed line still becomes an item, so it fails visibly.
        return is_array($record) ? $record : ['_invalid' => $line];
    }
}
