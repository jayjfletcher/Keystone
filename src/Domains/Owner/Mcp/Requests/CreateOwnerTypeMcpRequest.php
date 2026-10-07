<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Owner\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Owner\Actions\CreateOwnerTypeAction;
use JayI\Keystone\Domains\Owner\Models\OwnerTypeModel;
use JayI\Keystone\Domains\Owner\Resources\OwnerTypeResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class CreateOwnerTypeMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return parent::authorize() && $this->allows('create', OwnerTypeModel::class);
    }

    protected function rules(): array
    {
        return CreateOwnerTypeAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $ownerType = app(CreateOwnerTypeAction::class)->execute($validated);

        return Response::structured((new OwnerTypeResource($ownerType))->resolve());
    }
}
