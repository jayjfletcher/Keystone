<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Mcp\Requests;

use JayI\Keystone\Domains\Attribute\Actions\UpdateAttributeOptionAction;
use JayI\Keystone\Domains\Attribute\Resources\AttributeOptionResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class UpdateAttributeOptionMcpRequest extends AttributeOptionRequest
{
    protected function authorize(): bool
    {
        return $this->allows('update', $this->option());
    }

    protected function rules(): array
    {
        return UpdateAttributeOptionAction::rules() + [
            'attribute' => ['required', 'string', 'max:100'],
            'option' => ['required', 'string', 'max:100'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        unset($validated['attribute'], $validated['option']);

        $option = app(UpdateAttributeOptionAction::class)->execute($this->option(), $validated);

        return Response::structured((new AttributeOptionResource($option))->resolve());
    }
}
