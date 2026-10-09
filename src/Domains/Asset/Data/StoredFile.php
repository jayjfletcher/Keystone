<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Asset\Data;

/**
 * A file written to (or found on) the asset disk, with what is known of it.
 */
final readonly class StoredFile
{
    public function __construct(
        public string $disk,
        public string $path,
        public string $filename,
        public ?string $mimeType,
        public int $size,
        public ?string $checksum,
    ) {}

    /**
     * @return array{disk: string, path: string, filename: string, mime_type: string|null, size: int, checksum: string|null}
     */
    public function toAttributes(): array
    {
        return [
            'disk' => $this->disk,
            'path' => $this->path,
            'filename' => $this->filename,
            'mime_type' => $this->mimeType,
            'size' => $this->size,
            'checksum' => $this->checksum,
        ];
    }
}
