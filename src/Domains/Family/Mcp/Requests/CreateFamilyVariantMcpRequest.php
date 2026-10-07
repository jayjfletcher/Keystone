<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Family\Actions\CreateFamilyVariantAction;
use JayI\Keystone\Domains\Family\Models\FamilyVariantModel;
use JayI\Keystone\Domains\Family\Resources\FamilyVariantResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

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
