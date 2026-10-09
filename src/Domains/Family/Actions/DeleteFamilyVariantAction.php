<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Family\Actions;

use RefactorCircus\Showroom\Domains\Family\Events\FamilyVariantDeletedActionEvent;
use RefactorCircus\Showroom\Domains\Family\Events\FamilyVariantDeletingActionEvent;
use RefactorCircus\Showroom\Domains\Family\Models\FamilyVariantModel;
use RefactorCircus\Showroom\Exceptions\ModelInUseException;

final class DeleteFamilyVariantAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(FamilyVariantModel $familyVariant): FamilyVariantModel
    {
        FamilyVariantDeletingActionEvent::dispatch($familyVariant);

        $result = $this->perform($familyVariant);

        FamilyVariantDeletedActionEvent::dispatch($result);

        return $result;
    }

    private function perform(FamilyVariantModel $familyVariant): FamilyVariantModel
    {
        $models = $familyVariant->productModels()->count();

        if ($models > 0) {
            throw ModelInUseException::familyVariantHasModels($familyVariant->code, $models);
        }

        $familyVariant->delete();

        return $familyVariant;
    }
}
