<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Owner\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Owner\Models\OwnerTypeModel;

abstract class OwnerTypeRequest extends Request
{
    protected function ownerType(): OwnerTypeModel
    {
        return OwnerTypeModel::query()->where('code', $this->get('owner_type'))->firstOrFail();
    }
}
