<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Association\Mcp\Requests;

use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Keystone\Domains\Association\Models\AssociationTypeModel;

abstract class AssociationTypeRequest extends Request
{
    protected function associationType(): AssociationTypeModel
    {
        return AssociationTypeModel::query()->where('code', $this->get('association_type'))->firstOrFail();
    }
}
