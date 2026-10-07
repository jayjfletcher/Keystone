<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use JayI\Atrium\Domains\Navigation\Data\NavItem;
use JayI\Atrium\Domains\Plugins\Services\PluginRegistry;
use JayI\Atrium\Domains\Search\Data\SearchResult;
use JayI\Keystone\Atrium\Features\KeystoneSupportFeature;
use JayI\Keystone\Atrium\KeystonePlugin;
use JayI\Keystone\Domains\Attribute\Models\AttributeGroupModel;
use JayI\Keystone\Domains\Attribute\Models\AttributeModel;
use JayI\Keystone\Tests\Fixtures\OrphanSupportFeature;

it('registers itself with atrium', function (): void {
    expect(app(PluginRegistry::class)->has('keystone'))->toBeTrue();
});

it('contributes catalog navigation', function (): void {
    $labels = array_map(
        fn (NavItem $item): string => $item->label,
        app(KeystonePlugin::class)->navigation(),
    );

    expect($labels)->toBe(['Products', 'Product models', 'Categories', 'Assets', 'Owners', 'Families', 'Attributes', 'Channels', 'Import & export', 'Association types', 'Attribute groups', 'Audit log']);
});

it('registers its routes inside the atrium group', function (): void {
    expect(Route::has('atrium.keystone.attributes.index'))->toBeTrue()
        ->and(Route::has('atrium.keystone.attribute-groups.index'))->toBeTrue()
        ->and(route('atrium.keystone.attributes.index'))->toContain('/atrium/keystone/attributes');
});

it('offers a settings panel', function (): void {
    expect(app(KeystonePlugin::class)->settings()?->key)->toBe('keystone');
});

it('finds attributes and groups by code or label', function (): void {
    AttributeModel::factory()->create(['code' => 'color', 'labels' => ['en' => 'Colour']]);
    AttributeGroupModel::factory()->create(['code' => 'colors_and_finishes', 'labels' => ['en' => 'Colors and finishes']]);
    AttributeModel::factory()->create(['code' => 'weight']);

    $source = app(KeystonePlugin::class)->search();

    $titles = array_map(
        fn (SearchResult $result): string => $result->title,
        $source?->results('colo') ?? [],
    );

    expect($titles)->toBe(['Colour', 'Colors and finishes']);
});

it('skips feature classes that are not installed', function (): void {
    config()->set('keystone.atrium.features', [
        'JayI\\Keystone\\Tests\\Fixtures\\NoSuchFeature',
        // Its parent class is missing, as KeystoneSupportFeature's is without
        // jayi/pennantplus, so loading it throws rather than answering false.
        OrphanSupportFeature::class,
        'catalog-enabled',
        KeystoneSupportFeature::class,
    ]);

    expect(app(KeystonePlugin::class)->features())->toBe(['catalog-enabled', KeystoneSupportFeature::class]);
});

it('checks no feature when none are configured', function (): void {
    config()->set('keystone.atrium.features', []);

    expect(app(KeystonePlugin::class)->features())->toBe([]);
});
