<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Http\Requests;

use JayI\Foundation\Http\Requests\Request;
use JayI\Keystone\Domains\Attribute\Models\AttributeModel;

abstract class AttributeRequest extends Request
{
    protected function catalogAttribute(): AttributeModel
    {
        $attribute = $this->route('attribute');

        if (! $attribute instanceof AttributeModel) {
            abort(404);
        }

        return $attribute;
    }
}
