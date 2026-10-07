<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Mcp\Requests;

use JayI\Keystone\Domains\Attribute\Actions\UpdateAttributeGroupAction;
use JayI\Keystone\Domains\Attribute\Resources\AttributeGroupResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class UpdateAttributeGroupMcpRequest extends AttributeGroupRequest
{
    protected function authorize(): bool
    {
        return $this->allows('update', $this->group());
    }

    protected function rules(): array
    {
        return UpdateAttributeGroupAction::rules() + [
            'group' => ['required', 'string', 'max:100'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        unset($validated['group']);

        $group = app(UpdateAttributeGroupAction::class)->execute($this->group(), $validated);

        return Response::structured((new AttributeGroupResource($group))->resolve());
    }
}
