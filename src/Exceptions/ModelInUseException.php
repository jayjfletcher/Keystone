<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Exceptions;

/**
 * A catalog record other records still depend on.
 */
final class ModelInUseException extends KeystoneException
{
    public static function familyHasProducts(string $family, int $products, int $models): self
    {
        return new self(sprintf(
            'Family "%s" still has %d product(s) and %d product model(s). Delete or move them first.',
            $family,
            $products,
            $models,
        ));
    }

    public static function familyVariantHasModels(string $variant, int $models): self
    {
        return new self(sprintf(
            'Family variant "%s" still has %d product model(s). Delete them first.',
            $variant,
            $models,
        ));
    }

    /**
     * @param  array<int, string>  $channels
     */
    public static function localeInChannels(string $locale, array $channels): self
    {
        return new self(sprintf(
            'Locale "%s" is used by channel %s. Remove it from them first.',
            $locale,
            implode(', ', array_map(fn (string $code): string => '"'.$code.'"', $channels)),
        ));
    }

    /**
     * @param  array<int, string>  $channels
     */
    public static function categoryTreeOfChannels(string $category, array $channels): self
    {
        return new self(sprintf(
            'Category "%s" is the category tree of channel %s. Choose another tree for them first.',
            $category,
            implode(', ', array_map(fn (string $code): string => '"'.$code.'"', $channels)),
        ));
    }

    public static function categoryHasChildren(string $category, int $children): self
    {
        return new self(sprintf('Category "%s" still has %d child categor%s. Move or delete them first.', $category, $children, $children === 1 ? 'y' : 'ies'));
    }

    public static function associationTypeInUse(string $type, int $associations): self
    {
        return new self(sprintf('Association type "%s" is used by %d association(s). Remove them first.', $type, $associations));
    }

    public static function ownerTypeHasOwners(string $type, int $owners): self
    {
        return new self(sprintf('Owner type "%s" still has %d owner(s). Delete them first.', $type, $owners));
    }

    public static function ownerHasDependents(string $owner, int $children, int $products): self
    {
        return new self(sprintf(
            'Owner "%s" still has %d child owner(s) and %d product(s) or product model(s). Move or delete them first.',
            $owner,
            $children,
            $products,
        ));
    }

    /**
     * @param  array<int, string>  $variants
     */
    public static function attributeInFamilyVariants(string $attribute, array $variants): self
    {
        return new self(sprintf(
            'Attribute "%s" is placed in family variant %s. Remove it from them first.',
            $attribute,
            implode(', ', array_map(fn (string $code): string => '"'.$code.'"', $variants)),
        ));
    }
}
