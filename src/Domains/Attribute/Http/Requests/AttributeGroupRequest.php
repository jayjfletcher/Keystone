<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Http\Requests;

use JayI\Foundation\Http\Requests\Request;
use JayI\Keystone\Domains\Attribute\Models\AttributeGroupModel;

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
