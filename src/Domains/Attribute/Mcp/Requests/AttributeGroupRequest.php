<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Attribute\Mcp\Requests;

use RefactorCircus\Keystone\Mcp\Requests\Request;
use RefactorCircus\Showroom\Domains\Attribute\Models\AttributeGroupModel;

abstract class AttributeGroupRequest extends Request
{
    protected function group(): AttributeGroupModel
    {
        return AttributeGroupModel::query()->where('code', $this->get('group'))->firstOrFail();
    }
}
