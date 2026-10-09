<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Exceptions;

use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeModel;
use RefactorCircus\Keystone\Exceptions\KeystoneException;

final class AttributeLabelsFamiliesException extends KeystoneException
{
    /**
     * @param  array<int, string>  $families
     */
    public static function for(AttributeModel $attribute, array $families): self
    {
        return new self(sprintf(
            'Attribute "%s" is the label attribute of family %s. Choose another label attribute first.',
            $attribute->code,
            implode(', ', array_map(fn (string $code): string => '"'.$code.'"', $families)),
        ));
    }
}
