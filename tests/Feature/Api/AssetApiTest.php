<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use JayI\Keystone\Domains\Asset\Models\AssetModel;
use JayI\Keystone\Tests\Fixtures\Catalog;

beforeEach(function (): void {
    config()->set('keystone.media.disk', 'assets');
    Storage::fake('assets');
});

it('uploads a file to the configured disk', function (): void {
    $response = $this->post('/keystone/assets', [
        'file' => UploadedFile::fake()->image('Front View.JPG', 40, 30),
        'labels' => ['en' => 'Front view'],
    ], ['Accept' => 'application/json'])
        ->assertCreated()
        ->assertJsonPath('data.filename', 'front-view.jpg')
        ->assertJsonPath('data.mime_type', 'image/jpeg')
        ->assertJsonPath('data.labels.en', 'Front view');

    $asset = AssetModel::query()->firstOrFail();

    expect($response->json('data.code'))->toStartWith('front-view-')
        ->and($asset->disk)->toBe('assets')
        ->and($asset->path)->toStartWith('keystone/assets/')
        ->and($asset->size)->toBeGreaterThan(0)
        ->and($asset->checksum)->toBe(hash('sha256', (string) Storage::disk('assets')->get($asset->path)));

    Storage::disk('assets')->assertExists($asset->path);
});

