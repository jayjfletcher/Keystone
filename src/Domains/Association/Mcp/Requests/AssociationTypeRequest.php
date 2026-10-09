<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Association\Mcp\Requests;

use RefactorCircus\Keystone\Mcp\Requests\Request;
use RefactorCircus\Showroom\Domains\Association\Models\AssociationTypeModel;

abstract class AssociationTypeRequest extends Request
{
    protected function associationType(): AssociationTypeModel
    {
        return AssociationTypeModel::query()->where('code', $this->get('association_type'))->firstOrFail();
    }
}
