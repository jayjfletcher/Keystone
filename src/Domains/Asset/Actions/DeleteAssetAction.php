<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Asset\Actions;

use RefactorCircus\Showroom\Domains\Asset\Events\AssetDeletedActionEvent;
use RefactorCircus\Showroom\Domains\Asset\Events\AssetDeletingActionEvent;
use RefactorCircus\Showroom\Domains\Asset\Models\AssetModel;
use RefactorCircus\Showroom\Domains\Asset\Services\AssetStorage;

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
     * unless `showroom.media.delete_files` is off.
     */
    private function perform(AssetModel $asset): AssetModel
    {
        $asset->delete();

        $this->storage->delete($asset);

        return $asset;
    }
}
