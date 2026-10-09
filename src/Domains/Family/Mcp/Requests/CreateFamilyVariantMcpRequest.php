<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Family\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Keystone\Domains\Family\Actions\CreateFamilyVariantAction;
use RefactorCircus\Keystone\Domains\Family\Models\FamilyVariantModel;
use RefactorCircus\Keystone\Domains\Family\Resources\FamilyVariantResource;

final class CreateFamilyVariantMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return parent::authorize() && $this->allows('create', FamilyVariantModel::class);
    }

    protected function rules(): array
    {
        return CreateFamilyVariantAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $familyVariant = app(CreateFamilyVariantAction::class)->execute($validated);

        return Response::structured((new FamilyVariantResource($familyVariant))->resolve());
    }
}
