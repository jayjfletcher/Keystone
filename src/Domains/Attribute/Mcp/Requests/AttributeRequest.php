<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Attribute\Mcp\Requests;

use RefactorCircus\Keystone\Mcp\Requests\Request;
use RefactorCircus\Showroom\Domains\Attribute\Models\AttributeModel;

abstract class AttributeRequest extends Request
{
    protected function catalogAttribute(): AttributeModel
    {
        return AttributeModel::query()->where('code', $this->get('attribute'))->firstOrFail();
    }
}
