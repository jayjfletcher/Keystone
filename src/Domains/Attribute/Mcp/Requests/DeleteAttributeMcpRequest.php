<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Mcp\Requests;

use JayI\Keystone\Domains\Attribute\Actions\DeleteAttributeAction;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class DeleteAttributeMcpRequest extends AttributeRequest
{
    protected function authorize(): bool
    {
        return $this->allows('delete', $this->catalogAttribute());
    }

    protected function rules(): array
    {
        return DeleteAttributeAction::rules() + [
            'attribute' => ['required', 'string', 'max:100'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $attribute = app(DeleteAttributeAction::class)->execute($this->catalogAttribute());

        return Response::structured(['deleted' => true, 'code' => $attribute->code]);
    }
}
