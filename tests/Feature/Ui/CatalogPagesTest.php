<?php

declare(strict_types=1);

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeGroupModel;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeModel;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeOptionModel;
use RefactorCircus\Keystone\Domains\Family\Models\FamilyModel;

beforeEach(function (): void {
    ValidateCsrfToken::except(['*']);

    // Atrium denies access outside local until a gate is defined.
    app()->detectEnvironment(fn (): string => 'local');
});

it('lists attributes', function (): void {
    AttributeModel::factory()->select()->create(['code' => 'color', 'labels' => ['en' => 'Colour']]);

    $this->get(route('atrium.keystone.attributes.index'))
        ->assertOk()
        ->assertSee('color')
        ->assertSee('Colour')
        ->assertSee('select');
});

it('shows an empty state when nothing matches', function (): void {
    $this->get(route('atrium.keystone.attributes.index', ['search' => 'nothing']))
        ->assertOk()
        ->assertSee(__('keystone::keystone.no_attributes'));
});

it('offers every attribute type', function (): void {
    $response = $this->get(route('atrium.keystone.attributes.create'))->assertOk();

    // Driven from the enum, so the form cannot offer a type the API would reject.
    $response->assertSee('multiselect')->assertSee('metric');
});

it('creates an attribute from the form', function (): void {
    AttributeGroupModel::factory()->create(['code' => 'technical']);

    $this->post(route('atrium.keystone.attributes.store'), [
        'code' => 'weight',
        'type' => 'metric',
        'group' => 'technical',
        'labels' => ['en' => 'Weight'],
        'is_unique' => '0',
        'is_localizable' => '0',
        'is_scopable' => '1',
        'settings' => '{"metric_family": "weight", "default_unit": "kilogram"}',
        'sort_order' => '0',
    ])->assertRedirect(route('atrium.keystone.attributes.show', 'weight'));

    $attribute = AttributeModel::query()->where('code', 'weight')->firstOrFail();

    expect($attribute->group?->code)->toBe('technical')
        ->and($attribute->is_scopable)->toBeTrue()
        ->and($attribute->settings)->toBe(['metric_family' => 'weight', 'default_unit' => 'kilogram']);
});

it('rejects settings that are not JSON', function (): void {
    $this->from(route('atrium.keystone.attributes.create'))
        ->post(route('atrium.keystone.attributes.store'), ['code' => 'name', 'type' => 'text', 'settings' => 'nope'])
        ->assertRedirect(route('atrium.keystone.attributes.create'))
        ->assertSessionHasErrors('settings');
});

it('keeps other locales when the label is edited', function (): void {
    AttributeModel::factory()->create(['code' => 'name', 'labels' => ['en' => 'Name', 'fr' => 'Nom']]);

    $this->patch(route('atrium.keystone.attributes.update', 'name'), [
        'labels' => ['en' => 'Title'],
        'is_unique' => '0',
        'is_localizable' => '1',
        'is_scopable' => '0',
        'settings' => '',
    ])->assertRedirect();

    $attribute = AttributeModel::query()->where('code', 'name')->firstOrFail();

    expect($attribute->labels)->toBe(['en' => 'Title', 'fr' => 'Nom'])
        ->and($attribute->is_localizable)->toBeTrue();
});

it('shows an attribute and manages its options', function (): void {
    $attribute = AttributeModel::factory()->select()->create(['code' => 'color']);
    AttributeOptionModel::factory()->for($attribute, 'attribute')->create(['code' => 'blue']);

    $this->get(route('atrium.keystone.attributes.show', $attribute))
        ->assertOk()
        ->assertSee('blue')
        ->assertSee(__('keystone::keystone.add_option'));

    $this->post(route('atrium.keystone.attributes.options.store', $attribute), ['code' => 'red'])->assertRedirect();
    $this->delete(route('atrium.keystone.attributes.options.destroy', [$attribute, 'blue']))->assertRedirect();

    expect($attribute->options()->pluck('code')->all())->toBe(['red']);
});

it('offers no options on attributes that take none', function (): void {
    AttributeModel::factory()->create(['code' => 'name']);

    $this->get(route('atrium.keystone.attributes.show', 'name'))
        ->assertOk()
        ->assertDontSee(__('keystone::keystone.add_option'));
});

