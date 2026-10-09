<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Owner\Mcp\Requests;

use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Showroom\Domains\Owner\Models\OwnerModel;

abstract class OwnerRequest extends Request
{
    protected function owner(): OwnerModel
    {
        return OwnerModel::query()->where('code', $this->get('owner'))->firstOrFail();
    }
}
