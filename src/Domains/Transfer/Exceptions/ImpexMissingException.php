<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Transfer\Exceptions;

use JayI\Keystone\Exceptions\KeystoneException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Imports, exports and feeds run as Impex flows, and Impex is not installed
 * or is switched off.
 */
final class ImpexMissingException extends KeystoneException
{
    public static function make(): self
    {
        return new self('Imports, exports and feeds need jayi/impex. Run: composer require jayi/impex — and keep keystone.impex.enabled on.');
    }

    /**
     * Not implemented here: the feature exists, the package that runs it does not.
     */
    public function render(): Response
    {
        return new Response(
            json_encode(['message' => $this->getMessage()], JSON_THROW_ON_ERROR),
            501,
            ['Content-Type' => 'application/json'],
        );
    }
}
