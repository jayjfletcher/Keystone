<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Owner\Mcp\Requests;

use JayI\Keystone\Domains\Owner\Actions\DeleteOwnerTypeAction;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class DeleteOwnerTypeMcpRequest extends OwnerTypeRequest
{
    protected function authorize(): bool
    {
        return $this->allows('delete', $this->ownerType());
    }

    protected function rules(): array
    {
        return DeleteOwnerTypeAction::rules() + [
            'owner_type' => ['required', 'string', 'max:191'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $ownerType = app(DeleteOwnerTypeAction::class)->execute($this->ownerType());

        return Response::structured(['deleted' => true, 'code' => $ownerType->code]);
    }
}
