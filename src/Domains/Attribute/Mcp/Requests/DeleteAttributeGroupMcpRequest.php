<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Mcp\Requests;

use JayI\Keystone\Domains\Attribute\Actions\DeleteAttributeGroupAction;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class DeleteAttributeGroupMcpRequest extends AttributeGroupRequest
{
    protected function authorize(): bool
    {
        return $this->allows('delete', $this->group());
    }

    protected function rules(): array
    {
        return DeleteAttributeGroupAction::rules() + [
            'group' => ['required', 'string', 'max:100'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $group = app(DeleteAttributeGroupAction::class)->execute($this->group());

        return Response::structured(['deleted' => true, 'code' => $group->code]);
    }
}
