<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Owner\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Keystone\Domains\Owner\Actions\CreateOwnerAction;
use RefactorCircus\Keystone\Domains\Owner\Models\OwnerModel;
use RefactorCircus\Keystone\Domains\Owner\Resources\OwnerResource;

final class CreateOwnerMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return parent::authorize() && $this->allows('create', OwnerModel::class);
    }

    protected function rules(): array
    {
        return CreateOwnerAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $owner = app(CreateOwnerAction::class)->execute($validated);

        return Response::structured((new OwnerResource($owner))->resolve());
    }
}
