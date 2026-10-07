<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Attribute\Actions\ListAttributesAction;
use JayI\Keystone\Domains\Attribute\Models\AttributeModel;
use JayI\Keystone\Domains\Attribute\Resources\AttributeResource;
use Laravel\Mcp\ResponseFactory;

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
