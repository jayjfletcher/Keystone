<?php

declare(strict_types=1);

use RefactorCircus\Atrium\Testing\AtriumStyles;

/**
 * Keystone ships no stylesheet: its screens use Atrium's components and the
 * utilities Atrium safelists, so a class Atrium did not compile does nothing.
 */
it('uses only atrium styles', function (): void {
    $views = dirname(__DIR__, 3).'/resources/views';

    expect(AtriumStyles::missingClasses($views))->toBe([])
        ->and(AtriumStyles::inlineStyles($views))->toBe([]);
});
