<?php

declare(strict_types=1);

use JayI\Atrium\Domains\Navigation\Data\NavItem;
use JayI\Keystone\Atrium\KeystonePlugin;

it('links its own audit log from its sidebar group', function (): void {
    $urls = array_map(fn (NavItem $item): ?string => $item->resolveUrl(), app(KeystonePlugin::class)->navigation());

    expect($urls)->toContain(route('atrium.history.show', 'keystone'));
});
