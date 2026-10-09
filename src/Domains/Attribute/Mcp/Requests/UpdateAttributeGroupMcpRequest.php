<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Attribute\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Showroom\Domains\Attribute\Actions\UpdateAttributeGroupAction;
use RefactorCircus\Showroom\Domains\Attribute\Resources\AttributeGroupResource;

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
