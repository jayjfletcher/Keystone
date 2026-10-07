<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Attribute\Actions\CreateAttributeAction;
use JayI\Keystone\Domains\Attribute\Models\AttributeModel;
use JayI\Keystone\Domains\Attribute\Resources\AttributeResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class CreateAttributeMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return parent::authorize() && $this->allows('create', AttributeModel::class);
    }

    protected function rules(): array
    {
        return CreateAttributeAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $attribute = app(CreateAttributeAction::class)->execute($validated);

        return Response::structured((new AttributeResource($attribute))->resolve());
    }
}
