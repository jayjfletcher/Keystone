<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Asset\Actions;

use Illuminate\Validation\ValidationException;
use JayI\Keystone\Domains\Asset\Concerns\StoresAssetFiles;
use JayI\Keystone\Domains\Asset\Events\AssetUpdatedActionEvent;
use JayI\Keystone\Domains\Asset\Events\AssetUpdatingActionEvent;
use JayI\Keystone\Domains\Asset\Models\AssetModel;
use JayI\Keystone\Domains\Asset\Services\AssetStorage;

final class UpdateAssetAction
{
    use StoresAssetFiles;

    /**
     * Sending `file`, `path` or `url` replaces the file; the code and every
     * link stay as they are.
     *
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'code' => ['prohibited'],
            'labels' => ['sometimes', 'nullable', 'array'],
            'labels.*' => ['nullable', 'string', 'max:255'],
        ] + self::sourceRules(required: false);
    }

    public function __construct(private readonly AssetStorage $storage) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function execute(AssetModel $asset, array $data): AssetModel
    {
        AssetUpdatingActionEvent::dispatch($asset, $data);

        $result = $this->perform($asset, $data);

        AssetUpdatedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(AssetModel $asset, array $data): AssetModel
    {
        if (array_key_exists('labels', $data)) {
            $asset->labels = is_array($data['labels']) ? $data['labels'] : [];
        }

        $stored = $this->storeSource($this->storage, $data);

        if ($stored !== null) {
            $previous = $asset->replicate();
            $asset->fill($stored->toAttributes());
            $asset->save();

            if ($previous->path !== $asset->path || $previous->disk !== $asset->disk) {
                $this->storage->delete($previous);
            }

            return $asset;
        }

        $asset->save();

        return $asset;
    }
}
