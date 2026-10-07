<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Http\Requests;

use JayI\Foundation\Http\Requests\Request;
use JayI\Keystone\Domains\Family\Models\FamilyVariantModel;

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
