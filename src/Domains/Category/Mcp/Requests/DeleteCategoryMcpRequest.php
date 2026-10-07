<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Category\Mcp\Requests;

use JayI\Keystone\Domains\Category\Actions\DeleteCategoryAction;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class DeleteCategoryMcpRequest extends CategoryRequest
{
    protected function authorize(): bool
    {
        return $this->allows('delete', $this->category());
    }

    protected function rules(): array
    {
        return DeleteCategoryAction::rules() + [
            'category' => ['required', 'string', 'max:100'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $category = app(DeleteCategoryAction::class)->execute($this->category());

        return Response::structured(['deleted' => true, 'code' => $category->code]);
    }
}
