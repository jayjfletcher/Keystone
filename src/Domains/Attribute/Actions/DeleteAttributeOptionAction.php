<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Actions;

use RefactorCircus\Keystone\Domains\Attribute\Events\AttributeOptionDeletedActionEvent;
use RefactorCircus\Keystone\Domains\Attribute\Events\AttributeOptionDeletingActionEvent;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeOptionModel;
use RefactorCircus\Keystone\Jobs\PurgeAttributeValues;

final class DeleteAttributeOptionAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(AttributeOptionModel $option): AttributeOptionModel
    {
        AttributeOptionDeletingActionEvent::dispatch($option);

        $result = $this->perform($option);

        AttributeOptionDeletedActionEvent::dispatch($result);

        return $result;
    }

    private function perform(AttributeOptionModel $option): AttributeOptionModel
    {
        $option->delete();

        PurgeAttributeValues::dispatch($option->attribute()->value('code') ?? '', $option->code)->afterCommit();

        return $option;
    }
}
