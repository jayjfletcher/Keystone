<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Mcp\Requests;

use JayI\Keystone\Domains\Attribute\Actions\CreateAttributeOptionAction;
use JayI\Keystone\Domains\Attribute\Models\AttributeOptionModel;
use JayI\Keystone\Domains\Attribute\Resources\AttributeOptionResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class CreateAttributeOptionMcpRequest extends AttributeRequest
{
    protected function authorize(): bool
    {
        return $this->allows('update', $this->catalogAttribute()) && $this->allows('create', AttributeOptionModel::class);
    }

    protected function rules(): array
    {
        return CreateAttributeOptionAction::rules() + [
            'attribute' => ['required', 'string', 'max:100'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        unset($validated['attribute']);

        $option = app(CreateAttributeOptionAction::class)->execute($this->catalogAttribute(), $validated);

        return Response::structured((new AttributeOptionResource($option))->resolve());
    }
}
