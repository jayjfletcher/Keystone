<?php

declare(strict_types=1);

use RefactorCircus\Showroom\Domains\Attribute\Models\AttributeModel;
use RefactorCircus\Showroom\Domains\Family\Models\FamilyVariantModel;
use RefactorCircus\Showroom\Domains\ProductModel\Models\ProductModelModel;
use RefactorCircus\Showroom\Tests\Fixtures\Catalog;

beforeEach(fn () => Catalog::apparel());

it('shows the levels, axes and common attributes', function (): void {
    $this->getJson('/showroom/family-variants/shirts_by_color_size')
        ->assertOk()
        ->assertJsonPath('data.family', 'shirts')
        ->assertJsonPath('data.levels.0.axes', ['color'])
        ->assertJsonPath('data.levels.0.attributes', ['price'])
        ->assertJsonPath('data.levels.1.axes', ['size'])
        ->assertJsonPath('data.levels.1.attributes', ['ean', 'weight'])
        ->assertJsonPath('data.common_attributes', ['name', 'description', 'pack_size', 'rating', 'organic', 'released', 'tags']);
});

it('lists variants by family', function (): void {
    $this->getJson('/showroom/family-variants?family=shirts')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.code', 'shirts_by_color_size');
});

it('refuses axes that cannot be axes', function (): void {
    $this->postJson('/showroom/family-variants', [
        'code' => 'bad',
        'family' => 'shirts',
        'levels' => [['axes' => ['name']]],
    ])->assertUnprocessable()->assertJsonValidationErrors('levels.0.axes.0');

    $this->postJson('/showroom/family-variants', [
        'code' => 'bad',
        'family' => 'shirts',
        'levels' => [['axes' => ['color']], ['axes' => ['color']]],
    ])->assertUnprocessable()->assertJsonValidationErrors('levels.1.axes.0');
});

it('refuses attributes outside the family', function (): void {
    AttributeModel::factory()->select()->create(['code' => 'material']);

    $this->postJson('/showroom/family-variants', [
        'code' => 'bad',
        'family' => 'shirts',
        'levels' => [['axes' => ['material']]],
    ])->assertUnprocessable()->assertJsonValidationErrors('levels.0.axes.0');
});

it('requires unique attributes on the last level', function (): void {
    $this->postJson('/showroom/family-variants', [
        'code' => 'bad',
        'family' => 'shirts',
        'levels' => [['axes' => ['color']]],
    ])->assertUnprocessable()->assertJsonValidationErrors('levels');
});

it('allows at most two levels', function (): void {
    $this->postJson('/showroom/family-variants', [
        'code' => 'bad',
        'family' => 'shirts',
        'levels' => [['axes' => ['color']], ['axes' => ['size']], ['axes' => ['organic'], 'attributes' => ['ean']]],
    ])->assertUnprocessable()->assertJsonValidationErrors('levels');
});

it('changes levels only while no product model uses the variant', function (): void {
    $this->patchJson('/showroom/family-variants/shirts_by_size', [
        'levels' => [['axes' => ['color'], 'attributes' => ['ean']]],
    ])->assertOk()->assertJsonPath('data.levels.0.axes', ['color']);

    $this->postJson('/showroom/product-models', ['code' => 'tee', 'family_variant' => 'shirts_by_size'])->assertCreated();

    $this->patchJson('/showroom/family-variants/shirts_by_size', [
        'levels' => [['axes' => ['size'], 'attributes' => ['ean']]],
    ])->assertUnprocessable()->assertJsonValidationErrors('levels');

    $this->patchJson('/showroom/family-variants/shirts_by_size', ['labels' => ['en' => 'By size']])->assertOk();
});

it('refuses to delete a variant with product models', function (): void {
    ProductModelModel::factory()->create(['family_variant_id' => FamilyVariantModel::query()->where('code', 'shirts_by_size')->value('id')]);

    $this->deleteJson('/showroom/family-variants/shirts_by_size')->assertConflict();
    $this->deleteJson('/showroom/family-variants/shirts_by_color_size')->assertNoContent();
});

it('keeps variant attributes in the family and refuses to delete them', function (): void {
    $this->patchJson('/showroom/families/shirts', ['attributes' => [['attribute' => 'name']]])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('attributes');

    $this->deleteJson('/showroom/attributes/size')
        ->assertConflict()
        ->assertJsonPath('message', fn (string $message): bool => str_contains($message, 'family variant'));
});
