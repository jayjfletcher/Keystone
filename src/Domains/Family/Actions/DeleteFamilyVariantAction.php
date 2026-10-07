<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Actions;

use JayI\Keystone\Domains\Family\Events\FamilyVariantDeletedActionEvent;
use JayI\Keystone\Domains\Family\Events\FamilyVariantDeletingActionEvent;
use JayI\Keystone\Domains\Family\Models\FamilyVariantModel;
use JayI\Keystone\Exceptions\ModelInUseException;

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
