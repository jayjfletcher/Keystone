<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Http\Requests;

use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeOptionModel;

abstract class AttributeOptionRequest extends AttributeRequest
{
    protected function option(): AttributeOptionModel
    {
        $option = $this->route('option');

        if (! $option instanceof AttributeOptionModel) {
            abort(404);
        }

        return $option;
    }
}
