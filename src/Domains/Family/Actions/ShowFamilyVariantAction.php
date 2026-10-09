<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Family\Actions;

use RefactorCircus\Keystone\Domains\Family\Events\FamilyVariantShowingActionEvent;
use RefactorCircus\Keystone\Domains\Family\Events\FamilyVariantShownActionEvent;
use RefactorCircus\Keystone\Domains\Family\Models\FamilyVariantModel;

final class ShowFamilyVariantAction
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
        FamilyVariantShowingActionEvent::dispatch($familyVariant);

        $result = $this->perform($familyVariant);

        FamilyVariantShownActionEvent::dispatch($result);

        return $result;
    }

    private function perform(FamilyVariantModel $familyVariant): FamilyVariantModel
    {
        return $familyVariant->load(['family.familyAttributes', 'variantAttributes'])->loadCount('productModels');
    }
}
