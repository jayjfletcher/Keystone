<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Association\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Association\Actions\CreateAssociationTypeAction;
use JayI\Keystone\Domains\Association\Models\AssociationTypeModel;
use JayI\Keystone\Domains\Association\Resources\AssociationTypeResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

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
