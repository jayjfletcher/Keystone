<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Family\Mcp\Requests;

use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Showroom\Domains\Family\Actions\ListFamilyVariantsAction;
use RefactorCircus\Showroom\Domains\Family\Models\FamilyVariantModel;
use RefactorCircus\Showroom\Domains\Family\Resources\FamilyVariantResource;

final class ListFamilyVariantsMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', FamilyVariantModel::class);
    }

    protected function rules(): array
    {
        return ListFamilyVariantsAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $familyVariants = app(ListFamilyVariantsAction::class)->execute($validated);

        return $this->structuredCollection(
            FamilyVariantResource::collection($familyVariants)->resolve(),
            ['next_cursor' => $familyVariants->nextCursor()?->encode()],
        );
    }
}
