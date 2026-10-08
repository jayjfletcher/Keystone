<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use JayI\Keystone\Domains\Product\Mcp\Tools\ListProductsTool;
use JayI\Keystone\Tests\Fixtures\Catalog;

beforeEach(function (): void {
    Catalog::apparel();
});

it('answers 304 to a client that already has the product', function (): void {
    $this->postJson('/keystone/products', ['identifier' => 'TEE-1', 'values' => ['name' => Catalog::value('Tee')]])->assertCreated();

    $etag = $this->getJson('/keystone/products/TEE-1')->assertOk()->headers->get('ETag');

    expect($etag)->not->toBeNull();

    $this->getJson('/keystone/products/TEE-1', ['If-None-Match' => (string) $etag])
        ->assertStatus(304)
        ->assertContent('');

    $this->patchJson('/keystone/products/TEE-1', ['values' => ['name' => Catalog::value('New tee')]])->assertOk();

    $this->getJson('/keystone/products/TEE-1', ['If-None-Match' => (string) $etag])
        ->assertOk()
        ->assertJsonPath('data.values.name.0.data', 'New tee');
});

it('lists only products changed since a moment', function (): void {
    $this->postJson('/keystone/products', ['identifier' => 'OLD'])->assertCreated();
    $this->postJson('/keystone/products', ['identifier' => 'NEW'])->assertCreated();

    DB::table('keystone_products')->where('identifier', 'OLD')->update(['updated_at' => now()->subDay()]);

    $this->getJson('/keystone/products?updated_since='.urlencode(now()->subHour()->toIso8601String()))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.identifier', 'NEW');
});

it('filters by updated_since over MCP too', function (): void {
    $this->postJson('/keystone/products', ['identifier' => 'OLD'])->assertCreated();
    $this->postJson('/keystone/products', ['identifier' => 'NEW'])->assertCreated();

    DB::table('keystone_products')->where('identifier', 'OLD')->update(['updated_at' => now()->subDay()]);

    mcpTool(ListProductsTool::class, ['updated_since' => now()->subHour()->toIso8601String()])
        ->assertOk()
        ->assertSee('NEW')
        ->assertDontSee('OLD');
});
