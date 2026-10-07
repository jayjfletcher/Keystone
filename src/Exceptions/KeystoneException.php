<?php

declare(strict_types=1);

namespace JayI\Keystone\Exceptions;

use JayI\Foundation\Exceptions\PackageException;

/**
 * A catalog rule the caller broke.
 *
 * The message is written for whoever made the call — a person or an agent —
 * so the HTTP API and MCP tools surface it as it is. It answers 409 Conflict,
 * not a validation error: the request was well-formed, the catalog's current
 * state simply makes it impossible.
 */
abstract class KeystoneException extends PackageException
{
    protected int $status = 409;
}
