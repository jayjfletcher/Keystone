<?php

declare(strict_types=1);

use JayI\Keystone\Domains\Attribute\Enums\AttributeType;
use JayI\Keystone\Domains\Attribute\Models\AttributeGroupModel;
use JayI\Keystone\Domains\Attribute\Models\AttributeModel;
use JayI\Keystone\Domains\Attribute\Models\AttributeOptionModel;

it('creates an attribute in a group', function (): void {
    AttributeGroupModel::factory()->create(['code' => 'technical']);

    $this->postJson('/keystone/attributes', [
        'code' => 'weight',
        'type' => 'metric',
        'group' => 'technical',
        'labels' => ['en' => 'Weight'],
        'is_localizable' => false,
        'is_scopable' => true,
        'settings' => ['metric_family' => 'weight', 'default_unit' => 'kilogram'],
    ])
        ->assertCreated()
        ->assertJsonPath('data.code', 'weight')
        ->assertJsonPath('data.type', 'metric')
        ->assertJsonPath('data.group', 'technical')
        ->assertJsonPath('data.is_scopable', true)
        ->assertJsonPath('data.settings.default_unit', 'kilogram');
});

it('creates an attribute of every type', function (AttributeType $type): void {
    $settings = $type === AttributeType::Metric ? ['metric_family' => 'length', 'default_unit' => 'meter'] : [];

    $this->postJson('/keystone/attributes', ['code' => 'attr_'.$type->value, 'type' => $type->value, 'settings' => $settings])
        ->assertCreated()
        ->assertJsonPath('data.type', $type->value);
})->with(AttributeType::cases());

it('validates the type, code and group', function (): void {
    AttributeModel::factory()->create(['code' => 'color']);

    $this->postJson('/keystone/attributes', ['code' => 'color', 'type' => 'colour', 'group' => 'missing'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['code', 'type', 'group']);
});

it('validates settings against the type', function (): void {
    $this->postJson('/keystone/attributes', ['code' => 'weight', 'type' => 'metric'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['settings.metric_family', 'settings.default_unit']);

    $this->postJson('/keystone/attributes', ['code' => 'price', 'type' => 'price', 'settings' => ['decimals' => 9]])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('settings.decimals');
});

it('drops settings the type does not understand', function (): void {
    $this->postJson('/keystone/attributes', ['code' => 'name', 'type' => 'text', 'settings' => ['max_length' => 80, 'currencies' => ['USD']]])
        ->assertCreated()
        ->assertJsonPath('data.settings', ['max_length' => 80]);
});

it('refuses uniqueness on types that cannot be unique', function (): void {
    $this->postJson('/keystone/attributes', ['code' => 'active', 'type' => 'boolean', 'is_unique' => true])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('is_unique');

    $this->postJson('/keystone/attributes', ['code' => 'sku', 'type' => 'text', 'is_unique' => true])
        ->assertCreated()
        ->assertJsonPath('data.is_unique', true);
});

it('lists attributes filtered by type, group and search', function (): void {
    $group = AttributeGroupModel::factory()->create(['code' => 'marketing']);
    AttributeModel::factory()->for($group, 'group')->create(['code' => 'headline', 'labels' => ['en' => 'Headline']]);
    AttributeModel::factory()->select()->create(['code' => 'color', 'labels' => ['en' => 'Colour']]);
    AttributeModel::factory()->type(AttributeType::Number)->create(['code' => 'pack_size']);

    $this->getJson('/keystone/attributes?type=select')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.code', 'color');
    $this->getJson('/keystone/attributes?group=marketing')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.code', 'headline');
    $this->getJson('/keystone/attributes?search=colour')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.code', 'color');
    $this->getJson('/keystone/attributes?type=nope')->assertUnprocessable();
});

it('paginates with a cursor', function (): void {
    AttributeModel::factory()->count(3)->sequence(['code' => 'a1'], ['code' => 'a2'], ['code' => 'a3'])->create();

    $first = $this->getJson('/keystone/attributes?per_page=2')->assertOk()->assertJsonCount(2, 'data');

    $cursor = $first->json('meta.next_cursor');

    $this->getJson('/keystone/attributes?per_page=2&cursor='.$cursor)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.code', 'a3');
});

it('caps the page size', function (): void {
    $this->getJson('/keystone/attributes?per_page=1000')->assertUnprocessable()->assertJsonValidationErrors('per_page');
});

it('shows an attribute with its options', function (): void {
    $attribute = AttributeModel::factory()->select()->create(['code' => 'color']);
    AttributeOptionModel::factory()->for($attribute, 'attribute')->create(['code' => 'red']);

    $this->getJson('/keystone/attributes/color')
        ->assertOk()
        ->assertJsonPath('data.code', 'color')
        ->assertJsonPath('data.options.0.code', 'red');
});

it('never changes the code or type', function (): void {
    AttributeModel::factory()->create(['code' => 'name']);

    $this->patchJson('/keystone/attributes/name', ['code' => 'title', 'type' => 'number'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['code', 'type']);
});

it('updates the group, labels, flags and settings', function (): void {
    AttributeGroupModel::factory()->create(['code' => 'marketing']);
    AttributeModel::factory()->create(['code' => 'name', 'settings' => ['max_length' => 10]]);

    $this->patchJson('/keystone/attributes/name', [
        'group' => 'marketing',
        'labels' => ['en' => 'Name'],
        'is_localizable' => true,
        'settings' => ['regex' => '/^[A-Z]/'],
    ])
        ->assertOk()
        ->assertJsonPath('data.group', 'marketing')
        ->assertJsonPath('data.is_localizable', true)
        ->assertJsonPath('data.settings', ['regex' => '/^[A-Z]/']);

    $this->patchJson('/keystone/attributes/name', ['group' => null])
        ->assertOk()
        ->assertJsonPath('data.group', null);
});

it('deletes an attribute and its options', function (): void {
    $attribute = AttributeModel::factory()->select()->create(['code' => 'color']);
    AttributeOptionModel::factory()->for($attribute, 'attribute')->create();

    $this->deleteJson('/keystone/attributes/color')->assertNoContent();

    expect(AttributeModel::query()->count())->toBe(0)
        ->and(AttributeOptionModel::query()->count())->toBe(0);
});
