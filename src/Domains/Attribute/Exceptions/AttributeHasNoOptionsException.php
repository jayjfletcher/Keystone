<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Exceptions;

use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeModel;
use RefactorCircus\Keystone\Exceptions\KeystoneException;

final class AttributeHasNoOptionsException extends KeystoneException
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
