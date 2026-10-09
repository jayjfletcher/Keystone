<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Association\Http\Requests;

use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Keystone\Domains\Association\Models\AssociationTypeModel;

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
