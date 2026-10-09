<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Attribute\Exceptions;

use RefactorCircus\Showroom\Domains\Attribute\Models\AttributeModel;
use RefactorCircus\Showroom\Exceptions\ShowroomException;

final class AttributeHasNoOptionsException extends ShowroomException
{
    public static function for(AttributeModel $attribute): self
    {
        return new self(sprintf(
            'Attribute "%s" is of type "%s", which takes no options. Only select and multiselect attributes have options.',
            $attribute->code,
            $attribute->type->value,
        ));
    }
}
