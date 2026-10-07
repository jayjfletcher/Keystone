<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Http\Requests;

use JayI\Foundation\Http\Requests\Request;
use JayI\Keystone\Domains\Family\Models\FamilyModel;

abstract class FamilyRequest extends Request
{
    protected function family(): FamilyModel
    {
        $family = $this->route('family');

        if (! $family instanceof FamilyModel) {
            abort(404);
        }

        return $family;
    }
}
