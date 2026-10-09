<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Attribute\Http\Requests;

use RefactorCircus\Keystone\Http\Requests\Request;
use RefactorCircus\Showroom\Domains\Attribute\Models\AttributeGroupModel;

abstract class AttributeGroupRequest extends Request
{
    protected function group(): AttributeGroupModel
    {
        $group = $this->route('group');

        if (! $group instanceof AttributeGroupModel) {
            abort(404);
        }

        return $group;
    }
}
