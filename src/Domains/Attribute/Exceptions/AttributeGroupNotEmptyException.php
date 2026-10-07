<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Exceptions;

use JayI\Keystone\Domains\Attribute\Models\AttributeGroupModel;
use JayI\Keystone\Exceptions\KeystoneException;

final class AttributeGroupNotEmptyException extends KeystoneException
{
    public static function for(AttributeGroupModel $group, int $attributes): self
    {
        return new self(sprintf(
            'Attribute group "%s" still holds %d attribute(s). Move them to another group first.',
            $group->code,
            $attributes,
        ));
    }
}
