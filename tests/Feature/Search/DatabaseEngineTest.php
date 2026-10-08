<?php

declare(strict_types=1);

use JayI\Keystone\Domains\Search\Contracts\SearchEngine;
use JayI\Keystone\Domains\Search\Exceptions\UnsupportedSearchException;
use JayI\Keystone\Domains\Search\Services\ScoutEngine;
use JayI\Keystone\Tests\Fixtures\Catalog;

beforeEach(function (): void {
    Catalog::apparel();

    $product = fn (string $identifier, array $values, array $extra = []) => $this->postJson('/keystone/products', [
        'identifier' => $identifier,
        'values' => array_map(fn (mixed $data): array => Catalog::value($data), $values),
    ] + $extra)->assertCreated();

    $product('TEE-RED', ['name' => 'Red tee', 'color' => 'red', 'pack_size' => 1, 'organic' => true, 'tags' => ['summer', 'sale'], 'weight' => ['amount' => 150, 'unit' => 'gram']], ['family' => 'shirts']);
    $product('TEE-BLUE', ['name' => 'Blue tee', 'color' => 'blue', 'pack_size' => 3, 'organic' => false, 'tags' => ['new'], 'weight' => ['amount' => 250, 'unit' => 'gram']], ['family' => 'shirts']);
    $product('MUG', ['name' => 'Coffee mug', 'pack_size' => 6], ['enabled' => false]);

    // A variant whose name and color live on its models.
    $this->postJson('/keystone/product-models', ['code' => 'polo', 'family_variant' => 'shirts_by_color_size', 'values' => ['name' => Catalog::value('Green polo')]])->assertCreated();
    $this->postJson('/keystone/product-models', ['code' => 'polo-green', 'parent' => 'polo', 'values' => ['color' => Catalog::value('green')]])->assertCreated();
    $this->postJson('/keystone/products', ['identifier' => 'POLO-GREEN-M', 'parent' => 'polo-green', 'values' => ['size' => Catalog::value('m')]])->assertCreated();
});

/**
 * @param  array<string, mixed>  $query
 * @return array<int, string>
 */
function identifiersFor(array $query): array
{
    return collect(test()->getJson('/keystone/products?'.http_build_query($query))->assertOk()->json('data'))
        ->pluck('identifier')
        ->all();
}

it('lists every product, sorted and page-numbered', function (): void {
    $this->getJson('/keystone/products?per_page=2&sort=-identifier')
        ->assertOk()
        ->assertJsonPath('meta.total', 4)
        ->assertJsonPath('meta.last_page', 2)
        ->assertJsonPath('data.0.identifier', 'TEE-RED')
        ->assertJsonPath('data.1.identifier', 'TEE-BLUE');

    expect(identifiersFor(['per_page' => 2, 'page' => 2, 'sort' => '-identifier']))->toBe(['POLO-GREEN-M', 'MUG']);
});

it('searches text in identifiers and values, inherited ones included', function (): void {
    expect(identifiersFor(['search' => 'tee']))->toBe(['TEE-BLUE', 'TEE-RED'])
        ->and(identifiersFor(['search' => 'polo']))->toBe(['POLO-GREEN-M'])
        ->and(identifiersFor(['search' => 'MUG']))->toBe(['MUG']);
});

it('filters by family, enabled and parent', function (): void {
    expect(identifiersFor(['family' => 'shirts']))->toBe(['POLO-GREEN-M', 'TEE-BLUE', 'TEE-RED'])
        ->and(identifiersFor(['enabled' => 0]))->toBe(['MUG'])
        ->and(identifiersFor(['parent' => 'polo']))->toBe(['POLO-GREEN-M'])
        ->and(identifiersFor(['parent' => 'polo-green']))->toBe(['POLO-GREEN-M']);
});

it('filters by value', function (array $filter, array $expected): void {
    expect(identifiersFor(['filters' => [$filter]]))->toBe($expected);
})->with([
    'select =' => [['attribute' => 'color', 'operator' => '=', 'value' => 'red'], ['TEE-RED']],
    'inherited select =' => [['attribute' => 'color', 'operator' => '=', 'value' => 'green'], ['POLO-GREEN-M']],
    'select !=' => [['attribute' => 'color', 'operator' => '!=', 'value' => 'red'], ['MUG', 'POLO-GREEN-M', 'TEE-BLUE']],
    'select in' => [['attribute' => 'color', 'operator' => 'in', 'value' => ['red', 'green']], ['POLO-GREEN-M', 'TEE-RED']],
    'select not_in' => [['attribute' => 'color', 'operator' => 'not_in', 'value' => ['red', 'green']], ['MUG', 'TEE-BLUE']],
    'multiselect contains' => [['attribute' => 'tags', 'operator' => '=', 'value' => 'sale'], ['TEE-RED']],
    'boolean' => [['attribute' => 'organic', 'operator' => '=', 'value' => false], ['TEE-BLUE']],
    'number >' => [['attribute' => 'pack_size', 'operator' => '>', 'value' => 1], ['MUG', 'TEE-BLUE']],
    'number <=' => [['attribute' => 'pack_size', 'operator' => '<=', 'value' => 3], ['TEE-BLUE', 'TEE-RED']],
    'metric >=' => [['attribute' => 'weight', 'operator' => '>=', 'value' => 200], ['TEE-BLUE']],
    'empty' => [['attribute' => 'color', 'operator' => 'empty'], ['MUG']],
    'not_empty' => [['attribute' => 'tags', 'operator' => 'not_empty'], ['TEE-BLUE', 'TEE-RED']],
]);

it('combines filters', function (): void {
    expect(identifiersFor([
        'family' => 'shirts',
        'filters' => [
            ['attribute' => 'color', 'operator' => 'in', 'value' => ['red', 'blue']],
            ['attribute' => 'pack_size', 'operator' => '>=', 'value' => 2],
        ],
    ]))->toBe(['TEE-BLUE']);
});

it('refuses filters the engine cannot run', function (): void {
    $this->getJson('/keystone/products?'.http_build_query(['filters' => [['attribute' => 'price', 'operator' => '=', 'value' => 1]]]))
        ->assertUnprocessable()
        ->assertJsonPath('message', 'The database search engine cannot filter attribute "price" with "=".');

    $this->getJson('/keystone/products?'.http_build_query(['filters' => [['attribute' => 'color', 'operator' => 'like']]]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('filters.0.operator');
});

it('keeps no index', function (): void {
    $this->artisan('keystone:search:reindex')
        ->expectsOutputToContain('the database engine keeps no index')
        ->assertSuccessful();
});

it('searches with Scout when no engine is named and Scout is installed', function (): void {
    config()->set('keystone.search.engine', null);

    expect(app(SearchEngine::class))->toBeInstanceOf(ScoutEngine::class);
});

it('names the replacement when the removed Elasticsearch engine is configured', function (): void {
    config()->set('keystone.search.engine', 'elasticsearch');

    app(SearchEngine::class);
})->throws(UnsupportedSearchException::class, 'no longer ships the elasticsearch search engine');
