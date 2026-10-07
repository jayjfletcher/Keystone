<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Family\Actions\CreateFamilyAction;
use JayI\Keystone\Domains\Family\Models\FamilyModel;
use JayI\Keystone\Domains\Family\Resources\FamilyResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class CreateFamilyMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return parent::authorize() && $this->allows('create', FamilyModel::class);
    }

    protected function rules(): array
    {
        return CreateFamilyAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $family = app(CreateFamilyAction::class)->execute($validated);

        return Response::structured((new FamilyResource($family))->resolve());
    }
}
