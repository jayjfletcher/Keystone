<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Mcp\Requests;

use JayI\Keystone\Domains\Attribute\Actions\DeleteAttributeOptionAction;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class DeleteAttributeOptionMcpRequest extends AttributeOptionRequest
{
    protected function authorize(): bool
    {
        return $this->allows('delete', $this->option());
    }

    protected function rules(): array
    {
        return DeleteAttributeOptionAction::rules() + [
            'attribute' => ['required', 'string', 'max:100'],
            'option' => ['required', 'string', 'max:100'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $option = app(DeleteAttributeOptionAction::class)->execute($this->option());

        return Response::structured(['deleted' => true, 'code' => $option->code]);
    }
}
