<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Transfer\Exceptions;

use RefactorCircus\Showroom\Exceptions\ShowroomException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Imports, exports and feeds run as Impex flows, and Impex is not installed
 * or is switched off.
 */
final class ImpexMissingException extends ShowroomException
{
    public static function make(): self
    {
        return new self('Imports, exports and feeds need refactor-circus/impex. Run: composer require refactor-circus/impex — and keep showroom.impex.enabled on.');
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
