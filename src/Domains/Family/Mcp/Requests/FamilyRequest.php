<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Family\Mcp\Requests;

use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Keystone\Domains\Family\Models\FamilyModel;

abstract class FamilyRequest extends Request
{
    protected function family(): FamilyModel
    {
        return FamilyModel::query()->where('code', $this->get('family'))->firstOrFail();
    }
}
