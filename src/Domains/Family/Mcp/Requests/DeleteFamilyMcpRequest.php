<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Mcp\Requests;

use JayI\Keystone\Domains\Family\Actions\DeleteFamilyAction;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class DeleteFamilyMcpRequest extends FamilyRequest
{
    protected function authorize(): bool
    {
        return $this->allows('delete', $this->family());
    }

    protected function rules(): array
    {
        return DeleteFamilyAction::rules() + [
            'family' => ['required', 'string', 'max:100'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $family = app(DeleteFamilyAction::class)->execute($this->family());

        return Response::structured(['deleted' => true, 'code' => $family->code]);
    }
}
