<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Asset\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Asset\Models\AssetModel;

abstract class AssetRequest extends Request
{
    protected function asset(): AssetModel
    {
        return AssetModel::query()->where('code', $this->get('asset'))->firstOrFail();
    }
}
