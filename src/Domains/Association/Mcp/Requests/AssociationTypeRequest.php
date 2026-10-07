<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Association\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Association\Models\AssociationTypeModel;

abstract class AssociationTypeRequest extends Request
{
    protected function associationType(): AssociationTypeModel
    {
        return AssociationTypeModel::query()->where('code', $this->get('association_type'))->firstOrFail();
    }
}
