<?php

declare(strict_types=1);

use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeGroupModel;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeModel;

it('creates an attribute group', function (): void {
    $this->postJson('/keystone/attribute-groups', [
        'code' => 'marketing',
        'labels' => ['en' => 'Marketing', 'fr' => 'Marketing'],
        'sort_order' => 2,
    ])
        ->assertCreated()
        ->assertJsonPath('data.code', 'marketing')
        ->assertJsonPath('data.labels.en', 'Marketing')
        ->assertJsonPath('data.sort_order', 2);

    expect(AttributeGroupModel::query()->where('code', 'marketing')->exists())->toBeTrue();
});

it('rejects an invalid or duplicate code', function (string $code): void {
    AttributeGroupModel::factory()->create(['code' => 'taken']);

    $this->postJson('/keystone/attribute-groups', ['code' => $code])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');
})->with(['taken', 'Has Spaces', '1starts_with_digit', '']);

it('lists groups in display order with attribute counts', function (): void {
    $second = AttributeGroupModel::factory()->create(['code' => 'technical', 'sort_order' => 2]);
    AttributeGroupModel::factory()->create(['code' => 'marketing', 'sort_order' => 1]);
    AttributeModel::factory()->for($second, 'group')->create();

    $this->getJson('/keystone/attribute-groups')
        ->assertOk()
        ->assertJsonPath('data.0.code', 'marketing')
        ->assertJsonPath('data.1.code', 'technical')
        ->assertJsonPath('data.1.attributes_count', 1);
});

it('searches groups by code or label', function (): void {
    AttributeGroupModel::factory()->create(['code' => 'marketing', 'labels' => ['en' => 'Sales copy']]);
    AttributeGroupModel::factory()->create(['code' => 'technical', 'labels' => ['en' => 'Specs']]);

    $this->getJson('/keystone/attribute-groups?search=sales')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.code', 'marketing');
});

it('shows a group by code with its attributes', function (): void {
    $group = AttributeGroupModel::factory()->create(['code' => 'technical']);
    AttributeModel::factory()->for($group, 'group')->create(['code' => 'weight']);

    $this->getJson('/keystone/attribute-groups/technical')
        ->assertOk()
        ->assertJsonPath('data.code', 'technical')
        ->assertJsonPath('data.attributes.0.code', 'weight');
});

it('returns not found for an unknown group', function (): void {
    $this->getJson('/keystone/attribute-groups/nope')->assertNotFound();
});

it('updates labels and sort order but never the code', function (): void {
    AttributeGroupModel::factory()->create(['code' => 'technical', 'labels' => ['en' => 'Tech']]);

    $this->patchJson('/keystone/attribute-groups/technical', ['labels' => ['en' => 'Technical'], 'sort_order' => 5])
        ->assertOk()
        ->assertJsonPath('data.labels.en', 'Technical')
        ->assertJsonPath('data.sort_order', 5);

    $this->patchJson('/keystone/attribute-groups/technical', ['code' => 'renamed'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');
});

it('deletes an empty group', function (): void {
    AttributeGroupModel::factory()->create(['code' => 'empty']);

    $this->deleteJson('/keystone/attribute-groups/empty')->assertNoContent();

    expect(AttributeGroupModel::query()->where('code', 'empty')->exists())->toBeFalse();
});

it('refuses to delete a group that still holds attributes', function (): void {
    $group = AttributeGroupModel::factory()->create(['code' => 'technical']);
    AttributeModel::factory()->for($group, 'group')->create();

    $this->deleteJson('/keystone/attribute-groups/technical')
        ->assertConflict()
        ->assertJsonPath('message', fn (string $message): bool => str_contains($message, 'Move them to another group first'));

    expect($group->fresh())->not->toBeNull();
});
