<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Attribute\Http\Requests;

use RefactorCircus\Showroom\Domains\Attribute\Models\AttributeOptionModel;

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
