<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Family\Mcp\Requests;

use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Keystone\Domains\Family\Models\FamilyVariantModel;

abstract class FamilyVariantRequest extends Request
{
    protected function familyVariant(): FamilyVariantModel
    {
        return FamilyVariantModel::query()->where('code', $this->get('family_variant'))->firstOrFail();
    }
}
