<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Asset\Actions;

use JayI\Keystone\Domains\Asset\Events\AssetDeletedActionEvent;
use JayI\Keystone\Domains\Asset\Events\AssetDeletingActionEvent;
use JayI\Keystone\Domains\Asset\Models\AssetModel;
use JayI\Keystone\Domains\Asset\Services\AssetStorage;

final class DeleteAssetAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function __construct(private readonly AssetStorage $storage) {}

    public function execute(AssetModel $asset): AssetModel
    {
        AssetDeletingActionEvent::dispatch($asset);

        $result = $this->perform($asset);

        AssetDeletedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * Links go with the asset; the foreign key cascades. The file goes too,
     * unless `keystone.media.delete_files` is off.
     */
    private function perform(AssetModel $asset): AssetModel
    {
        $asset->delete();

        $this->storage->delete($asset);

        return $asset;
    }
}
