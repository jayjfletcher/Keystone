<?php

declare(strict_types=1);

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use JayI\Keystone\Domains\Asset\Models\AssetModel;
use JayI\Keystone\Domains\Product\Models\ProductModel;

beforeEach(function (): void {
    ValidateCsrfToken::except(['*']);

    // Atrium denies access outside local until a gate is defined.
    app()->detectEnvironment(fn (): string => 'local');

    config()->set('keystone.media.disk', 'assets');
    Storage::fake('assets');
});

it('uploads, browses and edits assets', function (): void {
    $this->get(route('atrium.keystone.assets.index'))->assertOk()->assertSee(__('keystone::keystone.no_assets'));

    $this->post(route('atrium.keystone.assets.store'), ['code' => 'hero', 'file' => UploadedFile::fake()->image('hero.jpg'), 'labels' => ['en' => 'Hero shot']])
        ->assertRedirect(route('atrium.keystone.assets.show', 'hero'));

    $this->get(route('atrium.keystone.assets.index'))->assertOk()->assertSee('data-asset="hero"', false);
    $this->get(route('atrium.keystone.assets.show', 'hero'))->assertOk()->assertSee('Hero shot')->assertSee(__('keystone::keystone.no_links'));

    $this->patch(route('atrium.keystone.assets.update', 'hero'), ['labels' => ['en' => 'Hero'], 'file' => UploadedFile::fake()->image('hero2.png')])
        ->assertSessionHasNoErrors();

    expect(AssetModel::query()->firstOrFail()->filename)->toBe('hero2.png');

    $this->delete(route('atrium.keystone.assets.destroy', 'hero'))->assertRedirect(route('atrium.keystone.assets.index'));
});

it('links and unlinks an asset from its page', function (): void {
    ProductModel::factory()->create(['identifier' => 'TEE-1']);
    $this->post(route('atrium.keystone.assets.store'), ['code' => 'hero', 'file' => UploadedFile::fake()->image('hero.jpg')]);

    $this->post(route('atrium.keystone.assets.attach', 'hero'), ['type' => 'product', 'target' => 'TEE-1', 'role' => 'image'])->assertSessionHasNoErrors();

    $this->get(route('atrium.keystone.assets.show', 'hero'))->assertOk()->assertSee('TEE-1');
    $this->get(route('atrium.keystone.products.show', 'TEE-1'))->assertOk()->assertSee('data-asset="hero"', false);

    $this->delete(route('atrium.keystone.assets.detach', 'hero'), ['type' => 'product', 'target' => 'TEE-1', 'role' => 'image'])->assertSessionHasNoErrors();

    expect(ProductModel::query()->firstOrFail()->assets)->toHaveCount(0);

    $this->post(route('atrium.keystone.assets.attach', 'hero'), ['type' => 'product', 'target' => 'NOPE'])->assertSessionHasErrors('target');
});

it('uploads and links a file from a product page in one step', function (): void {
    ProductModel::factory()->create(['identifier' => 'TEE-1']);

    $this->from(route('atrium.keystone.products.show', 'TEE-1'))
        ->post(route('atrium.keystone.assets.upload'), ['type' => 'product', 'target' => 'TEE-1', 'role' => 'image', 'file' => UploadedFile::fake()->image('front.jpg')])
        ->assertRedirect(route('atrium.keystone.products.show', 'TEE-1'))
        ->assertSessionHasNoErrors();

    $product = ProductModel::query()->firstOrFail();

    expect($product->assets)->toHaveCount(1)
        ->and($product->assets->first()?->getRelation('pivot')->getAttribute('role'))->toBe('image');

    // A bad target leaves no stray asset behind.
    $this->post(route('atrium.keystone.assets.upload'), ['type' => 'product', 'target' => 'NOPE', 'file' => UploadedFile::fake()->image('x.jpg')])
        ->assertSessionHasErrors('target');

    expect(AssetModel::query()->count())->toBe(1);
});
