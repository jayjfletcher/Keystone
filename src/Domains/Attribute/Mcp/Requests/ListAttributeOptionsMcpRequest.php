<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Mcp\Requests;

use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Keystone\Domains\Attribute\Actions\ListAttributeOptionsAction;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeOptionModel;
use RefactorCircus\Keystone\Domains\Attribute\Resources\AttributeOptionResource;

final class ListAttributeOptionsMcpRequest extends AttributeRequest
{
    protected function authorize(): bool
    {
        return $this->allows('view', $this->catalogAttribute()) && $this->allows('viewAny', AttributeOptionModel::class);
    }

    protected function rules(): array
    {
        return ListAttributeOptionsAction::rules() + [
            'attribute' => ['required', 'string', 'max:100'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        unset($validated['attribute']);

        $options = app(ListAttributeOptionsAction::class)->execute($this->catalogAttribute(), $validated);

        return $this->structuredCollection(
            AttributeOptionResource::collection($options)->resolve(),
            ['next_cursor' => $options->nextCursor()?->encode()],
        );
    }
}
