<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Owner\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Showroom\Domains\Owner\Actions\UpdateOwnerAction;
use RefactorCircus\Showroom\Domains\Owner\Resources\OwnerResource;

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
