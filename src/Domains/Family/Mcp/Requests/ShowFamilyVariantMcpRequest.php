<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Mcp\Requests;

use JayI\Keystone\Domains\Family\Actions\ShowFamilyVariantAction;
use JayI\Keystone\Domains\Family\Resources\FamilyVariantResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class ShowFamilyVariantMcpRequest extends FamilyVariantRequest
{
    protected function authorize(): bool
    {
        return $this->allows('view', $this->familyVariant());
    }

    protected function rules(): array
    {
        return ShowFamilyVariantAction::rules() + [
            'family_variant' => ['required', 'string', 'max:191'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $familyVariant = app(ShowFamilyVariantAction::class)->execute($this->familyVariant());

        return Response::structured((new FamilyVariantResource($familyVariant))->resolve());
    }
}
