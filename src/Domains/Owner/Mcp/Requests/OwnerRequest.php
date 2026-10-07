<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Owner\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Owner\Models\OwnerModel;

abstract class OwnerRequest extends Request
{
    protected function owner(): OwnerModel
    {
        return OwnerModel::query()->where('code', $this->get('owner'))->firstOrFail();
    }
}
