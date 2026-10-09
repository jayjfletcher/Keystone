<?php

declare(strict_types=1);

use Illuminate\Testing\TestResponse;
use RefactorCircus\Keystone\Domains\Product\Models\ProductModel;
use RefactorCircus\Keystone\Domains\Workflow\Models\VersionModel;
use RefactorCircus\Keystone\Tests\Fixtures\Catalog;
use Workbench\App\Models\User;

beforeEach(function (): void {
    Catalog::apparel();

    $this->postJson('/keystone/association-types', ['code' => 'cross_sell'])->assertCreated();
    $this->postJson('/keystone/categories', ['code' => 'master'])->assertCreated();
    $this->postJson('/keystone/products', ['identifier' => 'CAP'])->assertCreated();
    $this->postJson('/keystone/products', ['identifier' => 'TEE', 'family' => 'shirts', 'values' => ['name' => Catalog::value('Tee')]])->assertCreated();
});

function transition(string $identifier, string $transition, ?string $comment = null): TestResponse
{
    return test()->postJson('/keystone/products/'.$identifier.'/transitions', array_filter(['transition' => $transition, 'comment' => $comment]));
}

it('moves a product through review to publication', function (): void {
    $this->getJson('/keystone/products/TEE')->assertJsonPath('data.status', 'draft')->assertJsonPath('data.published_version', null);

    transition('TEE', 'approve')
        ->assertConflict()
        ->assertJsonPath('message', 'Product "TEE" is draft, so it cannot approve. It must be in review.');

    transition('TEE', 'submit')->assertOk()->assertJsonPath('data.status', 'in_review');
    transition('TEE', 'reject', 'Needs a better name')->assertOk()->assertJsonPath('data.status', 'draft');
    transition('TEE', 'submit')->assertOk();
    transition('TEE', 'approve')->assertOk()->assertJsonPath('data.status', 'approved');

    $published = transition('TEE', 'publish')->assertOk()->json('data');

    expect($published['published_version'])->toBeInt()
        ->and($published['published_at'])->not->toBeNull();

    $this->getJson('/keystone/products?published=1')->assertJsonPath('data.0.identifier', 'TEE')->assertJsonCount(1, 'data');
    $this->getJson('/keystone/products?status=approved')->assertJsonCount(1, 'data');
});

it('keeps the published version while the working copy changes', function (): void {
    transition('TEE', 'submit');
    transition('TEE', 'approve');
    $version = transition('TEE', 'publish')->json('data.published_version');

    // Editing an approved product sends it back for review.
    $this->patchJson('/keystone/products/TEE', ['values' => ['name' => Catalog::value('New tee')]])
        ->assertOk()
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.published_version', $version);

    $this->getJson('/keystone/products/TEE/versions/published')
        ->assertOk()
        ->assertJsonPath('data.version', $version)
        ->assertJsonPath('data.snapshot.values.name.0.data', 'Tee');

    $this->getJson('/keystone/products/TEE/versions/latest')->assertJsonPath('data.snapshot.values.name.0.data', 'New tee');
});

it('publishes without approval when told to', function (): void {
    config()->set('keystone.workflow.require_approval', false);

    transition('TEE', 'publish')->assertOk();
});

it('requires completeness to submit when told to', function (): void {
    config()->set('keystone.workflow.require_complete', true);

    $this->patchJson('/keystone/families/shirts', ['attributes' => [
        ['attribute' => 'name', 'is_required' => true],
        ['attribute' => 'color', 'is_required' => true],
        ['attribute' => 'size'], ['attribute' => 'ean'], ['attribute' => 'price'], ['attribute' => 'weight'],
    ]])->assertOk();

    transition('TEE', 'submit')
        ->assertConflict()
        ->assertJsonPath('message', 'Product "TEE" is not complete for ecommerce / en, ecommerce / fr, print / en, so it cannot be submitted.');

    $this->patchJson('/keystone/products/TEE', ['values' => ['color' => Catalog::value('red')]])->assertOk();

    transition('TEE', 'submit')->assertOk();
    transition('CAP', 'submit')->assertConflict()->assertJsonPath('message', 'Product "CAP" is not complete for any channel: it has no family, so it cannot be submitted.');
});

