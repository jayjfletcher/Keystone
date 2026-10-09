<?php

declare(strict_types=1);

use RefactorCircus\Keystone\Domains\Category\Models\CategoryModel;
use RefactorCircus\Keystone\Tests\Fixtures\Catalog;

function category(string $code, ?string $parent = null, int $sort = 0): void
{
    test()->postJson('/keystone/categories', ['code' => $code, 'parent' => $parent, 'sort_order' => $sort])->assertCreated();
}

beforeEach(function (): void {
    // Two trees: master → clothing → (shirts, pants); web → sale.
    category('master');
    category('clothing', 'master');
    category('shirts', 'clothing', 2);
    category('pants', 'clothing', 1);
    category('web');
    category('sale', 'web');
});

it('keeps several independent trees', function (): void {
    expect(collect($this->getJson('/keystone/categories?roots=1')->json('data'))->pluck('code')->all())->toBe(['master', 'web']);

    $this->getJson('/keystone/categories/shirts')
        ->assertOk()
        ->assertJsonPath('data.parent', 'clothing')
        ->assertJsonPath('data.depth', 2)
        ->assertJsonPath('data.chain', ['master', 'clothing', 'shirts']);

    // Siblings in sort order.
    expect(collect($this->getJson('/keystone/categories?parent=clothing')->json('data'))->pluck('code')->all())->toBe(['pants', 'shirts'])
        ->and(collect($this->getJson('/keystone/categories?under=master')->json('data'))->pluck('code')->all())->toBe(['clothing', 'pants', 'shirts']);
});

it('moves a branch within a tree, across trees and to the root', function (): void {
    $this->patchJson('/keystone/categories/clothing', ['parent' => 'sale'])->assertOk();

    $this->getJson('/keystone/categories/shirts')->assertJsonPath('data.chain', ['web', 'sale', 'clothing', 'shirts'])->assertJsonPath('data.depth', 3);

    $this->patchJson('/keystone/categories/clothing', ['parent' => null])->assertOk();

    $this->getJson('/keystone/categories/shirts')->assertJsonPath('data.chain', ['clothing', 'shirts'])->assertJsonPath('data.depth', 1);
    expect(CategoryModel::query()->where('code', 'clothing')->firstOrFail()->tree()->code)->toBe('clothing');
});

it('refuses cycles and code changes', function (): void {
    $this->patchJson('/keystone/categories/clothing', ['parent' => 'shirts'])->assertUnprocessable()->assertJsonValidationErrors('parent');
    $this->patchJson('/keystone/categories/clothing', ['code' => 'apparel'])->assertUnprocessable()->assertJsonValidationErrors('code');
});

it('files products and models in categories, and variants inherit them', function (): void {
    Catalog::apparel();

    $this->postJson('/keystone/products', ['identifier' => 'TEE-1', 'categories' => ['shirts', 'sale']])
        ->assertCreated()
        ->assertJsonPath('data.categories', ['sale', 'shirts']);

    $this->postJson('/keystone/products', ['identifier' => 'X', 'categories' => ['nope']])
        ->assertUnprocessable()->assertJsonValidationErrors('categories.0');

    $this->postJson('/keystone/product-models', ['code' => 'polo', 'family_variant' => 'shirts_by_size', 'categories' => ['shirts']])->assertCreated();
    $this->postJson('/keystone/products', ['identifier' => 'POLO-M', 'parent' => 'polo', 'categories' => ['sale'], 'values' => ['size' => Catalog::value('m')]])
        ->assertCreated()
        ->assertJsonPath('data.categories', ['sale', 'shirts']);

    // Replaced, not appended.
    $this->patchJson('/keystone/products/TEE-1', ['categories' => ['pants']])->assertOk()->assertJsonPath('data.categories', ['pants']);
    $this->patchJson('/keystone/products/TEE-1', ['enabled' => false])->assertOk()->assertJsonPath('data.categories', ['pants']);
});

it('finds products in a category and everything beneath it', function (): void {
    Catalog::apparel();

    $this->postJson('/keystone/products', ['identifier' => 'SHIRT', 'categories' => ['shirts']])->assertCreated();
    $this->postJson('/keystone/products', ['identifier' => 'PANTS', 'categories' => ['pants']])->assertCreated();
    $this->postJson('/keystone/products', ['identifier' => 'NONE'])->assertCreated();
    $this->postJson('/keystone/product-models', ['code' => 'polo', 'family_variant' => 'shirts_by_color_size', 'categories' => ['sale']])->assertCreated();
    $this->postJson('/keystone/product-models', ['code' => 'polo-red', 'parent' => 'polo', 'values' => ['color' => Catalog::value('red')]])->assertCreated();
    $this->postJson('/keystone/products', ['identifier' => 'POLO-RED-M', 'parent' => 'polo-red', 'values' => ['size' => Catalog::value('m')]])->assertCreated();

    $identifiers = fn (string $category): array => collect($this->getJson('/keystone/products?category='.$category)->json('data'))->pluck('identifier')->all();

    expect($identifiers('clothing'))->toBe(['PANTS', 'SHIRT'])
        ->and($identifiers('master'))->toBe(['PANTS', 'SHIRT'])
        ->and($identifiers('shirts'))->toBe(['SHIRT'])
        ->and($identifiers('web'))->toBe(['POLO-RED-M'])
        ->and($identifiers('missing'))->toBe([]);
});

it('deletes leaves only, unassigning their products', function (): void {
    $this->postJson('/keystone/products', ['identifier' => 'SHIRT', 'categories' => ['shirts', 'sale']])->assertCreated();

    $this->deleteJson('/keystone/categories/clothing')
        ->assertConflict()
        ->assertJsonPath('message', 'Category "clothing" still has 2 child categories. Move or delete them first.');

    $this->deleteJson('/keystone/categories/shirts')->assertNoContent();

    $this->getJson('/keystone/products/SHIRT')->assertJsonPath('data.categories', ['sale']);
});
