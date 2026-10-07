<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Owner\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Owner\Actions\CreateOwnerAction;
use JayI\Keystone\Domains\Owner\Models\OwnerModel;
use JayI\Keystone\Domains\Owner\Resources\OwnerResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

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
