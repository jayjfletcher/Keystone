<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Owner\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Keystone\Domains\Owner\Actions\UpdateOwnerTypeAction;
use RefactorCircus\Keystone\Domains\Owner\Resources\OwnerTypeResource;

final class UpdateOwnerTypeMcpRequest extends OwnerTypeRequest
{
    protected function authorize(): bool
    {
        return $this->allows('update', $this->ownerType());
    }

    protected function rules(): array
    {
        return UpdateOwnerTypeAction::rules() + [
            'owner_type' => ['required', 'string', 'max:191'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        unset($validated['owner_type']);

        $ownerType = app(UpdateOwnerTypeAction::class)->execute($this->ownerType(), $validated);

        return Response::structured((new OwnerTypeResource($ownerType))->resolve());
    }
}
