<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Association\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Keystone\Domains\Association\Actions\CreateAssociationTypeAction;
use RefactorCircus\Keystone\Domains\Association\Models\AssociationTypeModel;
use RefactorCircus\Keystone\Domains\Association\Resources\AssociationTypeResource;

final class CreateAssociationTypeMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return parent::authorize() && $this->allows('create', AssociationTypeModel::class);
    }

    protected function rules(): array
    {
        return CreateAssociationTypeAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $associationType = app(CreateAssociationTypeAction::class)->execute($validated);

        return Response::structured((new AssociationTypeResource($associationType))->resolve());
    }
}
