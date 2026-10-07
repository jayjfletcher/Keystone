<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Mcp\Requests;

use JayI\Keystone\Domains\Family\Actions\DeleteFamilyVariantAction;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class DeleteFamilyVariantMcpRequest extends FamilyVariantRequest
{
    protected function authorize(): bool
    {
        return $this->allows('delete', $this->familyVariant());
    }

    protected function rules(): array
    {
        return DeleteFamilyVariantAction::rules() + [
            'family_variant' => ['required', 'string', 'max:191'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $familyVariant = app(DeleteFamilyVariantAction::class)->execute($this->familyVariant());

        return Response::structured(['deleted' => true, 'code' => $familyVariant->code]);
    }
}
