<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Keystone\Domains\Attribute\Actions\DeleteAttributeOptionAction;

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
