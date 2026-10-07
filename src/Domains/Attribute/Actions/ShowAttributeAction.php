<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Actions;

use JayI\Keystone\Domains\Attribute\Events\AttributeShowingActionEvent;
use JayI\Keystone\Domains\Attribute\Events\AttributeShownActionEvent;
use JayI\Keystone\Domains\Attribute\Models\AttributeModel;

final class ShowAttributeAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(AttributeModel $attribute): AttributeModel
    {
        AttributeShowingActionEvent::dispatch($attribute);

        $result = $this->perform($attribute);

        AttributeShownActionEvent::dispatch($result);

        return $result;
    }

    private function perform(AttributeModel $attribute): AttributeModel
    {
        $attribute->load('group');

        if ($attribute->type->hasOptions()) {
            $attribute->load('options');
        }

        return $attribute;
    }
}
