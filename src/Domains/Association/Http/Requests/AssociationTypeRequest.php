<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Association\Http\Requests;

use JayI\Foundation\Http\Requests\Request;
use JayI\Keystone\Domains\Association\Models\AssociationTypeModel;

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
