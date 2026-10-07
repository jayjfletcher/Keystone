<?php

declare(strict_types=1);

use JayI\Keystone\Domains\Product\Models\ProductModel;
use JayI\Keystone\Domains\Search\Contracts\SearchEngine;
use JayI\Keystone\Domains\Search\Services\ElasticsearchEngine;
use JayI\Keystone\Tests\Fixtures\Catalog;
use JayI\Stretch\Domains\Connection\Contracts\ClientContract;
use JayI\Stretch\Stretch;

beforeEach(function (): void {
    config()->set('keystone.search.engine', 'elasticsearch');
    config()->set('keystone.search.elasticsearch.index', 'test_products');

    $this->client = Mockery::mock(ClientContract::class);
    app()->instance('stretch', new Stretch($this->client));

    Catalog::apparel();
});

it('resolves the Stretch-backed engine', function (): void {
    expect(app(SearchEngine::class))->toBeInstanceOf(ElasticsearchEngine::class);
});

it('indexes a product with its inherited values after each write', function (): void {
    $documents = [];

    $this->client->shouldReceive('bulk')->andReturnUsing(function (array $params) use (&$documents): array {
        $documents[] = $params['body'];

        return ['errors' => false];
    });

    $this->postJson('/keystone/owner-types', ['code' => 'vendor'])->assertCreated();
    $this->postJson('/keystone/owners', ['code' => 'acme', 'type' => 'vendor'])->assertCreated();
    $this->postJson('/keystone/owners', ['code' => 'acme-eu', 'type' => 'vendor', 'parent' => 'acme'])->assertCreated();

    $this->postJson('/keystone/categories', ['code' => 'master'])->assertCreated();
    $this->postJson('/keystone/categories', ['code' => 'shirts', 'parent' => 'master'])->assertCreated();

    $this->postJson('/keystone/product-models', ['code' => 'polo', 'family_variant' => 'shirts_by_size', 'owner' => 'acme-eu', 'categories' => ['shirts'], 'values' => ['name' => Catalog::value('Polo'), 'rating' => Catalog::value(4.5)]])->assertCreated();
    $this->postJson('/keystone/products', ['identifier' => 'POLO-M', 'parent' => 'polo', 'values' => ['size' => Catalog::value('m')]])->assertCreated();

    expect($documents)->toHaveCount(1);

    [$action, $document] = $documents[0];

    expect($action['index']['_index'])->toBe('test_products')
        ->and($document)->toMatchArray([
            'identifier' => 'POLO-M',
            'family' => 'shirts',
            'parent' => 'polo',
            'owner' => 'acme-eu',
            'owners' => ['acme', 'acme-eu'],
            'categories' => ['shirts'],
            'category_tree' => ['master', 'shirts'],
            'enabled' => true,
        ])
        ->and($document['values']['name']['<all_channels>']['<all_locales>'])->toBe('Polo')
        ->and($document['values']['rating']['<all_channels>']['<all_locales>'])->toBe(4.5)
        ->and($document['values']['size']['<all_channels>']['<all_locales>'])->toBe('m')
        ->and($document['text'])->toContain('POLO-M', 'Polo', 'm');

    // Changing the model re-indexes the variants beneath it.
    $this->patchJson('/keystone/product-models/polo', ['values' => ['name' => Catalog::value('Polo shirt')]])->assertOk();

    expect($documents)->toHaveCount(2)
        ->and($documents[1][1]['values']['name']['<all_channels>']['<all_locales>'])->toBe('Polo shirt');

    // Deleting removes it.
    $this->deleteJson('/keystone/products/POLO-M')->assertNoContent();

    expect($documents[2][0])->toHaveKey('delete');
});

