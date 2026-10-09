<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Attribute\Exceptions;

use RefactorCircus\Showroom\Domains\Attribute\Models\AttributeGroupModel;
use RefactorCircus\Showroom\Exceptions\ShowroomException;

final class AttributeGroupNotEmptyException extends ShowroomException
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
