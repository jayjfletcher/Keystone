<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Actions;

use JayI\Keystone\Domains\Family\Events\FamilyShowingActionEvent;
use JayI\Keystone\Domains\Family\Events\FamilyShownActionEvent;
use JayI\Keystone\Domains\Family\Models\FamilyModel;

final class ShowFamilyAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(FamilyModel $family): FamilyModel
    {
        FamilyShowingActionEvent::dispatch($family);

        $result = $this->perform($family);

        FamilyShownActionEvent::dispatch($result);

        return $result;
    }

    private function perform(FamilyModel $family): FamilyModel
    {
        return $family->load(['labelAttribute', 'familyAttributes.group']);
    }
}
