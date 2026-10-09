<?php

declare(strict_types=1);

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use RefactorCircus\Keystone\Domains\Owner\Models\OwnerModel;
use RefactorCircus\Keystone\Domains\Owner\Models\OwnerTypeModel;
use RefactorCircus\Keystone\Domains\Product\Models\ProductModel;

beforeEach(function (): void {
    ValidateCsrfToken::except(['*']);

    // Atrium denies access outside local until a gate is defined.
    app()->detectEnvironment(fn (): string => 'local');
});

it('defines owner types and their chain rules', function (): void {
    $this->get(route('atrium.keystone.owner-types.index'))->assertOk()->assertSee(__('keystone::keystone.no_owner_types'));

    $this->post(route('atrium.keystone.owner-types.store'), ['code' => 'vendor', 'any_parent' => '1', 'can_be_root' => '1', 'owns_products' => '1'])
        ->assertRedirect(route('atrium.keystone.owner-types.show', 'vendor'));

    $this->post(route('atrium.keystone.owner-types.store'), [
        'code' => 'series',
        'any_parent' => '0',
        'parent_types' => ['vendor'],
        'can_be_root' => '0',
        'owns_products' => '1',
    ])->assertRedirect();

    $series = OwnerTypeModel::query()->with('parentTypes')->where('code', 'series')->firstOrFail();

    expect($series->restricts_parents)->toBeTrue()
        ->and($series->can_be_root)->toBeFalse()
        ->and($series->parentTypes->pluck('code')->all())->toBe(['vendor']);

    $this->get(route('atrium.keystone.owner-types.index'))->assertOk()->assertSee('series');

    $this->patch(route('atrium.keystone.owner-types.update', 'series'), ['any_parent' => '1', 'can_be_root' => '1', 'owns_products' => '0'])->assertRedirect();

    expect($series->fresh()?->restricts_parents)->toBeFalse();
});

it('builds, moves and browses an ownership chain', function (): void {
    OwnerTypeModel::factory()->create(['code' => 'vendor']);

    $this->post(route('atrium.keystone.owners.store'), ['code' => 'acme', 'type' => 'vendor', 'labels' => ['en' => 'Acme']])
        ->assertRedirect(route('atrium.keystone.owners.show', 'acme'));

    $this->post(route('atrium.keystone.owners.store'), ['code' => 'acme-eu', 'type' => 'vendor', 'parent' => 'acme'])->assertRedirect();
    $this->post(route('atrium.keystone.owners.store'), ['code' => 'initech', 'type' => 'vendor'])->assertRedirect();

    $this->get(route('atrium.keystone.owners.show', 'acme-eu'))
        ->assertOk()
        ->assertSee('data-testid="owner-chain"', false)
        ->assertSeeInOrder(['Acme', 'acme-eu']);

    $this->patch(route('atrium.keystone.owners.update', 'acme-eu'), ['parent' => 'initech'])->assertSessionHasNoErrors();

    expect(OwnerModel::query()->where('code', 'acme-eu')->firstOrFail()->parent?->code)->toBe('initech');

    $this->get(route('atrium.keystone.owners.index', ['type' => 'vendor']))->assertOk()->assertSee('acme-eu');

    $this->from(route('atrium.keystone.owners.show', 'initech'))
        ->delete(route('atrium.keystone.owners.destroy', 'initech'))
        ->assertSessionHasErrors('owner');
});

it('assigns a product to an owner from its page', function (): void {
    OwnerTypeModel::factory()->create(['code' => 'vendor']);
    $this->post(route('atrium.keystone.owners.store'), ['code' => 'acme', 'type' => 'vendor']);
    ProductModel::factory()->create(['identifier' => 'TEE-1']);

    $this->patch(route('atrium.keystone.products.update', 'TEE-1'), ['owner' => 'acme'])->assertSessionHasNoErrors();

    expect(ProductModel::query()->firstOrFail()->owner?->code)->toBe('acme');

    $this->get(route('atrium.keystone.products.index', ['owner' => 'acme']))->assertOk()->assertSee('TEE-1');
    $this->get(route('atrium.keystone.owners.show', 'acme'))->assertOk()->assertSee('1 product(s)', false);
});
