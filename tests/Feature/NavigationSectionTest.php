<?php

declare(strict_types=1);

use JayI\Atrium\Support\Icons;
use JayI\Keystone\Atrium\KeystonePlugin;

it('gives its sidebar section its own icon', function (): void {
    [$group] = app(KeystonePlugin::class)->navigationGroups();

    expect($group->icon)->toBe(Icons::svg('cube'))
        ->and($group->sort)->toBe(20);
});

it('collects its pages in that section', function (): void {
    $plugin = app(KeystonePlugin::class);
    [$group] = $plugin->navigationGroups();

    $labels = array_values(array_unique(array_map(fn ($item): ?string => $item->group, $plugin->navigation())));

    expect($labels)->toBe([$group->name]);
});
