<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use JayI\Keystone\Domains\Asset\AssetServiceProvider;
use JayI\Keystone\Domains\Association\AssociationServiceProvider;
use JayI\Keystone\Domains\Attribute\AttributeServiceProvider;
use JayI\Keystone\Domains\Category\CategoryServiceProvider;
use JayI\Keystone\Domains\Channel\ChannelServiceProvider;
use JayI\Keystone\Domains\DomainServiceProvider;
use JayI\Keystone\Domains\Family\FamilyServiceProvider;
use JayI\Keystone\Domains\Owner\OwnerServiceProvider;
use JayI\Keystone\Domains\Product\ProductServiceProvider;
use JayI\Keystone\Domains\ProductModel\ProductModelServiceProvider;
use JayI\Keystone\Domains\Search\SearchServiceProvider;
use JayI\Keystone\Domains\Transfer\TransferServiceProvider;
use JayI\Keystone\Domains\Workflow\WorkflowServiceProvider;
use JayI\Keystone\Keystone;
use JayI\Keystone\KeystoneServiceProvider;

it('resolves the Keystone singleton', function (): void {
    expect(app(Keystone::class))->toBe(app(Keystone::class));
});

it('merges the package config', function (): void {
    expect(config('keystone.routes.prefix'))->toBe('keystone')
        ->and(config('keystone.mcp.web.enabled'))->toBeFalse()
        ->and(config('keystone.pagination.per_page'))->toBe(25);
});

it('registers the API routes', function (): void {
    expect(Route::has('keystone.attributes.index'))->toBeTrue()
        ->and(route('keystone.attributes.index'))->toEndWith('/keystone/attributes');
});

it('serves its audit history, answering 404 while no audit log is installed', function (): void {
    expect(Route::has('keystone.history.index'))->toBeTrue()
        ->and(route('keystone.history.index'))->toEndWith('/keystone/history');

    $this->getJson(route('keystone.history.index'))
        ->assertNotFound()
        ->assertJsonPath('message', fn (string $message): bool => str_contains($message, 'No audit log is installed'));
});

it('registers every domain service provider', function (string $provider): void {
    expect(app()->getProvider($provider))->not->toBeNull();
})->with([
    DomainServiceProvider::class,
    AssetServiceProvider::class,
    AssociationServiceProvider::class,
    AttributeServiceProvider::class,
    CategoryServiceProvider::class,
    ChannelServiceProvider::class,
    FamilyServiceProvider::class,
    OwnerServiceProvider::class,
    ProductServiceProvider::class,
    ProductModelServiceProvider::class,
    SearchServiceProvider::class,
    TransferServiceProvider::class,
    WorkflowServiceProvider::class,
]);

it('keeps every API route name now each domain loads its own routes', function (): void {
    $names = [
        'attribute-groups.index',
        'attribute-groups.store',
        'attribute-groups.show',
        'attribute-groups.update',
        'attribute-groups.destroy',
        'attributes.index',
        'attributes.store',
        'attributes.show',
        'attributes.update',
        'attributes.destroy',
        'attributes.options.index',
        'attributes.options.store',
        'attributes.options.update',
        'attributes.options.destroy',
        'families.index',
        'families.store',
        'families.show',
        'families.update',
        'families.destroy',
        'family-variants.index',
        'family-variants.store',
        'family-variants.show',
        'family-variants.update',
        'family-variants.destroy',
        'product-models.index',
        'product-models.store',
        'product-models.show',
        'product-models.update',
        'product-models.destroy',
        'owner-types.index',
        'owner-types.store',
        'owner-types.show',
        'owner-types.update',
        'owner-types.destroy',
        'owners.index',
        'owners.store',
        'owners.show',
        'owners.update',
        'owners.destroy',
        'categories.index',
        'categories.store',
        'categories.show',
        'categories.update',
        'categories.destroy',
        'locales.index',
        'locales.store',
        'locales.show',
        'locales.update',
        'locales.destroy',
        'channels.index',
        'channels.store',
        'channels.show',
        'channels.update',
        'channels.destroy',
        'association-types.index',
        'association-types.store',
        'association-types.show',
        'association-types.update',
        'association-types.destroy',
        'imports.store',
        'exports.store',
        'assets.index',
        'assets.store',
        'assets.show',
        'assets.update',
        'assets.destroy',
        'assets.links.store',
        'assets.links.destroy',
        'products.index',
        'products.store',
        'products.show',
        'products.update',
        'products.destroy',
        'products.transitions.store',
        'products.versions.index',
        'products.versions.show',
        'products.revert',
    ];

    foreach ($names as $name) {
        expect(Route::has('keystone.'.$name))->toBeTrue('keystone.'.$name);
    }
});

it('loads the package translations', function (): void {
    expect(__('keystone::keystone.attributes'))->toBe('Attributes');
});

it('publishes every resource group under its own tag', function (string $tag): void {
    expect(ServiceProvider::pathsToPublish(KeystoneServiceProvider::class, $tag))->not->toBeEmpty();
})->with(['keystone', 'keystone-config', 'keystone-views', 'keystone-lang', 'keystone-migrations']);