it('archives, unpublishes and restores', function (): void {
    config()->set('keystone.workflow.require_approval', false);

    transition('TEE', 'publish')->assertOk();
    transition('TEE', 'archive')->assertOk()->assertJsonPath('data.status', 'archived')->assertJsonPath('data.published_version', null);
    transition('TEE', 'publish')->assertConflict();
    transition('TEE', 'restore')->assertOk()->assertJsonPath('data.status', 'draft');
    transition('TEE', 'publish')->assertOk();
    transition('TEE', 'unpublish')->assertOk()->assertJsonPath('data.published_version', null);
});

it('records every change with its author', function (): void {
    $user = User::forceCreate(['name' => 'Ann', 'email' => 'ann@example.test', 'password' => 'x']);
    $this->actingAs($user);

    $this->patchJson('/keystone/products/TEE', ['values' => ['name' => Catalog::value('Classic tee')], 'categories' => ['master']])->assertOk();
    // Writing the same thing again is not history.
    $this->patchJson('/keystone/products/TEE', ['values' => ['name' => Catalog::value('Classic tee')]])->assertOk();
    transition('TEE', 'submit', 'Ready')->assertOk();

    $versions = $this->getJson('/keystone/products/TEE/versions')->assertOk()->json('data');

    expect(array_column($versions, 'action'))->toBe(['submitted', 'updated', 'created'])
        ->and($versions[0]['comment'])->toBe('Ready')
        ->and($versions[0]['changes']['status'])->toBe(['old' => 'draft', 'new' => 'in_review'])
        ->and($versions[1]['author'])->toBe(['type' => $user->getMorphClass(), 'id' => (string) $user->getKey()])
        ->and($versions[1]['changes']['values.name']['new'])->toBe([['locale' => null, 'scope' => null, 'data' => 'Classic tee']])
        ->and($versions[1]['changes']['categories'])->toBe(['old' => [], 'new' => ['master']])
        ->and($versions[2]['author'])->toBeNull();

    $this->getJson('/keystone/products/TEE/versions/2')->assertOk()->assertJsonPath('data.snapshot.categories', ['master']);
    $this->getJson('/keystone/products/TEE/versions/9')->assertNotFound();
    $this->getJson('/keystone/products/TEE/versions/published')->assertNotFound();
});

it('reverts to an earlier version', function (): void {
    $this->patchJson('/keystone/products/TEE', [
        'values' => ['name' => Catalog::value('Renamed'), 'color' => Catalog::value('red')],
        'categories' => ['master'],
        'associations' => ['cross_sell' => ['products' => ['CAP']]],
        'enabled' => false,
    ])->assertOk();

    $this->postJson('/keystone/products/TEE/revert', ['version' => 1])
        ->assertOk()
        ->assertJsonPath('data.values.name.0.data', 'Tee')
        ->assertJsonMissingPath('data.values.color')
        ->assertJsonPath('data.categories', [])
        ->assertJsonPath('data.associations', [])
        ->assertJsonPath('data.enabled', true);

    $latest = $this->getJson('/keystone/products/TEE/versions/latest')->json('data');

    expect($latest['action'])->toBe('reverted')
        ->and($latest['comment'])->toBe('Reverted to version 1.');

    $this->postJson('/keystone/products/TEE/revert', ['version' => 99])
        ->assertUnprocessable()->assertJsonValidationErrors(['version' => 'Product "TEE" has no version 99.']);
});

it('forgets the versions of deleted products', function (): void {
    $this->deleteJson('/keystone/products/TEE')->assertNoContent();

    expect(VersionModel::query()->where('versionable_id', '!=', ProductModel::query()->where('identifier', 'CAP')->value('id'))->count())->toBe(0);
});
