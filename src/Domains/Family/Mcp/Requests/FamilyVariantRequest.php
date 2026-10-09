<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Family\Mcp\Requests;

use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Showroom\Domains\Family\Models\FamilyVariantModel;

abstract class FamilyVariantRequest extends Request
{
    protected function familyVariant(): FamilyVariantModel
    {
        return FamilyVariantModel::query()->where('code', $this->get('family_variant'))->firstOrFail();
    }
}
