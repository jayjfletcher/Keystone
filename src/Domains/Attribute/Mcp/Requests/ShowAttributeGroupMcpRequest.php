<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Keystone\Domains\Attribute\Actions\ShowAttributeGroupAction;
use RefactorCircus\Keystone\Domains\Attribute\Resources\AttributeGroupResource;

final class ShowAttributeGroupMcpRequest extends AttributeGroupRequest
{
    protected function authorize(): bool
    {
        return $this->allows('view', $this->group());
    }

    protected function rules(): array
    {
        return ShowAttributeGroupAction::rules() + [
            'group' => ['required', 'string', 'max:100'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $group = app(ShowAttributeGroupAction::class)->execute($this->group());

        return Response::structured((new AttributeGroupResource($group))->resolve());
    }
}
