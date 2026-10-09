<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Owner\Mcp\Requests;

use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Showroom\Domains\Owner\Models\OwnerTypeModel;

abstract class OwnerTypeRequest extends Request
{
    protected function ownerType(): OwnerTypeModel
    {
        return OwnerTypeModel::query()->where('code', $this->get('owner_type'))->firstOrFail();
    }
}
