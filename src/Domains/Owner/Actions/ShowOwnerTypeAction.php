<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Owner\Actions;

use RefactorCircus\Keystone\Domains\Owner\Events\OwnerTypeShowingActionEvent;
use RefactorCircus\Keystone\Domains\Owner\Events\OwnerTypeShownActionEvent;
use RefactorCircus\Keystone\Domains\Owner\Models\OwnerTypeModel;

final class ShowOwnerTypeAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(OwnerTypeModel $ownerType): OwnerTypeModel
    {
        OwnerTypeShowingActionEvent::dispatch($ownerType);

        $result = $this->perform($ownerType);

        OwnerTypeShownActionEvent::dispatch($result);

        return $result;
    }

    private function perform(OwnerTypeModel $ownerType): OwnerTypeModel
    {
        return $ownerType->load('parentTypes')->loadCount('owners');
    }
}
