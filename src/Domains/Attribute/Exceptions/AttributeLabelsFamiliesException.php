<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Exceptions;

use JayI\Keystone\Domains\Attribute\Models\AttributeModel;
use JayI\Keystone\Exceptions\KeystoneException;

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
