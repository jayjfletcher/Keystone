<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Mcp\Requests;

use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Keystone\Domains\Attribute\Actions\ListAttributeGroupsAction;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeGroupModel;
use RefactorCircus\Keystone\Domains\Attribute\Resources\AttributeGroupResource;

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
