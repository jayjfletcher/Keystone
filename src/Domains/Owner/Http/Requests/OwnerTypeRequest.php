<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Owner\Http\Requests;

use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Showroom\Domains\Owner\Models\OwnerTypeModel;

abstract class OwnerTypeRequest extends Request
{
    protected function ownerType(): OwnerTypeModel
    {
        $ownerType = $this->route('ownerType');

        if (! $ownerType instanceof OwnerTypeModel) {
            abort(404);
        }

        return $ownerType;
    }
}
