<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Laravel\Pennant\Feature;
use RefactorCircus\Atrium\Domains\Navigation\Data\NavItem;
use RefactorCircus\Atrium\Domains\Navigation\Services\NavigationRegistry;
use RefactorCircus\Showroom\Atrium\Features\ShowroomSupportFeature;
use RefactorCircus\Showroom\Atrium\ShowroomPlugin;
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
class OffShowroomSupportFeature extends ShowroomSupportFeature
{
    protected function default(): bool
    {
        return false;
    }
}

it('gates showroom on the bundled feature by default', function (): void {
    expect(app(ShowroomPlugin::class)->features())->toBe([ShowroomSupportFeature::class]);
});

it('shows showroom until the feature is turned off globally', function (): void {
    $user = catalogUser();

    expect(catalogNavigationFor($user))->toContain('Products');

    $this->actingAs($user)->get(route('atrium.showroom.products.index'))->assertOk();

    Feature::for(null)->deactivate(ShowroomSupportFeature::class);

    expect(catalogNavigationFor($user))->not->toContain('Products');

    $this->actingAs($user)->get(route('atrium.showroom.products.index'))->assertNotFound();
    $this->actingAs($user)->get(route('atrium.showroom.attributes.index'))->assertNotFound();
});

it('only counts the global value, leaving per-user access to policies', function (): void {
    $user = catalogUser();

    Feature::for($user)->deactivate(ShowroomSupportFeature::class);

    expect(catalogNavigationFor($user))->toContain('Products');

    $this->actingAs($user)->get(route('atrium.showroom.products.index'))->assertOk();
});

it('uses a subclass named in the config instead', function (): void {
    config()->set('showroom.atrium.features', [OffShowroomSupportFeature::class]);

    expect(catalogNavigationFor(catalogUser()))->not->toContain('Products');

    Feature::for(null)->activate(OffShowroomSupportFeature::class);

    expect(catalogNavigationFor(catalogUser()))->toContain('Products');
});
