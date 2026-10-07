<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Owner\Mcp\Requests;

use JayI\Keystone\Domains\Owner\Actions\UpdateOwnerAction;
use JayI\Keystone\Domains\Owner\Resources\OwnerResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class UpdateOwnerMcpRequest extends OwnerRequest
{
    protected function authorize(): bool
    {
        return $this->allows('update', $this->owner());
    }

    protected function rules(): array
    {
        return UpdateOwnerAction::rules() + [
            'owner' => ['required', 'string', 'max:191'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        unset($validated['owner']);

        $owner = app(UpdateOwnerAction::class)->execute($this->owner(), $validated);

        return Response::structured((new OwnerResource($owner))->resolve());
    }
}
