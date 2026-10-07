<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Attribute\Models\AttributeModel;

abstract class AttributeRequest extends Request
{
    protected function catalogAttribute(): AttributeModel
    {
        return AttributeModel::query()->where('code', $this->get('attribute'))->firstOrFail();
    }
}
