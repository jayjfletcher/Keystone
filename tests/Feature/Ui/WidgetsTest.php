<?php

declare(strict_types=1);

use JayI\Atrium\Domains\Widgets\Data\WidgetDefinition;
use JayI\Atrium\Domains\Widgets\Services\WidgetRegistry;
use JayI\Keystone\Atrium\KeystonePlugin;
use JayI\Keystone\Tests\Fixtures\Catalog;

function renderWidget(string $key): string
{
    $definition = app(WidgetRegistry::class)->get($key);

    expect($definition)->toBeInstanceOf(WidgetDefinition::class);

    /** @var WidgetDefinition $definition */
    /** @var view-string $view */
    $view = $definition->view;

    return view($view, $definition->resolveData())->render();
}

it('offers widgets without placing any', function (): void {
    $keys = array_map(
        fn (WidgetDefinition $definition): string => $definition->key,
        app(KeystonePlugin::class)->widgets(),
    );

    expect($keys)->toBe(['keystone.product-status', 'keystone.completeness', 'keystone.review-queue', 'keystone.recent-changes'])
        ->and(app(WidgetRegistry::class)->all())->toHaveKeys($keys);
});

it('renders empty widgets on a new catalog', function (): void {
    expect(renderWidget('keystone.completeness'))->toContain(__('keystone::keystone.no_completeness_yet'))
        ->and(renderWidget('keystone.review-queue'))->toContain(__('keystone::keystone.nothing_in_review'))
        ->and(renderWidget('keystone.recent-changes'))->toContain(__('keystone::keystone.no_changes'));
});

it('summarises the catalog', function (): void {
    Catalog::apparel();

    $attributes = ['name', 'description', 'ean', 'weight', 'price', 'pack_size', 'rating', 'organic', 'released', 'color', 'size', 'tags'];

    $this->patchJson('/keystone/families/shirts', ['attributes' => array_map(
        fn (string $code): array => ['attribute' => $code, 'is_required' => $code === 'name'],
        $attributes,
    )])->assertOk();

    foreach (['TEE-1' => 'Tee', 'TEE-2' => null] as $identifier => $name) {
        $this->postJson('/keystone/products', [
            'identifier' => $identifier,
            'family' => 'shirts',
            'values' => $name === null ? [] : ['name' => Catalog::value($name)],
        ])->assertCreated();
    }

    $this->postJson('/keystone/products/TEE-1/transitions', ['transition' => 'submit'])->assertOk();

    $status = renderWidget('keystone.product-status');

    expect($status)->toMatch('/data-status="draft".*?>\s*1\s*</s')
        ->and($status)->toMatch('/data-status="in_review".*?>\s*1\s*</s')
        ->and($status)->toMatch('/data-status="published".*?>\s*0\s*</s');

    // TEE-1 has every required value, TEE-2 none: 1 of 2 complete.
    expect(renderWidget('keystone.completeness'))
        ->toContain('data-slot="ecommerce-en"')
        ->toContain('50%')
        ->toContain('1 of 2 complete');

    expect(renderWidget('keystone.review-queue'))
        ->toContain('data-product="TEE-1"')
        ->not->toContain('data-product="TEE-2"');

    expect(renderWidget('keystone.recent-changes'))
        ->toContain('TEE-1')
        ->toContain('submitted');
});
