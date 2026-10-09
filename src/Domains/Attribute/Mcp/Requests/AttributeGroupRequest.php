<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Mcp\Requests;

use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeGroupModel;

abstract class AttributeGroupRequest extends Request
{
    protected function group(): AttributeGroupModel
    {
        return AttributeGroupModel::query()->where('code', $this->get('group'))->firstOrFail();
    }
}
