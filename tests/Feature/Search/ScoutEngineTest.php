<?php

declare(strict_types=1);

use JayI\Keystone\Domains\Search\Contracts\SearchEngine;
use JayI\Keystone\Domains\Search\Services\ScoutEngine;
use JayI\Keystone\Tests\Fixtures\Catalog;
use JayI\Keystone\Tests\Fixtures\RecordingScoutEngine;
use Laravel\Scout\EngineManager;

beforeEach(function (): void {
    $scout = $this->scout = new RecordingScoutEngine;

    // Scout's provider makes the manager a singleton in a real application.
    app()->singleton(EngineManager::class, fn ($app): EngineManager => new EngineManager($app));
    app(EngineManager::class)->extend('recording', fn (): RecordingScoutEngine => $scout);

    config()->set('scout.driver', 'recording');
    config()->set('keystone.search.engine', 'scout');

    Catalog::apparel();

    $this->postJson('/keystone/products', ['identifier' => 'TEE-RED', 'family' => 'shirts', 'values' => ['color' => Catalog::value('red')]])->assertCreated();
    $this->postJson('/keystone/products', ['identifier' => 'TEE-BLUE', 'family' => 'shirts', 'values' => ['color' => Catalog::value('blue')]])->assertCreated();
    $this->postJson('/keystone/products', ['identifier' => 'MUG', 'enabled' => false])->assertCreated();
});

it('resolves the Scout-backed engine', function (): void {
    expect(app(SearchEngine::class))->toBeInstanceOf(ScoutEngine::class);
});

it('indexes products through the configured Scout engine', function (): void {
    expect($this->scout->documents)->toHaveCount(3)
        ->and(collect($this->scout->documents)->pluck('identifier')->sort()->values()->all())->toBe(['MUG', 'TEE-BLUE', 'TEE-RED']);

    $this->deleteJson('/keystone/products/MUG')->assertNoContent();

    expect($this->scout->documents)->toHaveCount(2);
});

it('searches with equality filters', function (): void {
    $this->getJson('/keystone/products?'.http_build_query([
        'family' => 'shirts',
        'filters' => [['attribute' => 'color', 'operator' => 'in', 'value' => ['red']]],
    ]))
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.identifier', 'TEE-RED');

    expect($this->scout->searches[0]->wheres)->toBe([['field' => 'family', 'operator' => '=', 'value' => 'shirts']]);
});

it('passes comparisons through to the Scout engine', function (): void {
    $this->patchJson('/keystone/products/TEE-BLUE', ['values' => ['weight' => Catalog::value(['amount' => 300, 'unit' => 'gram'])]])->assertOk();

    $this->getJson('/keystone/products?'.http_build_query(['filters' => [['attribute' => 'weight', 'operator' => '>', 'value' => '200']]]))
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.identifier', 'TEE-BLUE');

    expect(end($this->scout->searches)->wheres[0])->toBe(['field' => 'values.weight.<all_channels>.<all_locales>.amount', 'operator' => '>', 'value' => 200]);
});

it('refuses filters Scout cannot express', function (): void {
    $this->getJson('/keystone/products?'.http_build_query(['filters' => [['attribute' => 'color', 'operator' => 'empty']]]))
        ->assertUnprocessable()
        ->assertJsonPath('message', 'The scout search engine cannot filter attribute "color" with "empty".');
});

it('rebuilds the Scout index', function (): void {
    $this->artisan('keystone:search:reindex')->expectsOutputToContain('Indexed 3 products.')->assertSuccessful();

    expect($this->scout->flushes)->toBe(1)
        ->and($this->scout->documents)->toHaveCount(3);
});
