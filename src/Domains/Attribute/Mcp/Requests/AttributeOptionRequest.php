<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Mcp\Requests;

use JayI\Keystone\Domains\Attribute\Models\AttributeOptionModel;

abstract class AttributeOptionRequest extends AttributeRequest
{
    protected function option(): AttributeOptionModel
    {
        return $this->catalogAttribute()->options()->where('code', $this->get('option'))->firstOrFail();
    }
}
