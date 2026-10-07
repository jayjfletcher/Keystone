<?php

declare(strict_types=1);

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use JayI\Impex\Domains\Run\Models\RunModel;
use JayI\Keystone\Domains\Product\Models\ProductModel;
use Workbench\Database\Factories\UserFactory;

beforeEach(function (): void {
    ValidateCsrfToken::except(['*']);

    // Atrium denies access outside local until a gate is defined.
    app()->detectEnvironment(fn (): string => 'local');

    config()->set('keystone.media.disk', 'assets');
    Storage::fake('assets');
});

it('imports and exports from the dashboard', function (): void {
    $this->get(route('atrium.keystone.transfers.index'))->assertOk()->assertSee(__('keystone::keystone.no_runs'));

    $file = UploadedFile::fake()->createWithContent('products.jsonl', json_encode(['identifier' => 'UI-1'])."\n");

    $this->post(route('atrium.keystone.transfers.import'), ['file' => $file, 'mode' => 'upsert'])->assertSessionHasNoErrors();

    expect(ProductModel::query()->where('identifier', 'UI-1')->exists())->toBeTrue();

    $this->post(route('atrium.keystone.transfers.export'), ['format' => 'csv', 'family' => '', 'published' => '0'])->assertSessionHasNoErrors();

    $this->get(route('atrium.keystone.transfers.index'))
        ->assertOk()
        ->assertSee('data-run="keystone:import-products"', false)
        ->assertSee('data-run="keystone:export-products"', false)
        ->assertSee('1 of 1 imported, 0 failed');
});

it('explains when Impex is off', function (): void {
    config()->set('keystone.impex.enabled', false);

    $this->get(route('atrium.keystone.transfers.index'))->assertOk()->assertSee(__('keystone::keystone.impex_missing'));
});

it('links a transfer to its Impex run only when Impex would open it for the viewer', function (): void {
    config()->set('impex.authorization', true);

    $this->actingAs(UserFactory::new()->create());

    $file = UploadedFile::fake()->createWithContent('products.jsonl', json_encode(['identifier' => 'UI-2'])."\n");
    $this->post(route('atrium.keystone.transfers.import'), ['file' => $file, 'mode' => 'upsert'])->assertSessionHasNoErrors();

    $run = RunModel::query()->latest()->firstOrFail();
    $link = 'href="'.route('atrium.impex.runs.show', $run).'"';

    // Someone else's run: Impex would refuse it, so no link.
    $this->actingAs(UserFactory::new()->create())
        ->get(route('atrium.keystone.transfers.index'))
        ->assertOk()
        ->assertDontSee($link, false);

    // An Impex dashboard operator may open every run.
    config()->set('impex.atrium.show_all', true);

    $this->get(route('atrium.keystone.transfers.index'))->assertOk()->assertSee($link, false);
});
