<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Owner\Http\Requests;

use JayI\Foundation\Http\Requests\Request;
use JayI\Keystone\Domains\Owner\Models\OwnerModel;

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
