<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Channel\Http\Requests;

use RefactorCircus\Keystone\Http\Requests\Request;
use RefactorCircus\Showroom\Domains\Channel\Models\LocaleModel;

abstract class LocaleRequest extends Request
{
    protected function locale(): LocaleModel
    {
        $locale = $this->route('locale');

        if (! $locale instanceof LocaleModel) {
            abort(404);
        }

        return $locale;
    }
}
