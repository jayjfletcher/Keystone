<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Mcp\Requests;

use JayI\Keystone\Domains\Attribute\Actions\ShowAttributeAction;
use JayI\Keystone\Domains\Attribute\Resources\AttributeResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class ShowAttributeMcpRequest extends AttributeRequest
{
    protected function authorize(): bool
    {
        return $this->allows('view', $this->catalogAttribute());
    }

    protected function rules(): array
    {
        return ShowAttributeAction::rules() + [
            'attribute' => ['required', 'string', 'max:100'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $attribute = app(ShowAttributeAction::class)->execute($this->catalogAttribute());

        return Response::structured((new AttributeResource($attribute))->resolve());
    }
}
