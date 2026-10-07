<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Association\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Association\Actions\ListAssociationTypesAction;
use JayI\Keystone\Domains\Association\Models\AssociationTypeModel;
use JayI\Keystone\Domains\Association\Resources\AssociationTypeResource;
use Laravel\Mcp\ResponseFactory;

final class ListAssociationTypesMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', AssociationTypeModel::class);
    }

    protected function rules(): array
    {
        return ListAssociationTypesAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $associationTypes = app(ListAssociationTypesAction::class)->execute($validated);

        return $this->structuredCollection(
            AssociationTypeResource::collection($associationTypes)->resolve(),
            ['next_cursor' => $associationTypes->nextCursor()?->encode()],
        );
    }
}
