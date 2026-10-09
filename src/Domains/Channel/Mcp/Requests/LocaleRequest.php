<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Channel\Mcp\Requests;

use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Showroom\Domains\Channel\Models\LocaleModel;

abstract class LocaleRequest extends Request
{
    protected function locale(): LocaleModel
    {
        return LocaleModel::query()->where('code', $this->get('locale'))->firstOrFail();
    }
}
