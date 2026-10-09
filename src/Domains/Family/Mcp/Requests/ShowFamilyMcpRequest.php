<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Family\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Keystone\Domains\Family\Actions\ShowFamilyAction;
use RefactorCircus\Keystone\Domains\Family\Resources\FamilyResource;

final class ShowFamilyMcpRequest extends FamilyRequest
{
    protected function authorize(): bool
    {
        return $this->allows('view', $this->family());
    }

    protected function rules(): array
    {
        return ShowFamilyAction::rules() + [
            'family' => ['required', 'string', 'max:100'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $family = app(ShowFamilyAction::class)->execute($this->family());

        return Response::structured((new FamilyResource($family))->resolve());
    }
}
