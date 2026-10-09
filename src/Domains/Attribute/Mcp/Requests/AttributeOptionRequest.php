<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Mcp\Requests;

use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeOptionModel;

abstract class AttributeOptionRequest extends AttributeRequest
{
    protected function option(): AttributeOptionModel
    {
        return $this->catalogAttribute()->options()->where('code', $this->get('option'))->firstOrFail();
    }
}
