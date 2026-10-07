<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Attribute\Actions\ListAttributeGroupsAction;
use JayI\Keystone\Domains\Attribute\Models\AttributeGroupModel;
use JayI\Keystone\Domains\Attribute\Resources\AttributeGroupResource;
use Laravel\Mcp\ResponseFactory;

final class ListAttributeGroupsMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', AttributeGroupModel::class);
    }

    protected function rules(): array
    {
        return ListAttributeGroupsAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $groups = app(ListAttributeGroupsAction::class)->execute($validated);

        return $this->structuredCollection(
            AttributeGroupResource::collection($groups)->resolve(),
            ['next_cursor' => $groups->nextCursor()?->encode()],
        );
    }
}
