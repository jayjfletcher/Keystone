<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Association\Actions;

use JayI\Keystone\Domains\Association\Events\AssociationTypeShowingActionEvent;
use JayI\Keystone\Domains\Association\Events\AssociationTypeShownActionEvent;
use JayI\Keystone\Domains\Association\Models\AssociationTypeModel;

final class ShowAssociationTypeAction
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
        AssociationTypeShowingActionEvent::dispatch($associationType);

        $result = $this->perform($associationType);

        AssociationTypeShownActionEvent::dispatch($result);

        return $result;
    }

    private function perform(AssociationTypeModel $associationType): AssociationTypeModel
    {
        return $associationType->loadCount('associations');
    }
}
