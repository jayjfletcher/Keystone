<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Mcp\Requests;

use JayI\Keystone\Domains\Attribute\Actions\ShowAttributeGroupAction;
use JayI\Keystone\Domains\Attribute\Resources\AttributeGroupResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

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
