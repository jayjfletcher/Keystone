<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Owner\Actions;

use RefactorCircus\Showroom\Domains\Owner\Events\OwnerShowingActionEvent;
use RefactorCircus\Showroom\Domains\Owner\Events\OwnerShownActionEvent;
use RefactorCircus\Showroom\Domains\Owner\Models\OwnerModel;

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
