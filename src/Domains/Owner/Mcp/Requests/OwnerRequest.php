<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Owner\Mcp\Requests;

use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Keystone\Domains\Owner\Models\OwnerModel;

abstract class OwnerRequest extends Request
{
    protected function owner(): OwnerModel
    {
        return OwnerModel::query()->where('code', $this->get('owner'))->firstOrFail();
    }
}
