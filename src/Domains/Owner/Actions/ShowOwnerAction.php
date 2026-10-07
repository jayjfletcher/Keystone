<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Owner\Actions;

use JayI\Keystone\Domains\Owner\Events\OwnerShowingActionEvent;
use JayI\Keystone\Domains\Owner\Events\OwnerShownActionEvent;
use JayI\Keystone\Domains\Owner\Models\OwnerModel;

final class ShowOwnerAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(OwnerModel $owner): OwnerModel
    {
        OwnerShowingActionEvent::dispatch($owner);

        $result = $this->perform($owner);

        OwnerShownActionEvent::dispatch($result);

        return $result;
    }

    private function perform(OwnerModel $owner): OwnerModel
    {
        $owner->load(['type', 'parent', 'children.type', 'assets'])->loadCount(['products', 'productModels']);

        return $owner->setRelation('chain', $owner->chain()->load('type'));
    }
}
