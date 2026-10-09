<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Asset\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use RefactorCircus\Showroom\Domains\Asset\Data\StoredFile;
use RefactorCircus\Showroom\Domains\Asset\Services\AssetStorage;

/**
 * Taking a file in from an upload, a path on the disk or a URL, shared by
 * creating and replacing an asset.
 */
trait StoresAssetFiles
{
    /**
     * @return array<string, mixed>
     */
    private static function sourceRules(bool $required): array
    {
        $file = ['file', 'max:'.config('showroom.media.max_kilobytes', 51200)];

        /** @var array<int, string>|null $types */
        $types = config('showroom.media.mime_types');

        if (is_array($types) && $types !== []) {
            $file[] = 'mimetypes:'.implode(',', $types);
        }

        $one = fn (string $others): array => $required ? ['required_without_all:'.$others, 'prohibits:'.$others] : ['prohibits:'.$others];

        return [
            // Exactly one source: a multipart upload, a path already on the
            // asset disk, or a URL to fetch.
            'file' => [...$one('path,url'), ...$file],
            'path' => [...$one('file,url'), 'string', 'max:1024'],
            'url' => [...$one('file,path'), 'string', 'url:http,https', 'max:2048'],
        ];
    }

    /**
     * Store the source the data names, or null when it names none.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    private function storeSource(AssetStorage $storage, array $data): ?StoredFile
    {
        $stored = match (true) {
            ($data['file'] ?? null) instanceof UploadedFile => $storage->storeUpload($data['file']),
            is_string($data['path'] ?? null) => $storage->adopt($data['path']),
            is_string($data['url'] ?? null) => $storage->storeFromUrl($data['url']),
            default => null,
        };

        if ($stored !== null && ! is_string($data['path'] ?? null)) {
            $this->checkLimits($storage, $stored, is_string($data['url'] ?? null) ? 'url' : 'file');
        } elseif ($stored !== null) {
            $this->checkLimits(null, $stored, 'path');
        }

        return $stored;
    }

    /**
     * Uploads are checked by their rules; a fetched or adopted file only once
     * its size and type are known.
     *
     * @throws ValidationException
     */
    private function checkLimits(?AssetStorage $storage, StoredFile $stored, string $key): void
    {
        $max = (int) config('showroom.media.max_kilobytes', 51200) * 1024;

        /** @var array<int, string>|null $types */
        $types = config('showroom.media.mime_types');

        $error = match (true) {
            $stored->size > $max => sprintf('The file is %d KB; the limit is %d KB.', intdiv($stored->size, 1024), intdiv($max, 1024)),
            is_array($types) && $types !== [] && ! in_array($stored->mimeType, $types, true) => sprintf('Files of type "%s" are not accepted.', $stored->mimeType ?? 'unknown'),
            default => null,
        };

        if ($error === null) {
            return;
        }

        // Only discard what Showroom wrote; an adopted file is the caller's.
        $storage?->discard($stored);

        throw ValidationException::withMessages([$key => $error]);
    }
}
