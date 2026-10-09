<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Attribute\Http\Requests;

use RefactorCircus\Keystone\Http\Requests\Request;
use RefactorCircus\Showroom\Domains\Attribute\Models\AttributeModel;

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
