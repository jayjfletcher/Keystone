<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Attribute\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Showroom\Domains\Attribute\Actions\UpdateAttributeAction;
use RefactorCircus\Showroom\Domains\Attribute\Resources\AttributeResource;

final class UpdateAttributeMcpRequest extends AttributeRequest
{
    protected function authorize(): bool
    {
        return $this->allows('update', $this->catalogAttribute());
    }

    protected function rules(): array
    {
        return UpdateAttributeAction::rules() + [
            'attribute' => ['required', 'string', 'max:100'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        unset($validated['attribute']);

        $attribute = app(UpdateAttributeAction::class)->execute($this->catalogAttribute(), $validated);

        return Response::structured((new AttributeResource($attribute))->resolve());
    }
}
