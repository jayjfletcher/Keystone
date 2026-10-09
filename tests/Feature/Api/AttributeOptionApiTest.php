<?php

declare(strict_types=1);

use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeModel;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeOptionModel;

it('adds options to a select attribute', function (): void {
    AttributeModel::factory()->select()->create(['code' => 'color']);

    $this->postJson('/keystone/attributes/color/options', ['code' => 'red', 'labels' => ['en' => 'Red']])
        ->assertCreated()
        ->assertJsonPath('data.code', 'red')
        ->assertJsonPath('data.labels.en', 'Red');
});

it('allows option codes that start with a digit', function (): void {
    AttributeModel::factory()->select()->create(['code' => 'size']);

    $this->postJson('/keystone/attributes/size/options', ['code' => '10'])->assertCreated();
});

it('refuses options on attributes that take none', function (): void {
    AttributeModel::factory()->create(['code' => 'name']);

    $this->postJson('/keystone/attributes/name/options', ['code' => 'red'])
        ->assertConflict()
        ->assertJsonPath('message', fn (string $message): bool => str_contains($message, 'takes no options'));

    $this->getJson('/keystone/attributes/name/options')->assertConflict();
});

it('keeps option codes unique within an attribute only', function (): void {
    $color = AttributeModel::factory()->select()->create(['code' => 'color']);
    AttributeModel::factory()->select()->create(['code' => 'finish']);
    AttributeOptionModel::factory()->for($color, 'attribute')->create(['code' => 'red']);

    $this->postJson('/keystone/attributes/color/options', ['code' => 'red'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');

    $this->postJson('/keystone/attributes/finish/options', ['code' => 'red'])->assertCreated();
});

it('lists options in display order', function (): void {
    $attribute = AttributeModel::factory()->select()->create(['code' => 'color']);
    AttributeOptionModel::factory()->for($attribute, 'attribute')->create(['code' => 'red', 'sort_order' => 2]);
    AttributeOptionModel::factory()->for($attribute, 'attribute')->create(['code' => 'blue', 'sort_order' => 1]);

    $this->getJson('/keystone/attributes/color/options')
        ->assertOk()
        ->assertJsonPath('data.0.code', 'blue')
        ->assertJsonPath('data.1.code', 'red');
});

it('updates an option but never its code', function (): void {
    $attribute = AttributeModel::factory()->select()->create(['code' => 'color']);
    AttributeOptionModel::factory()->for($attribute, 'attribute')->create(['code' => 'red']);

    $this->patchJson('/keystone/attributes/color/options/red', ['labels' => ['en' => 'Crimson'], 'sort_order' => 3])
        ->assertOk()
        ->assertJsonPath('data.labels.en', 'Crimson')
        ->assertJsonPath('data.sort_order', 3);

    $this->patchJson('/keystone/attributes/color/options/red', ['code' => 'crimson'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');
});

it('scopes an option to its attribute', function (): void {
    AttributeModel::factory()->select()->create(['code' => 'color']);
    $finish = AttributeModel::factory()->select()->create(['code' => 'finish']);
    AttributeOptionModel::factory()->for($finish, 'attribute')->create(['code' => 'matte']);

    $this->deleteJson('/keystone/attributes/color/options/matte')->assertNotFound();
});

it('deletes an option', function (): void {
    $attribute = AttributeModel::factory()->select()->create(['code' => 'color']);
    AttributeOptionModel::factory()->for($attribute, 'attribute')->create(['code' => 'red']);

    $this->deleteJson('/keystone/attributes/color/options/red')->assertNoContent();

    expect($attribute->options()->count())->toBe(0);
});