it('adopts a file already on the disk, as after a presigned S3 upload', function (): void {
    Storage::disk('assets')->put('incoming/manual.pdf', '%PDF-1.4 manual');

    $this->postJson('/keystone/assets', ['code' => 'tee-manual', 'path' => 'incoming/manual.pdf'])
        ->assertCreated()
        ->assertJsonPath('data.code', 'tee-manual')
        ->assertJsonPath('data.filename', 'manual.pdf')
        ->assertJsonPath('data.size', 15);

    expect(AssetModel::query()->firstOrFail()->path)->toBe('incoming/manual.pdf');

    $this->postJson('/keystone/assets', ['path' => 'incoming/missing.pdf'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['path' => 'No file exists at "incoming/missing.pdf" on the "assets" disk.']);
});

it('fetches a file from a URL', function (): void {
    Http::fake([
        'cdn.example.test/*' => Http::response('png-bytes', 200, ['Content-Type' => 'image/png; charset=binary']),
        'broken.example.test/*' => Http::response('', 404),
    ]);

    $this->postJson('/keystone/assets', ['url' => 'https://cdn.example.test/images/Tee%20Back.png'])
        ->assertCreated()
        ->assertJsonPath('data.mime_type', 'image/png')
        ->assertJsonPath('data.size', 9);

    Storage::disk('assets')->assertExists(AssetModel::query()->firstOrFail()->path);

    $this->postJson('/keystone/assets', ['url' => 'https://broken.example.test/x.png'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['url' => 'The URL answered with status 404.']);
});

it('takes exactly one source', function (): void {
    $this->postJson('/keystone/assets', [])->assertUnprocessable()->assertJsonValidationErrors(['file', 'path', 'url']);

    $this->postJson('/keystone/assets', ['path' => 'a.jpg', 'url' => 'https://example.test/a.jpg'])
        ->assertUnprocessable()->assertJsonValidationErrors('path');
});

it('enforces size and type limits on every source', function (): void {
    config()->set('keystone.media.max_kilobytes', 1);
    config()->set('keystone.media.mime_types', ['image/png']);

    $this->post('/keystone/assets', ['file' => UploadedFile::fake()->create('big.png', 5, 'image/png')], ['Accept' => 'application/json'])
        ->assertUnprocessable()->assertJsonValidationErrors('file');

    Http::fake(['*' => Http::response(str_repeat('x', 2048), 200, ['Content-Type' => 'image/png'])]);

    $this->postJson('/keystone/assets', ['url' => 'https://example.test/big.png'])
        ->assertUnprocessable()->assertJsonValidationErrors(['url' => 'The file is 2 KB; the limit is 1 KB.']);

    // What Keystone fetched and refused is not left behind.
    expect(Storage::disk('assets')->allFiles())->toBe([]);

    Storage::disk('assets')->put('incoming/doc.txt', 'text');

    $this->postJson('/keystone/assets', ['path' => 'incoming/doc.txt'])
        ->assertUnprocessable()->assertJsonValidationErrors('path');

    // An adopted file belongs to the caller, so it stays.
    Storage::disk('assets')->assertExists('incoming/doc.txt');
});

it('replaces the file but keeps the code and links', function (): void {
    Catalog::apparel();
    $this->postJson('/keystone/products', ['identifier' => 'TEE-1'])->assertCreated();

    $this->post('/keystone/assets', ['code' => 'hero', 'file' => UploadedFile::fake()->image('old.jpg')], ['Accept' => 'application/json'])->assertCreated();
    $old = AssetModel::query()->firstOrFail()->path;
    $this->postJson('/keystone/assets/hero/links', ['type' => 'product', 'target' => 'TEE-1', 'role' => 'image'])->assertOk();

    $this->post('/keystone/assets/hero', ['_method' => 'PATCH', 'file' => UploadedFile::fake()->image('new.png')], ['Accept' => 'application/json'])
        ->assertOk()
        ->assertJsonPath('data.code', 'hero')
        ->assertJsonPath('data.filename', 'new.png');

    Storage::disk('assets')->assertMissing($old);

    $this->getJson('/keystone/products/TEE-1')->assertJsonPath('data.assets.0.code', 'hero');

    $this->patchJson('/keystone/assets/hero', ['code' => 'other'])->assertUnprocessable()->assertJsonValidationErrors('code');
});

it('links assets to products, models and owners under roles', function (): void {
    Catalog::apparel();
    $this->postJson('/keystone/owner-types', ['code' => 'brand'])->assertCreated();
    $this->postJson('/keystone/owners', ['code' => 'acme', 'type' => 'brand'])->assertCreated();
    $this->postJson('/keystone/product-models', ['code' => 'tee', 'family_variant' => 'shirts_by_size'])->assertCreated();
    $this->postJson('/keystone/products', ['identifier' => 'TEE-M', 'parent' => 'tee', 'values' => ['size' => Catalog::value('m')]])->assertCreated();

    foreach (['logo', 'front', 'back', 'size-chart'] as $code) {
        $this->post('/keystone/assets', ['code' => $code, 'file' => UploadedFile::fake()->image($code.'.jpg')], ['Accept' => 'application/json'])->assertCreated();
    }

    $this->postJson('/keystone/assets/logo/links', ['type' => 'owner', 'target' => 'acme', 'role' => 'logo'])->assertOk();
    $this->postJson('/keystone/assets/front/links', ['type' => 'product_model', 'target' => 'tee', 'role' => 'image', 'sort_order' => 1])->assertOk();
    $this->postJson('/keystone/assets/back/links', ['type' => 'product_model', 'target' => 'tee', 'role' => 'image', 'sort_order' => 2])->assertOk();
    $this->postJson('/keystone/assets/size-chart/links', ['type' => 'product', 'target' => 'TEE-M', 'role' => 'manual'])->assertOk()
        ->assertJsonPath('data.links', [['type' => 'product', 'target' => 'TEE-M', 'role' => 'manual', 'sort_order' => 0]]);

    $this->postJson('/keystone/assets/logo/links', ['type' => 'product', 'target' => 'NOPE'])
        ->assertUnprocessable()->assertJsonValidationErrors(['target' => 'No product "NOPE" exists.']);

    $this->getJson('/keystone/owners/acme')->assertJsonPath('data.assets.0.code', 'logo')->assertJsonPath('data.assets.0.role', 'logo');

    // A variant shows its own assets, then its model's.
    $this->getJson('/keystone/products/TEE-M')
        ->assertJsonPath('data.assets.0.code', 'size-chart')
        ->assertJsonPath('data.assets.0.inherited', false)
        ->assertJsonPath('data.assets.1.code', 'front')
        ->assertJsonPath('data.assets.2.code', 'back')
        ->assertJsonPath('data.assets.2.inherited', true);

    expect(collect($this->getJson('/keystone/assets?product_model=tee&role=image')->json('data'))->pluck('code')->sort()->values()->all())->toBe(['back', 'front'])
        ->and(collect($this->getJson('/keystone/assets?type=image/*')->json('data'))->count())->toBe(4);

    $this->deleteJson('/keystone/assets/front/links', ['type' => 'product_model', 'target' => 'tee'])->assertOk()->assertJsonPath('data.links', []);
});

it('deletes an asset with its links and its file', function (): void {
    $this->postJson('/keystone/products', ['identifier' => 'TEE-1'])->assertCreated();
    $this->post('/keystone/assets', ['code' => 'hero', 'file' => UploadedFile::fake()->image('hero.jpg')], ['Accept' => 'application/json'])->assertCreated();
    $this->postJson('/keystone/assets/hero/links', ['type' => 'product', 'target' => 'TEE-1'])->assertOk();
    $path = AssetModel::query()->firstOrFail()->path;

    $this->deleteJson('/keystone/assets/hero')->assertNoContent();

    Storage::disk('assets')->assertMissing($path);
    $this->getJson('/keystone/products/TEE-1')->assertJsonPath('data.assets', []);
});

it('keeps files when told to', function (): void {
    config()->set('keystone.media.delete_files', false);

    $this->post('/keystone/assets', ['code' => 'hero', 'file' => UploadedFile::fake()->image('hero.jpg')], ['Accept' => 'application/json'])->assertCreated();
    $path = AssetModel::query()->firstOrFail()->path;

    $this->deleteJson('/keystone/assets/hero')->assertNoContent();

    Storage::disk('assets')->assertExists($path);
});

it('removes links when the linked record goes', function (): void {
    $this->postJson('/keystone/products', ['identifier' => 'TEE-1'])->assertCreated();
    $this->post('/keystone/assets', ['code' => 'hero', 'file' => UploadedFile::fake()->image('hero.jpg')], ['Accept' => 'application/json'])->assertCreated();
    $this->postJson('/keystone/assets/hero/links', ['type' => 'product', 'target' => 'TEE-1'])->assertOk();

    $this->deleteJson('/keystone/products/TEE-1')->assertNoContent();

    $this->getJson('/keystone/assets/hero')->assertJsonPath('data.links', []);
});

it('serves temporary URLs for private disks when configured', function (): void {
    $this->post('/keystone/assets', ['code' => 'hero', 'file' => UploadedFile::fake()->image('hero.jpg')], ['Accept' => 'application/json'])->assertCreated();

    expect($this->getJson('/keystone/assets/hero')->json('data.url'))->toContain('/storage/keystone/assets/');

    config()->set('keystone.media.temporary_urls', 5);

    expect($this->getJson('/keystone/assets/hero')->json('data.url'))->toContain('expiration=');
});
