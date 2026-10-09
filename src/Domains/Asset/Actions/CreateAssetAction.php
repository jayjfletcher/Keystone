<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Asset\Actions;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RefactorCircus\Showroom\Domains\Asset\Concerns\StoresAssetFiles;
use RefactorCircus\Showroom\Domains\Asset\Events\AssetCreatedActionEvent;
use RefactorCircus\Showroom\Domains\Asset\Events\AssetCreatingActionEvent;
use RefactorCircus\Showroom\Domains\Asset\Models\AssetModel;
use RefactorCircus\Showroom\Domains\Asset\Services\AssetStorage;
use Throwable;

final class CreateAssetAction
{
    use StoresAssetFiles;

    /**
     * Exactly one of `file`, `path` or `url`. The code is generated from the
     * file name when omitted.
     *
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'code' => ['sometimes', 'nullable', 'string', 'max:191', 'regex:/^[A-Za-z0-9][A-Za-z0-9_.-]*$/', 'unique:showroom_assets,code'],
            'labels' => ['sometimes', 'nullable', 'array'],
            'labels.*' => ['nullable', 'string', 'max:255'],
        ] + self::sourceRules(required: true);
    }

    public function __construct(private readonly AssetStorage $storage) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function execute(array $data): AssetModel
    {
        AssetCreatingActionEvent::dispatch($data);

        $result = $this->perform($data);

        AssetCreatedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(array $data): AssetModel
    {
        $stored = $this->storeSource($this->storage, $data);

        if ($stored === null) {
            throw ValidationException::withMessages(['file' => 'Send a file, a path on the asset disk, or a URL.']);
        }

        try {
            return AssetModel::query()->create([
                'code' => is_string($data['code'] ?? null) ? $data['code'] : $this->codeFor($stored->filename),
                'labels' => $data['labels'] ?? [],
                ...$stored->toAttributes(),
            ]);
        } catch (Throwable $e) {
            // A record that failed to save must not leave its file behind.
            if (! is_string($data['path'] ?? null)) {
                $this->storage->discard($stored);
            }

            throw $e;
        }
    }

    private function codeFor(string $filename): string
    {
        $base = Str::slug(pathinfo($filename, PATHINFO_FILENAME)) ?: 'asset';

        do {
            $code = $base.'-'.Str::lower(Str::random(6));
        } while (AssetModel::query()->where('code', $code)->exists());

        return $code;
    }
}
