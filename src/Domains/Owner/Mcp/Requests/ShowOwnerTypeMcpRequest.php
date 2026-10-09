<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Owner\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Showroom\Domains\Owner\Actions\ShowOwnerTypeAction;
use RefactorCircus\Showroom\Domains\Owner\Resources\OwnerTypeResource;

final class ShowOwnerTypeMcpRequest extends OwnerTypeRequest
{
    protected function authorize(): bool
    {
        return $this->allows('view', $this->ownerType());
    }

    protected function rules(): array
    {
        return ShowOwnerTypeAction::rules() + [
            'owner_type' => ['required', 'string', 'max:191'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $ownerType = app(ShowOwnerTypeAction::class)->execute($this->ownerType());

        return Response::structured((new OwnerTypeResource($ownerType))->resolve());
    }
}
