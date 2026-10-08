<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Search\Exceptions;

use JayI\Keystone\Exceptions\KeystoneException;
use Symfony\Component\HttpFoundation\Response;

/**
 * The configured search engine cannot run part of a query.
 */
final class UnsupportedSearchException extends KeystoneException
{
    public static function filter(string $engine, string $attribute, string $operator): self
    {
        return new self(sprintf(
            'The %s search engine cannot filter attribute "%s" with "%s".',
            $engine,
            $attribute,
            $operator,
        ));
    }

    public static function missingPackage(string $engine, string $package): self
    {
        return new self(sprintf(
            'The %s search engine needs %s. Run: composer require %s',
            $engine,
            $package,
            $package,
        ));
    }

    public static function removedEngine(string $engine): self
    {
        return new self(sprintf(
            'Keystone no longer ships the %s search engine. Use "scout" with a Scout driver for it, or the class name of your own SearchEngine, in keystone.search.engine.',
            $engine,
        ));
    }

    /**
     * Unprocessable: the query is well-formed, the engine cannot answer it.
     */
    public function render(): Response
    {
        return new Response(
            json_encode(['message' => $this->getMessage()], JSON_THROW_ON_ERROR),
            422,
            ['Content-Type' => 'application/json'],
        );
    }
}
