<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Family\Mcp\Requests;

use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Showroom\Domains\Family\Models\FamilyModel;

abstract class FamilyRequest extends Request
{
    protected function family(): FamilyModel
    {
        return FamilyModel::query()->where('code', $this->get('family'))->firstOrFail();
    }
}
