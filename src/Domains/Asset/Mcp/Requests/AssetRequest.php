<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Asset\Mcp\Requests;

use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Showroom\Domains\Asset\Models\AssetModel;

abstract class AssetRequest extends Request
{
    protected function asset(): AssetModel
    {
        return AssetModel::query()->where('code', $this->get('asset'))->firstOrFail();
    }
}
