<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Association\Mcp\Requests;

use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Keystone\Domains\Association\Actions\ListAssociationTypesAction;
use RefactorCircus\Keystone\Domains\Association\Models\AssociationTypeModel;
use RefactorCircus\Keystone\Domains\Association\Resources\AssociationTypeResource;

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
