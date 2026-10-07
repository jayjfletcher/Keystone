<?php

declare(strict_types=1);

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use JayI\Keystone\Domains\Family\Models\FamilyVariantModel;
use JayI\Keystone\Domains\Product\Models\ProductModel;
use JayI\Keystone\Domains\ProductModel\Models\ProductModelModel;
use JayI\Keystone\Tests\Fixtures\Catalog;

beforeEach(function (): void {
    ValidateCsrfToken::except(['*']);

    // Atrium denies access outside local until a gate is defined.
    app()->detectEnvironment(fn (): string => 'local');

    Catalog::apparel();
});

it('creates a product and edits its values', function (): void {
    $this->get(route('atrium.keystone.products.create'))->assertOk()->assertSee('shirts');

    $this->post(route('atrium.keystone.products.store'), ['identifier' => 'TEE-1', 'family' => 'shirts'])
        ->assertRedirect(route('atrium.keystone.products.show', 'TEE-1'));

    $this->get(route('atrium.keystone.products.show', 'TEE-1'))
        ->assertOk()
        ->assertSee('data-attribute="weight"', false)
        ->assertSee('data-attribute="tags"', false);

    $this->patch(route('atrium.keystone.products.update', 'TEE-1'), [
        'family' => 'shirts',
        'enabled' => '0',
        'v' => [
            'name' => 'Classic tee',
            'description' => 'Soft',
            'color' => 'red',
            'tags' => ['', 'summer', 'new'],
            'organic' => '1',
            'weight' => ['amount' => '180', 'unit' => 'gram'],
            'price' => ['USD' => '19.99', 'EUR' => ''],
            'pack_size' => '',
        ],
    ])->assertRedirect(route('atrium.keystone.products.show', ['product' => 'TEE-1', 'locale' => 'en', 'channel' => 'ecommerce']))->assertSessionHasNoErrors();

    $product = ProductModel::query()->where('identifier', 'TEE-1')->firstOrFail();

    expect($product->enabled)->toBeFalse()
        ->and($product->value('name'))->toBe('Classic tee')
        ->and($product->value('description', null, 'en'))->toBe('Soft')
        ->and($product->value('tags'))->toBe(['summer', 'new'])
        ->and($product->value('organic'))->toBeTrue()
        ->and($product->value('weight'))->toBe(['amount' => '180', 'unit' => 'gram'])
        ->and($product->value('price'))->toBe([['amount' => '19.99', 'currency' => 'USD']])
        ->and($product->value('pack_size'))->toBeNull();

    $this->get(route('atrium.keystone.products.index', ['search' => 'TEE']))->assertOk()->assertSee('TEE-1')->assertSee('Classic tee');
});

it('shows validation errors next to the value', function (): void {
    $this->post(route('atrium.keystone.products.store'), ['identifier' => 'TEE-1', 'family' => 'shirts']);

    $this->from(route('atrium.keystone.products.show', 'TEE-1'))
        ->patch(route('atrium.keystone.products.update', 'TEE-1'), ['v' => ['pack_size' => '0']])
        ->assertSessionHasErrors('values.pack_size.0.data');
});

it('adds attributes one at a time to a product without a family', function (): void {
    $this->post(route('atrium.keystone.products.store'), ['identifier' => 'LOOSE']);

    $this->get(route('atrium.keystone.products.show', ['product' => 'LOOSE', 'add' => 'color']))
        ->assertOk()
        ->assertSee('data-attribute="color"', false)
        ->assertDontSee('data-attribute="size"', false);

    $this->patch(route('atrium.keystone.products.update', 'LOOSE'), ['add' => 'color', 'v' => ['color' => 'blue']])->assertSessionHasNoErrors();

    expect(ProductModel::query()->where('identifier', 'LOOSE')->firstOrFail()->value('color'))->toBe('blue');
});

it('builds a variant tree from the dashboard', function (): void {
    $this->get(route('atrium.keystone.product-models.index'))->assertOk()->assertSee('shirts_by_color_size');

    $this->post(route('atrium.keystone.product-models.store'), ['code' => 'tee', 'family_variant' => 'shirts_by_color_size'])
        ->assertRedirect(route('atrium.keystone.product-models.show', 'tee'));

    $this->get(route('atrium.keystone.product-models.show', 'tee'))
        ->assertOk()
        ->assertSee(__('keystone::keystone.add_sub_model'))
        ->assertSee('data-attribute="name"', false);

    $this->post(route('atrium.keystone.product-models.store'), [
        'code' => 'tee-red',
        'parent' => 'tee',
        'values' => ['color' => [['locale' => '', 'scope' => '', 'data' => 'red']]],
    ])->assertRedirect(route('atrium.keystone.product-models.show', 'tee-red'));

    $this->get(route('atrium.keystone.product-models.show', 'tee-red'))->assertOk()->assertSee(__('keystone::keystone.add_variant'));

    $this->post(route('atrium.keystone.products.store'), [
        'identifier' => 'TEE-RED-M',
        'parent' => 'tee-red',
        'values' => ['size' => [['locale' => '', 'scope' => '', 'data' => 'm']]],
    ])->assertRedirect(route('atrium.keystone.products.show', 'TEE-RED-M'));

    $this->patch(route('atrium.keystone.product-models.update', 'tee'), ['v' => ['name' => 'Classic tee']])->assertSessionHasNoErrors();

    $this->get(route('atrium.keystone.products.show', 'TEE-RED-M'))
        ->assertOk()
        ->assertSee(__('keystone::keystone.inherited_values'))
        ->assertSee('Classic tee')
        ->assertDontSee('name="family"', false);

    $this->delete(route('atrium.keystone.product-models.destroy', 'tee-red'))->assertRedirect(route('atrium.keystone.product-models.show', 'tee'));

    expect(ProductModelModel::query()->count())->toBe(1)->and(ProductModel::query()->count())->toBe(0);
});

it('creates and deletes a family variant from the family page', function (): void {
    $this->get(route('atrium.keystone.families.show', 'shirts'))->assertOk()->assertSee('shirts_by_size');

    $this->post(route('atrium.keystone.family-variants.store'), [
        'family' => 'shirts',
        'code' => 'shirts_by_organic',
        'levels' => [
            ['axes' => ['organic'], 'attributes' => ['ean']],
            ['axes' => [], 'attributes' => []],
        ],
    ])->assertRedirect(route('atrium.keystone.family-variants.show', 'shirts_by_organic'));

    $this->get(route('atrium.keystone.family-variants.show', 'shirts_by_organic'))->assertOk()->assertSee('organic');

    expect(FamilyVariantModel::query()->where('code', 'shirts_by_organic')->value('levels'))->toBe(1);

    $this->delete(route('atrium.keystone.family-variants.destroy', 'shirts_by_organic'))
        ->assertRedirect(route('atrium.keystone.families.show', 'shirts'));
});

it('keeps the products page usable when the search engine refuses a query', function (): void {
    $this->get(route('atrium.keystone.products.index', ['filters' => [['attribute' => 'price', 'operator' => '=', 'value' => '1']]]))
        ->assertOk()
        ->assertSee('cannot filter attribute');
});
