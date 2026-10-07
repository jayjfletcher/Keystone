<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Family\Models\FamilyVariantModel;

abstract class FamilyVariantRequest extends Request
{
    protected function familyVariant(): FamilyVariantModel
    {
        return FamilyVariantModel::query()->where('code', $this->get('family_variant'))->firstOrFail();
    }
}
