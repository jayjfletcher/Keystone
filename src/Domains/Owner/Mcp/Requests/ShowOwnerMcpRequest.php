<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Owner\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Keystone\Domains\Owner\Actions\ShowOwnerAction;
use RefactorCircus\Keystone\Domains\Owner\Resources\OwnerResource;

final class ShowOwnerMcpRequest extends OwnerRequest
{
    protected function authorize(): bool
    {
        return $this->allows('view', $this->owner());
    }

    protected function rules(): array
    {
        return ShowOwnerAction::rules() + [
            'owner' => ['required', 'string', 'max:191'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $owner = app(ShowOwnerAction::class)->execute($this->owner());

        return Response::structured((new OwnerResource($owner))->resolve());
    }
}
