<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Family\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Showroom\Domains\Family\Actions\UpdateFamilyAction;
use RefactorCircus\Showroom\Domains\Family\Resources\FamilyResource;

final class UpdateFamilyMcpRequest extends FamilyRequest
{
    protected function authorize(): bool
    {
        return $this->allows('update', $this->family());
    }

    protected function rules(): array
    {
        return UpdateFamilyAction::rules() + [
            'family' => ['required', 'string', 'max:100'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        unset($validated['family']);

        $family = app(UpdateFamilyAction::class)->execute($this->family(), $validated);

        return Response::structured((new FamilyResource($family))->resolve());
    }
}