it('translates a query into Elasticsearch filters and facets', function (): void {
    $this->client->shouldReceive('bulk')->andReturn(['errors' => false]);

    $this->postJson('/keystone/products', ['identifier' => 'TEE-RED', 'values' => ['color' => Catalog::value('red')]])->assertCreated();
    $this->postJson('/keystone/products', ['identifier' => 'TEE-BLUE', 'values' => ['color' => Catalog::value('blue')]])->assertCreated();

    $sent = null;

    $this->client->shouldReceive('search')->once()->andReturnUsing(function (array $params) use (&$sent): array {
        $sent = $params;

        return [
            'hits' => [
                'total' => ['value' => 1, 'relation' => 'eq'],
                'hits' => [['_id' => ProductModel::query()->where('identifier', 'TEE-RED')->value('id')]],
            ],
            'aggregations' => [
                'facet_color' => ['buckets' => [['key' => 'red', 'doc_count' => 1], ['key' => 'blue', 'doc_count' => 1]]],
            ],
        ];
    });

    $this->getJson('/keystone/products?'.http_build_query([
        'search' => 'tee',
        'family' => 'shirts',
        'enabled' => 1,
        'owner' => 'acme',
        'category' => 'master',
        'status' => 'draft',
        'published' => 0,
        'complete' => ['scope' => 'ecommerce', 'locale' => 'en', 'min' => 80],
        'filters' => [
            ['attribute' => 'color', 'operator' => 'in', 'value' => ['red', 'blue']],
            ['attribute' => 'weight', 'operator' => '>=', 'value' => 100],
            ['attribute' => 'tags', 'operator' => 'empty'],
        ],
        'facets' => ['color'],
        'sort' => '-updated_at',
        'per_page' => 10,
        'page' => 2,
    ]))
        ->assertOk()
        ->assertJsonPath('data.0.identifier', 'TEE-RED')
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('facets.color', ['red' => 1, 'blue' => 1]);

    $body = $sent['body'];
    $filters = json_encode($body['query'], JSON_THROW_ON_ERROR);

    expect($sent['index'])->toBe('test_products')
        ->and($body['from'])->toBe(10)
        ->and($body['size'])->toBe(10)
        ->and($body['sort'][0])->toBe(['updated_at' => ['order' => 'desc']])
        ->and($filters)->toContain('"term":{"family":"shirts"}')
        ->and($filters)->toContain('"term":{"enabled":true}')
        ->and($filters)->toContain('"term":{"owners":"acme"}')
        ->and($filters)->toContain('"term":{"category_tree":"master"}')
        ->and($filters)->toContain('"term":{"status":"draft"}')
        ->and($filters)->toContain('"term":{"published":false}')
        ->and($filters)->toContain('"range":{"completeness.ecommerce.en":{"gte":80}}')
        ->and($filters)->toContain('"terms":{"values.color.<all_channels>.<all_locales>":["red","blue"]}')
        ->and($filters)->toContain('"range":{"values.weight.<all_channels>.<all_locales>.amount":{"gte":100}}')
        ->and($filters)->toContain('"must_not"')
        ->and($filters)->toContain('"match":{"text"')
        ->and($body['aggs']['facet_color']['terms']['field'])->toBe('values.color.<all_channels>.<all_locales>');
});

it('rebuilds the index from the products table', function (): void {
    $this->client->shouldReceive('bulk')->andReturn(['errors' => false]);

    $this->postJson('/keystone/products', ['identifier' => 'A'])->assertCreated();
    $this->postJson('/keystone/products', ['identifier' => 'B'])->assertCreated();

    $this->client->shouldReceive('indexExists')->with('test_products')->once()->andReturn(true);
    $this->client->shouldReceive('deleteIndex')->with('test_products')->once()->andReturn(['acknowledged' => true]);
    $this->client->shouldReceive('createIndex')->withArgs(fn (string $index, array $settings): bool => $index === 'test_products'
        && $settings['mappings']['properties']['identifier'] === ['type' => 'keyword'])->once()->andReturn(['acknowledged' => true]);

    $this->artisan('keystone:search:reindex')
        ->expectsOutputToContain('Indexed 2 products.')
        ->assertSuccessful();
});
