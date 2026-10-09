<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Family\Http\Requests;

use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Keystone\Domains\Family\Models\FamilyVariantModel;

abstract class FamilyVariantRequest extends Request
{
    protected function familyVariant(): FamilyVariantModel
    {
        $familyVariant = $this->route('familyVariant');

        if (! $familyVariant instanceof FamilyVariantModel) {
            abort(404);
        }

        return $familyVariant;
    }
}
