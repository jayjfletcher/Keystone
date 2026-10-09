<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Mcp\Requests;

use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Keystone\Domains\Attribute\Actions\ListAttributesAction;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeModel;
use RefactorCircus\Keystone\Domains\Attribute\Resources\AttributeResource;

final class ListAttributesMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', AttributeModel::class);
    }

    protected function rules(): array
    {
        return ListAttributesAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $attributes = app(ListAttributesAction::class)->execute($validated);

        return $this->structuredCollection(
            AttributeResource::collection($attributes)->resolve(),
            ['next_cursor' => $attributes->nextCursor()?->encode()],
        );
    }
}
