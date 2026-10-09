<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Attribute\Mcp\Requests;

use RefactorCircus\Showroom\Domains\Attribute\Models\AttributeOptionModel;

abstract class AttributeOptionRequest extends AttributeRequest
{
    protected function option(): AttributeOptionModel
    {
        return $this->catalogAttribute()->options()->where('code', $this->get('option'))->firstOrFail();
    }
}
