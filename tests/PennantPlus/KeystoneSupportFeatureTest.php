<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use JayI\Atrium\Domains\Navigation\Data\NavItem;
use JayI\Atrium\Domains\Navigation\Services\NavigationRegistry;
use JayI\Keystone\Atrium\Features\KeystoneSupportFeature;
use JayI\Keystone\Atrium\KeystonePlugin;
use Laravel\Pennant\Feature;
use Workbench\App\Models\User;

// Pennant's array store keeps values in its cache: Feature::flushCache()
// would wipe them, so these tests never call it.

beforeEach(function (): void {
    // Atrium denies access outside local until a gate is defined.
    app()->detectEnvironment(fn (): string => 'local');
});

/**
 * @return array<int, string>
 */
function catalogNavigationFor(?Authenticatable $user = null): array
{
    $request = Request::create('/atrium');
    $request->setUserResolver(fn (): ?Authenticatable => $user);

    return array_map(fn (NavItem $item): string => $item->label, app(NavigationRegistry::class)->items($request));
}

function catalogUser(): User
{
    return User::forceCreate(['name' => 'Ann', 'email' => fake()->unique()->safeEmail(), 'password' => 'x']);
}

/**
 * Off until its global value is set, to show the class can be overridden.
 */
class OffKeystoneSupportFeature extends KeystoneSupportFeature
{
    protected function default(): bool
    {
        return false;
    }
}

it('gates keystone on the bundled feature by default', function (): void {
    expect(app(KeystonePlugin::class)->features())->toBe([KeystoneSupportFeature::class]);
});

it('shows keystone until the feature is turned off globally', function (): void {
    $user = catalogUser();

    expect(catalogNavigationFor($user))->toContain('Products');

    $this->actingAs($user)->get(route('atrium.keystone.products.index'))->assertOk();

    Feature::for(null)->deactivate(KeystoneSupportFeature::class);

    expect(catalogNavigationFor($user))->not->toContain('Products');

    $this->actingAs($user)->get(route('atrium.keystone.products.index'))->assertNotFound();
    $this->actingAs($user)->get(route('atrium.keystone.attributes.index'))->assertNotFound();
});

it('only counts the global value, leaving per-user access to policies', function (): void {
    $user = catalogUser();

    Feature::for($user)->deactivate(KeystoneSupportFeature::class);

    expect(catalogNavigationFor($user))->toContain('Products');

    $this->actingAs($user)->get(route('atrium.keystone.products.index'))->assertOk();
});

it('uses a subclass named in the config instead', function (): void {
    config()->set('keystone.atrium.features', [OffKeystoneSupportFeature::class]);

    expect(catalogNavigationFor(catalogUser()))->not->toContain('Products');

    Feature::for(null)->activate(OffKeystoneSupportFeature::class);

    expect(catalogNavigationFor(catalogUser()))->toContain('Products');
});

it('keeps the stored name it had before it moved', function (): void {
    // Values stored before the class moved from JayI\Keystone\Features.
    Feature::for(null)->deactivate('JayI\\Keystone\\Features\\KeystoneSupportFeature');

    expect(Feature::for(null)->active(KeystoneSupportFeature::class))->toBeFalse()
        ->and(catalogNavigationFor(catalogUser()))->not->toContain('Products');

    Feature::define(OffKeystoneSupportFeature::class);

    expect(Feature::defined())->toContain('JayI\\Keystone\\Features\\KeystoneSupportFeature', OffKeystoneSupportFeature::class);
});
