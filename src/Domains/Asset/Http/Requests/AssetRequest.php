<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Asset\Http\Requests;

use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Keystone\Domains\Asset\Models\AssetModel;

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
