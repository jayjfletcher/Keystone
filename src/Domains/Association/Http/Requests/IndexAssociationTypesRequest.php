<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Association\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Foundation\Http\Requests\Request;
use JayI\Keystone\Domains\Association\Actions\ListAssociationTypesAction;
use JayI\Keystone\Domains\Association\Models\AssociationTypeModel;
use JayI\Keystone\Domains\Association\Resources\AssociationTypeResource;

final class IndexAssociationTypesRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', AssociationTypeModel::class);
    }

    public function rules(): array
    {
        return ListAssociationTypesAction::rules();
    }

    public function persist(): JsonResponse
    {
        $associationTypes = app(ListAssociationTypesAction::class)->execute($this->validated());

        return AssociationTypeResource::collection($associationTypes)->response();
    }
}
