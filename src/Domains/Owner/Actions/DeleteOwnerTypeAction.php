<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Owner\Actions;

use RefactorCircus\Keystone\Domains\Owner\Events\OwnerTypeDeletedActionEvent;
use RefactorCircus\Keystone\Domains\Owner\Events\OwnerTypeDeletingActionEvent;
use RefactorCircus\Keystone\Domains\Owner\Models\OwnerTypeModel;
use RefactorCircus\Keystone\Exceptions\ModelInUseException;

final class DeleteOwnerTypeAction
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
        OwnerTypeDeletingActionEvent::dispatch($ownerType);

        $result = $this->perform($ownerType);

        OwnerTypeDeletedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * Parent-type rules naming this type go with it; the foreign keys cascade.
     */
    private function perform(OwnerTypeModel $ownerType): OwnerTypeModel
    {
        $owners = $ownerType->owners()->count();

        if ($owners > 0) {
            throw ModelInUseException::ownerTypeHasOwners($ownerType->code, $owners);
        }

        $ownerType->delete();

        return $ownerType;
    }
}
