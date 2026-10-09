<?php

declare(strict_types=1);

use RefactorCircus\Showroom\Domains\Attribute\Enums\AttributeType;
use RefactorCircus\Showroom\Domains\Attribute\Models\AttributeModel;
use RefactorCircus\Showroom\Domains\Family\Models\FamilyModel;

beforeEach(function (): void {
    AttributeModel::factory()->create(['code' => 'name']);
    AttributeModel::factory()->select()->create(['code' => 'color']);
    AttributeModel::factory()->select()->create(['code' => 'size']);
    AttributeModel::factory()->type(AttributeType::Textarea)->create(['code' => 'description']);
});

it('creates a family with its attributes and label attribute', function (): void {
    $this->postJson('/showroom/families', [
        'code' => 'shoes',
        'labels' => ['en' => 'Shoes'],
        'attributes' => [
            ['attribute' => 'name', 'is_required' => true],
            ['attribute' => 'size', 'is_required' => true],
            ['attribute' => 'color'],
        ],
        'label_attribute' => 'name',
    ])
        ->assertCreated()
        ->assertJsonPath('data.code', 'shoes')
        ->assertJsonPath('data.label_attribute', 'name')
        ->assertJsonPath('data.attributes.0.attribute', 'name')
        ->assertJsonPath('data.attributes.0.is_required', true)
        ->assertJsonPath('data.attributes.1.attribute', 'size')
        ->assertJsonPath('data.attributes.2.attribute', 'color')
        ->assertJsonPath('data.attributes.2.is_required', false);
});

it('orders attributes by explicit sort order', function (): void {
    $this->postJson('/showroom/families', [
        'code' => 'shoes',
        'attributes' => [
            ['attribute' => 'color', 'sort_order' => 2],
            ['attribute' => 'size', 'sort_order' => 1],
        ],
    ])
        ->assertCreated()
        ->assertJsonPath('data.attributes.0.attribute', 'size')
        ->assertJsonPath('data.attributes.1.attribute', 'color');
});

it('validates the code and the attribute list', function (): void {
    FamilyModel::factory()->create(['code' => 'shoes']);

    $this->postJson('/showroom/families', [
        'code' => 'shoes',
        'attributes' => [['attribute' => 'missing'], ['attribute' => 'color'], ['attribute' => 'color']],
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['code', 'attributes.0.attribute', 'attributes.1.attribute']);
});

it('requires the label attribute to be a text attribute of the family', function (): void {
    $this->postJson('/showroom/families', ['code' => 'shoes', 'attributes' => [['attribute' => 'name']], 'label_attribute' => 'color'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('label_attribute');

    $this->postJson('/showroom/families', ['code' => 'shoes', 'attributes' => [['attribute' => 'description']], 'label_attribute' => 'description'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('label_attribute');

    expect(FamilyModel::query()->count())->toBe(0);
});

it('replaces the whole attribute list on update', function (): void {
    $this->postJson('/showroom/families', ['code' => 'shoes', 'attributes' => [['attribute' => 'name'], ['attribute' => 'color']]])->assertCreated();

    $this->patchJson('/showroom/families/shoes', ['attributes' => [['attribute' => 'size', 'is_required' => true]]])
        ->assertOk()
        ->assertJsonCount(1, 'data.attributes')
        ->assertJsonPath('data.attributes.0.attribute', 'size');
});

it('keeps attributes when the list is not sent', function (): void {
    $this->postJson('/showroom/families', ['code' => 'shoes', 'attributes' => [['attribute' => 'name']]])->assertCreated();

    $this->patchJson('/showroom/families/shoes', ['labels' => ['en' => 'Footwear']])
        ->assertOk()
        ->assertJsonPath('data.labels.en', 'Footwear')
        ->assertJsonCount(1, 'data.attributes');
});

it('refuses to drop the label attribute from the family', function (): void {
    $this->postJson('/showroom/families', ['code' => 'shoes', 'attributes' => [['attribute' => 'name']], 'label_attribute' => 'name'])->assertCreated();

    $this->patchJson('/showroom/families/shoes', ['attributes' => [['attribute' => 'color']]])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('label_attribute');

    // Dropping both together is fine.
    $this->patchJson('/showroom/families/shoes', ['attributes' => [['attribute' => 'color']], 'label_attribute' => null])
        ->assertOk()
        ->assertJsonPath('data.label_attribute', null);
});

it('never changes the code', function (): void {
    FamilyModel::factory()->create(['code' => 'shoes']);

    $this->patchJson('/showroom/families/shoes', ['code' => 'boots'])->assertUnprocessable()->assertJsonValidationErrors('code');
});

it('lists families with attribute counts and filters by attribute', function (): void {
    $this->postJson('/showroom/families', ['code' => 'shoes', 'attributes' => [['attribute' => 'size'], ['attribute' => 'color']]])->assertCreated();
    $this->postJson('/showroom/families', ['code' => 'books', 'attributes' => [['attribute' => 'name']]])->assertCreated();

    $this->getJson('/showroom/families')
        ->assertOk()
        ->assertJsonPath('data.0.code', 'books')
        ->assertJsonPath('data.1.attributes_count', 2);

    $this->getJson('/showroom/families?attribute=size')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.code', 'shoes');
});

it('shows a family by code', function (): void {
    $this->postJson('/showroom/families', ['code' => 'shoes', 'attributes' => [['attribute' => 'size', 'is_required' => true]]])->assertCreated();

    $this->getJson('/showroom/families/shoes')
        ->assertOk()
        ->assertJsonPath('data.attributes.0.attribute', 'size')
        ->assertJsonPath('data.attributes.0.type', 'select');

    $this->getJson('/showroom/families/missing')->assertNotFound();
});

it('deletes a family but not its attributes', function (): void {
    $this->postJson('/showroom/families', ['code' => 'shoes', 'attributes' => [['attribute' => 'size']]])->assertCreated();

    $this->deleteJson('/showroom/families/shoes')->assertNoContent();

    expect(FamilyModel::query()->count())->toBe(0)
        ->and(AttributeModel::query()->where('code', 'size')->exists())->toBeTrue();
});

it('removes a deleted attribute from every family', function (): void {
    $this->postJson('/showroom/families', ['code' => 'shoes', 'attributes' => [['attribute' => 'size'], ['attribute' => 'color']]])->assertCreated();

    $this->deleteJson('/showroom/attributes/size')->assertNoContent();

    $this->getJson('/showroom/families/shoes')->assertJsonCount(1, 'data.attributes');
});

it('refuses to delete an attribute that labels a family', function (): void {
    $this->postJson('/showroom/families', ['code' => 'shoes', 'attributes' => [['attribute' => 'name']], 'label_attribute' => 'name'])->assertCreated();

    $this->deleteJson('/showroom/attributes/name')
        ->assertConflict()
        ->assertJsonPath('message', 'Attribute "name" is the label attribute of family "shoes". Choose another label attribute first.');
});
