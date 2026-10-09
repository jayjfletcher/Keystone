<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Family\Http\Requests;

use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Showroom\Domains\Family\Models\FamilyModel;

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
