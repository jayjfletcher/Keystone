<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Family\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Keystone\Domains\Family\Actions\UpdateFamilyVariantAction;
use RefactorCircus\Keystone\Domains\Family\Resources\FamilyVariantResource;

final class UpdateFamilyVariantMcpRequest extends FamilyVariantRequest
{
    protected function authorize(): bool
    {
        return $this->allows('update', $this->familyVariant());
    }

    protected function rules(): array
    {
        return UpdateFamilyVariantAction::rules() + [
            'family_variant' => ['required', 'string', 'max:191'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        unset($validated['family_variant']);

        $familyVariant = app(UpdateFamilyVariantAction::class)->execute($this->familyVariant(), $validated);

        return Response::structured((new FamilyVariantResource($familyVariant))->resolve());
    }
}
