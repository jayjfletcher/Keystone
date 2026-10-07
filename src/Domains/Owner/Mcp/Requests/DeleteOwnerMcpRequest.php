<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Owner\Mcp\Requests;

use JayI\Keystone\Domains\Owner\Actions\DeleteOwnerAction;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class DeleteOwnerMcpRequest extends OwnerRequest
{
    protected function authorize(): bool
    {
        return $this->allows('delete', $this->owner());
    }

    protected function rules(): array
    {
        return DeleteOwnerAction::rules() + [
            'owner' => ['required', 'string', 'max:191'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $owner = app(DeleteOwnerAction::class)->execute($this->owner());

        return Response::structured(['deleted' => true, 'code' => $owner->code]);
    }
}
