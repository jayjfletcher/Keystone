<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Family\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Keystone\Domains\Family\Actions\CreateFamilyAction;
use RefactorCircus\Keystone\Domains\Family\Models\FamilyModel;
use RefactorCircus\Keystone\Domains\Family\Resources\FamilyResource;

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
