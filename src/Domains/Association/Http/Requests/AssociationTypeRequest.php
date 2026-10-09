<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Association\Http\Requests;

use RefactorCircus\Keystone\Http\Requests\Request;
use RefactorCircus\Showroom\Domains\Association\Models\AssociationTypeModel;

abstract class AssociationTypeRequest extends Request
{
    protected function associationType(): AssociationTypeModel
    {
        $associationType = $this->route('associationType');

        if (! $associationType instanceof AssociationTypeModel) {
            abort(404);
        }

        return $associationType;
    }
}
