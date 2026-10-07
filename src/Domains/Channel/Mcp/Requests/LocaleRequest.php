<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Channel\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Channel\Models\LocaleModel;

abstract class LocaleRequest extends Request
{
    protected function locale(): LocaleModel
    {
        return LocaleModel::query()->where('code', $this->get('locale'))->firstOrFail();
    }
}
