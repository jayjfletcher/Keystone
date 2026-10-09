<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Owner\Http\Requests;

use RefactorCircus\Keystone\Http\Requests\Request;
use RefactorCircus\Showroom\Domains\Owner\Models\OwnerModel;

abstract class OwnerRequest extends Request
{
    protected function owner(): OwnerModel
    {
        $owner = $this->route('owner');

        if (! $owner instanceof OwnerModel) {
            abort(404);
        }

        return $owner;
    }
}
