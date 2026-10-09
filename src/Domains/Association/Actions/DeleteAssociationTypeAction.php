<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Association\Actions;

use RefactorCircus\Showroom\Domains\Association\Events\AssociationTypeDeletedActionEvent;
use RefactorCircus\Showroom\Domains\Association\Events\AssociationTypeDeletingActionEvent;
use RefactorCircus\Showroom\Domains\Association\Models\AssociationTypeModel;
use RefactorCircus\Showroom\Exceptions\ModelInUseException;

final class DeleteAssociationTypeAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(AssociationTypeModel $associationType): AssociationTypeModel
    {
        AssociationTypeDeletingActionEvent::dispatch($associationType);

        $result = $this->perform($associationType);

        AssociationTypeDeletedActionEvent::dispatch($result);

        return $result;
    }

    private function perform(AssociationTypeModel $associationType): AssociationTypeModel
    {
        $count = $associationType->associations()->count();

        if ($count > 0) {
            throw ModelInUseException::associationTypeInUse($associationType->code, $count);
        }

        $associationType->delete();

        return $associationType;
    }
}
