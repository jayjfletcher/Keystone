<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Asset\Http\Requests;

use JayI\Foundation\Http\Requests\Request;
use JayI\Keystone\Domains\Asset\Models\AssetModel;

abstract class AssetRequest extends Request
{
    protected function asset(): AssetModel
    {
        $asset = $this->route('asset');

        if (! $asset instanceof AssetModel) {
            abort(404);
        }

        return $asset;
    }
}
