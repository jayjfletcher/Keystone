<?php

declare(strict_types=1);

arch()->preset()->php();

arch()->preset()->security();

arch('it will not use dd(), ddd(), env(), or exit()')
    ->expect(['dd', 'ddd', 'env', 'exit'])
    ->each->not->toBeUsed();

arch('the package source declares strict types')
    ->expect('RefactorCircus\Showroom')
    ->toUseStrictTypes();

/**
 * One namespace per domain that has the given subdirectory.
 *
 * @return array<int, string>
 */
function domainNamespaces(string $directory): array
{
    return array_map(
        fn (string $path): string => 'RefactorCircus\\Showroom\\Domains\\'.basename(dirname($path)).'\\'.$directory,
        (array) glob(dirname(__DIR__).'/src/Domains/*/'.$directory, GLOB_ONLYDIR),
    );
}

arch('models are final')
    ->expect(domainNamespaces('Models'))
    ->classes()
    ->toBeFinal();

arch('domain models are named for their entity and end in Model')
    ->expect(domainNamespaces('Models'))
    ->toHaveSuffix('Model');

arch('actions are final')
    ->expect(domainNamespaces('Actions'))
    ->classes()
    ->toBeFinal();

// Parity is the default, with declared exceptions. An Action reachable over
// HTTP but not MCP is a test failure unless it is listed in MCP_EXCEPTIONS
// with a reason.
arch('every use case is reachable from both the HTTP API and MCP')
    ->expect(fn (): array => parityGaps())
    ->toBeEmpty();
