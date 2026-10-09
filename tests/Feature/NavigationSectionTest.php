<?php

declare(strict_types=1);

use RefactorCircus\Atrium\Support\Icons;
use RefactorCircus\Showroom\Atrium\ShowroomPlugin;

it('gives its sidebar section its own icon', function (): void {
    [$group] = app(ShowroomPlugin::class)->navigationGroups();

    expect($group->icon)->toBe(Icons::svg('cube'))
        ->and($group->sort)->toBe(20);
});

it('collects its pages in that section', function (): void {
    $plugin = app(ShowroomPlugin::class);
    [$group] = $plugin->navigationGroups();

    $labels = array_values(array_unique(array_map(fn ($item): ?string => $item->group, $plugin->navigation())));

    expect($labels)->toBe([$group->name]);
});