it('deletes an attribute', function (): void {
    AttributeModel::factory()->create(['code' => 'name']);

    $this->delete(route('atrium.keystone.attributes.destroy', 'name'))
        ->assertRedirect(route('atrium.keystone.attributes.index'));

    expect(AttributeModel::query()->count())->toBe(0);
});

it('manages attribute groups', function (): void {
    $this->post(route('atrium.keystone.attribute-groups.store'), ['code' => 'technical', 'labels' => ['en' => 'Technical']])
        ->assertRedirect(route('atrium.keystone.attribute-groups.show', 'technical'));

    $this->get(route('atrium.keystone.attribute-groups.index'))->assertOk()->assertSee('Technical');
    $this->get(route('atrium.keystone.attribute-groups.show', 'technical'))->assertOk()->assertSee(__('keystone::keystone.no_group_attributes'));

    $this->patch(route('atrium.keystone.attribute-groups.update', 'technical'), ['labels' => ['en' => 'Specs'], 'sort_order' => '1'])
        ->assertRedirect();

    expect(AttributeGroupModel::query()->firstOrFail()->label())->toBe('Specs');

    $this->delete(route('atrium.keystone.attribute-groups.destroy', 'technical'))
        ->assertRedirect(route('atrium.keystone.attribute-groups.index'));
});

it('explains why a group with attributes cannot be deleted', function (): void {
    $group = AttributeGroupModel::factory()->create(['code' => 'technical']);
    AttributeModel::factory()->for($group, 'group')->create();

    $this->from(route('atrium.keystone.attribute-groups.show', $group))
        ->delete(route('atrium.keystone.attribute-groups.destroy', $group))
        ->assertRedirect(route('atrium.keystone.attribute-groups.show', $group))
        ->assertSessionHasErrors('group');
});

it('creates a family and manages its attributes', function (): void {
    AttributeModel::factory()->create(['code' => 'name']);
    AttributeModel::factory()->select()->create(['code' => 'size']);

    $this->post(route('atrium.keystone.families.store'), ['code' => 'shoes', 'labels' => ['en' => 'Shoes']])
        ->assertRedirect(route('atrium.keystone.families.show', 'shoes'));

    $this->get(route('atrium.keystone.families.index'))->assertOk()->assertSee('Shoes');
    $this->get(route('atrium.keystone.families.show', 'shoes'))->assertOk()->assertSee(__('keystone::keystone.no_family_attributes'));

    // Add two attributes, one at a time, the way the form does.
    $this->patch(route('atrium.keystone.families.update', 'shoes'), ['add_attribute' => 'name', 'add_required' => '1'])->assertRedirect();
    $this->patch(route('atrium.keystone.families.update', 'shoes'), [
        'attributes' => [['attribute' => 'name', 'is_required' => '1', 'sort_order' => '0']],
        'add_attribute' => 'size',
        'label_attribute' => 'name',
    ])->assertRedirect();

    $family = FamilyModel::query()->where('code', 'shoes')->firstOrFail();

    expect($family->familyAttributes->pluck('code')->all())->toBe(['name', 'size'])
        ->and($family->labelAttribute?->code)->toBe('name');

    $this->get(route('atrium.keystone.families.show', 'shoes'))->assertOk()->assertSee('size');

    // Remove one row with its remove box.
    $this->patch(route('atrium.keystone.families.update', 'shoes'), [
        'attributes' => [
            ['attribute' => 'name', 'is_required' => '1', 'sort_order' => '0'],
            ['attribute' => 'size', 'is_required' => '0', 'sort_order' => '1', 'remove' => '1'],
        ],
        'label_attribute' => 'name',
    ])->assertRedirect();

    expect($family->familyAttributes()->pluck('code')->all())->toBe(['name']);

    $this->delete(route('atrium.keystone.families.destroy', 'shoes'))->assertRedirect(route('atrium.keystone.families.index'));
});

it('explains why a family\'s label attribute cannot be deleted', function (): void {
    $name = AttributeModel::factory()->create(['code' => 'name']);
    $family = FamilyModel::factory()->create(['code' => 'shoes', 'label_attribute_id' => $name->id]);
    $family->familyAttributes()->attach($name);

    $this->from(route('atrium.keystone.attributes.show', 'name'))
        ->delete(route('atrium.keystone.attributes.destroy', 'name'))
        ->assertRedirect(route('atrium.keystone.attributes.show', 'name'))
        ->assertSessionHasErrors('attribute');
});
