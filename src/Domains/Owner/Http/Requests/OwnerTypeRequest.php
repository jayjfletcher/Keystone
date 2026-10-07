<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Owner\Http\Requests;

use JayI\Foundation\Http\Requests\Request;
use JayI\Keystone\Domains\Owner\Models\OwnerTypeModel;

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
