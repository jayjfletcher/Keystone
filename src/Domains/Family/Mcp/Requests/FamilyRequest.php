<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Family\Models\FamilyModel;

abstract class FamilyRequest extends Request
{
    protected function family(): FamilyModel
    {
        return FamilyModel::query()->where('code', $this->get('family'))->firstOrFail();
    }
}
