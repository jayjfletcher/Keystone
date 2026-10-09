<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Asset\Services;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Filesystem\Factory as Filesystems;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as Http;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RefactorCircus\Keystone\Domains\Asset\Data\StoredFile;
use RefactorCircus\Keystone\Domains\Asset\Models\AssetModel;

/**
 * Reads and writes asset files on the configured disk.
 *
 * Every write streams, so a large file never sits whole in memory — which
 * matters on Lambda as much as the absence of a lasting local disk does.
 */
final class AssetStorage
{
    public function __construct(
        private readonly Config $config,
        private readonly Filesystems $filesystems,
        private readonly Http $http,
    ) {}

    /**
     * The disk new assets are written to.
     */
    public function diskName(): string
    {
        $disk = $this->config->get('keystone.media.disk');

        return is_string($disk) && $disk !== '' ? $disk : $this->config->string('filesystems.default', 'local');
    }

    public function storeUpload(UploadedFile $file): StoredFile
    {
        $filename = $this->filename($file->getClientOriginalName(), $file->getClientOriginalExtension() ?: $file->extension());
        $path = $this->newPath($filename);

        $stream = fopen($file->getRealPath() ?: '', 'rb');

        if ($stream === false) {
            throw ValidationException::withMessages(['file' => 'The uploaded file could not be read.']);
        }

        try {
            $this->disk()->writeStream($path, $stream);
        } finally {
            fclose($stream);
        }

        return $this->describe($this->diskName(), $path, $filename, $file->getMimeType());
    }

    /**
     * Fetch a file over HTTP and stream it to the disk.
     *
     * @throws ValidationException
     */
    public function storeFromUrl(string $url): StoredFile
    {
        try {
            $response = $this->http
                ->timeout($this->config->integer('keystone.media.download_timeout', 30))
                ->withOptions(['stream' => true])
                ->get($url);
        } catch (ConnectionException) {
            throw ValidationException::withMessages(['url' => 'The URL could not be reached.']);
        }

        if (! $response->successful()) {
            throw ValidationException::withMessages(['url' => sprintf('The URL answered with status %d.', $response->status())]);
        }

        $name = basename((string) parse_url($url, PHP_URL_PATH)) ?: 'download';
        $filename = $this->filename($name, pathinfo($name, PATHINFO_EXTENSION));
        $path = $this->newPath($filename);

        $this->disk()->put($path, $response->toPsrResponse()->getBody());

        $type = $response->header('Content-Type');
        $mime = $type !== '' ? trim(explode(';', $type)[0]) : null;

        return $this->describe($this->diskName(), $path, $filename, $mime);
    }

    /**
     * Take on a file already on the disk — uploaded straight to S3 with a
     * presigned URL, say — without copying it.
     *
     * @throws ValidationException
     */
    public function adopt(string $path): StoredFile
    {
        $path = ltrim($path, '/');

        if (! $this->disk()->exists($path)) {
            throw ValidationException::withMessages(['path' => sprintf('No file exists at "%s" on the "%s" disk.', $path, $this->diskName())]);
        }

        return $this->describe($this->diskName(), $path, basename($path), null);
    }

    public function url(AssetModel $asset): string
    {
        $minutes = $this->config->get('keystone.media.temporary_urls');
        $disk = $this->filesystems->disk($asset->disk);

        if (is_numeric($minutes) && method_exists($disk, 'temporaryUrl')) {
            return $disk->temporaryUrl($asset->path, now()->addMinutes((int) $minutes));
        }

        return method_exists($disk, 'url') ? $disk->url($asset->path) : $asset->path;
    }

    public function delete(AssetModel $asset): void
    {
        if ($this->config->get('keystone.media.delete_files', true) === false) {
            return;
        }

        $this->filesystems->disk($asset->disk)->delete($asset->path);
    }

    /**
     * Remove a file this storage wrote, when a later check refuses it.
     */
    public function discard(StoredFile $file): void
    {
        $this->filesystems->disk($file->disk)->delete($file->path);
    }

    private function describe(string $disk, string $path, string $filename, ?string $mime): StoredFile
    {
        $filesystem = $this->filesystems->disk($disk);
        $checksum = null;
        $stream = $filesystem->readStream($path);

        if (is_resource($stream)) {
            $context = hash_init('sha256');
            hash_update_stream($context, $stream);
            fclose($stream);
            $checksum = hash_final($context);
        }

        $detected = $filesystem->mimeType($path);

        return new StoredFile(
            disk: $disk,
            path: $path,
            filename: $filename,
            mimeType: $mime ?? ($detected !== false ? $detected : null),
            size: $filesystem->size($path),
            checksum: $checksum,
        );
    }

    /**
     * A safe file name: the slugged base name and a lowercase extension.
     */
    private function filename(string $original, ?string $extension): string
    {
        $base = Str::slug(pathinfo($original, PATHINFO_FILENAME)) ?: 'file';
        $extension = strtolower((string) $extension);

        return $extension !== '' ? $base.'.'.$extension : $base;
    }

    /**
     * Each file gets a folder of its own, so names never collide.
     */
    private function newPath(string $filename): string
    {
        return trim($this->config->string('keystone.media.path', 'keystone/assets'), '/').'/'.Str::ulid()->toBase32().'/'.$filename;
    }

    private function disk(): Filesystem
    {
        return $this->filesystems->disk($this->diskName());
    }
}
