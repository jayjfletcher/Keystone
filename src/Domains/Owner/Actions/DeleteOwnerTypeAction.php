<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Owner\Actions;

use JayI\Keystone\Domains\Owner\Events\OwnerTypeDeletedActionEvent;
use JayI\Keystone\Domains\Owner\Events\OwnerTypeDeletingActionEvent;
use JayI\Keystone\Domains\Owner\Models\OwnerTypeModel;
use JayI\Keystone\Exceptions\ModelInUseException;

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
