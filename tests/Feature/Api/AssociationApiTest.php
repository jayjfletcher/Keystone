<?php

declare(strict_types=1);

use RefactorCircus\Showroom\Domains\Association\Models\AssociationModel;
use RefactorCircus\Showroom\Tests\Fixtures\Catalog;

beforeEach(function (): void {
    Catalog::apparel();

    $this->postJson('/showroom/association-types', ['code' => 'cross_sell', 'labels' => ['en' => 'Cross-sell']])->assertCreated();
    $this->postJson('/showroom/association-types', ['code' => 'compatible', 'is_two_way' => true])->assertCreated();
    $this->postJson('/showroom/association-types', ['code' => 'bundle', 'is_quantified' => true])->assertCreated();

    foreach (['TEE', 'CAP', 'SOCKS', 'BAG'] as $identifier) {
        $this->postJson('/showroom/products', ['identifier' => $identifier])->assertCreated();
    }

    $this->postJson('/showroom/product-models', ['code' => 'polo', 'family_variant' => 'shirts_by_size'])->assertCreated();
});

it('describes association types', function (): void {
    $this->getJson('/showroom/association-types/bundle')
        ->assertOk()
        ->assertJsonPath('data.is_quantified', true)
        ->assertJsonPath('data.is_two_way', false);

    $this->postJson('/showroom/association-types', ['code' => 'both', 'is_two_way' => true, 'is_quantified' => true])
        ->assertUnprocessable()->assertJsonValidationErrors('is_quantified');

    $this->patchJson('/showroom/association-types/bundle', ['is_quantified' => false])
        ->assertUnprocessable()->assertJsonValidationErrors('is_quantified');
});

it('associates products and product models', function (): void {
    $this->patchJson('/showroom/products/TEE', [
        'associations' => ['cross_sell' => ['products' => ['CAP', 'SOCKS'], 'product_models' => ['polo']]],
    ])
        ->assertOk()
        ->assertJsonPath('data.associations.cross_sell.products', ['CAP', 'SOCKS'])
        ->assertJsonPath('data.associations.cross_sell.product_models', ['polo']);

    // A type sent replaces its targets; types not sent stay.
    $this->patchJson('/showroom/products/TEE', ['associations' => ['cross_sell' => ['products' => ['BAG']]]])
        ->assertOk()
        ->assertJsonPath('data.associations.cross_sell.products', ['BAG'])
        ->assertJsonPath('data.associations.cross_sell.product_models', []);

    $this->patchJson('/showroom/products/TEE', ['enabled' => false])->assertOk()->assertJsonPath('data.associations.cross_sell.products', ['BAG']);

    $this->patchJson('/showroom/products/TEE', ['associations' => ['cross_sell' => ['products' => []]]])
        ->assertOk()->assertJsonPath('data.associations', []);
});

it('mirrors two-way associations on both sides', function (): void {
    $this->patchJson('/showroom/products/TEE', ['associations' => ['compatible' => ['products' => ['CAP', 'SOCKS']]]])->assertOk();

    $this->getJson('/showroom/products/CAP')->assertJsonPath('data.associations.compatible.products', ['TEE']);
    $this->getJson('/showroom/products/SOCKS')->assertJsonPath('data.associations.compatible.products', ['TEE']);

    $this->patchJson('/showroom/products/TEE', ['associations' => ['compatible' => ['products' => ['CAP']]]])->assertOk();

    $this->getJson('/showroom/products/SOCKS')->assertJsonPath('data.associations', []);
    $this->getJson('/showroom/products/CAP')->assertJsonPath('data.associations.compatible.products', ['TEE']);
});

it('holds bundle components with quantities', function (): void {
    $this->postJson('/showroom/products', [
        'identifier' => 'GIFT-SET',
        'quantified_associations' => ['bundle' => ['products' => [
            ['identifier' => 'TEE', 'quantity' => 2],
            ['identifier' => 'CAP', 'quantity' => 1],
        ]]],
    ])
        ->assertCreated()
        ->assertJsonPath('data.quantified_associations.bundle.products', [
            ['identifier' => 'TEE', 'quantity' => 2],
            ['identifier' => 'CAP', 'quantity' => 1],
        ]);

    $this->patchJson('/showroom/products/GIFT-SET', ['quantified_associations' => ['bundle' => ['products' => [['identifier' => 'TEE', 'quantity' => 0]]]]])
        ->assertUnprocessable()->assertJsonValidationErrors('quantified_associations.bundle.products.0.quantity');
});

it('keeps plain and quantified types apart', function (): void {
    $this->patchJson('/showroom/products/TEE', ['associations' => ['bundle' => ['products' => ['CAP']]]])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['associations.bundle' => 'Association type "bundle" is quantified; send it under quantified_associations with quantities.']);

    $this->patchJson('/showroom/products/TEE', ['quantified_associations' => ['cross_sell' => ['products' => [['identifier' => 'CAP', 'quantity' => 1]]]]])
        ->assertUnprocessable()->assertJsonValidationErrors('quantified_associations.cross_sell');
});

it('validates targets', function (): void {
    $this->patchJson('/showroom/products/TEE', ['associations' => ['upsell' => ['products' => ['CAP']]]])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['associations.upsell' => 'Association type "upsell" does not exist.']);

    $this->patchJson('/showroom/products/TEE', ['associations' => ['cross_sell' => ['products' => ['NOPE', 'TEE'], 'product_models' => ['nope']]]])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'associations.cross_sell.products.0' => 'No product "NOPE" exists.',
            'associations.cross_sell.products.1' => 'A record cannot be associated with itself.',
            'associations.cross_sell.product_models.0' => 'No product model "nope" exists.',
        ]);
});

it('lets variants inherit their models\' associations', function (): void {
    $this->patchJson('/showroom/product-models/polo', [
        'associations' => ['cross_sell' => ['products' => ['CAP']]],
        'quantified_associations' => ['bundle' => ['products' => [['identifier' => 'SOCKS', 'quantity' => 2]]]],
    ])->assertOk()->assertJsonPath('data.associations.cross_sell.products', ['CAP']);

    $this->postJson('/showroom/products', [
        'identifier' => 'POLO-M',
        'parent' => 'polo',
        'values' => ['size' => Catalog::value('m')],
        'associations' => ['cross_sell' => ['products' => ['BAG']]],
        'quantified_associations' => ['bundle' => ['products' => [['identifier' => 'SOCKS', 'quantity' => 3]]]],
    ])
        ->assertCreated()
        ->assertJsonPath('data.associations.cross_sell.products', ['CAP', 'BAG'])
        // The variant's own quantity wins over the model's.
        ->assertJsonPath('data.quantified_associations.bundle.products', [['identifier' => 'SOCKS', 'quantity' => 3]]);
});

it('removes associations from and to a deleted record', function (): void {
    $this->patchJson('/showroom/products/TEE', ['associations' => ['compatible' => ['products' => ['CAP']], 'cross_sell' => ['products' => ['SOCKS']]]])->assertOk();

    $this->deleteJson('/showroom/association-types/compatible')
        ->assertConflict()
        ->assertJsonPath('message', 'Association type "compatible" is used by 2 association(s). Remove them first.');

    $this->deleteJson('/showroom/products/CAP')->assertNoContent();
    $this->deleteJson('/showroom/product-models/polo')->assertNoContent();

    $this->getJson('/showroom/products/TEE')
        ->assertJsonPath('data.associations', ['cross_sell' => ['products' => ['SOCKS'], 'product_models' => []]]);

    expect(AssociationModel::query()->count())->toBe(1);
});
