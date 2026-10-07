<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Actions;

use Illuminate\Support\Facades\DB;
use JayI\Keystone\Domains\Attribute\Events\AttributeGroupDeletedActionEvent;
use JayI\Keystone\Domains\Attribute\Events\AttributeGroupDeletingActionEvent;
use JayI\Keystone\Domains\Attribute\Exceptions\AttributeGroupNotEmptyException;
use JayI\Keystone\Domains\Attribute\Models\AttributeGroupModel;

final class DeleteAttributeGroupAction
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
        AttributeGroupDeletingActionEvent::dispatch($group);

        $result = $this->perform($group);

        AttributeGroupDeletedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * A group with attributes is refused rather than emptied: silently
     * ungrouping attributes would reorganise the catalog behind the caller.
     */
    private function perform(AttributeGroupModel $group): AttributeGroupModel
    {
        return DB::transaction(function () use ($group): AttributeGroupModel {
            $count = $group->groupedAttributes()->count();

            if ($count > 0) {
                throw AttributeGroupNotEmptyException::for($group, $count);
            }

            $group->delete();

            return $group;
        });
    }
}
