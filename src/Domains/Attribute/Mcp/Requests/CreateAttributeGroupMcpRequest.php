<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Attribute\Actions\CreateAttributeGroupAction;
use JayI\Keystone\Domains\Attribute\Models\AttributeGroupModel;
use JayI\Keystone\Domains\Attribute\Resources\AttributeGroupResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class CreateAttributeGroupMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return parent::authorize() && $this->allows('create', AttributeGroupModel::class);
    }

    protected function rules(): array
    {
        return CreateAttributeGroupAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $group = app(CreateAttributeGroupAction::class)->execute($validated);

        return Response::structured((new AttributeGroupResource($group))->resolve());
    }
}
