<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Asset\Mcp\Requests;

use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Keystone\Domains\Asset\Models\AssetModel;

abstract class AssetRequest extends Request
{
    protected function asset(): AssetModel
    {
        return AssetModel::query()->where('code', $this->get('asset'))->firstOrFail();
    }
}
