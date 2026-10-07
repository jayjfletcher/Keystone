<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Attribute\Models\AttributeGroupModel;

abstract class AttributeGroupRequest extends Request
{
    protected function group(): AttributeGroupModel
    {
        return AttributeGroupModel::query()->where('code', $this->get('group'))->firstOrFail();
    }
}
