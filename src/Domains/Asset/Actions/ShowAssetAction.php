<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Asset\Actions;

use JayI\Keystone\Domains\Asset\Events\AssetShowingActionEvent;
use JayI\Keystone\Domains\Asset\Events\AssetShownActionEvent;
use JayI\Keystone\Domains\Asset\Models\AssetModel;

final class ShowAssetAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(AssetModel $asset): AssetModel
    {
        AssetShowingActionEvent::dispatch($asset);

        $result = $this->perform($asset);

        AssetShownActionEvent::dispatch($result);

        return $result;
    }

    private function perform(AssetModel $asset): AssetModel
    {
        return $asset->load(['products', 'productModels', 'owners']);
    }
}
