<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Family\Actions\ListFamilyVariantsAction;
use JayI\Keystone\Domains\Family\Models\FamilyVariantModel;
use JayI\Keystone\Domains\Family\Resources\FamilyVariantResource;
use Laravel\Mcp\ResponseFactory;

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
