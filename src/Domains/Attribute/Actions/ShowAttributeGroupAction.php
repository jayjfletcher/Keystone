<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Actions;

use RefactorCircus\Keystone\Domains\Attribute\Events\AttributeGroupShowingActionEvent;
use RefactorCircus\Keystone\Domains\Attribute\Events\AttributeGroupShownActionEvent;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeGroupModel;

final class ShowAttributeGroupAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(AttributeGroupModel $group): AttributeGroupModel
    {
        AttributeGroupShowingActionEvent::dispatch($group);

        $result = $this->perform($group);

        AttributeGroupShownActionEvent::dispatch($result);

        return $result;
    }

    private function perform(AttributeGroupModel $group): AttributeGroupModel
    {
        return $group->load('groupedAttributes')->loadCount('groupedAttributes');
    }
}
