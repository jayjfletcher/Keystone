<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use JayI\Impex\Domains\Flow\Services\FlowRegistry;
use JayI\Impex\Domains\Message\Models\MessageModel;
use JayI\Impex\Domains\Run\Enums\RunStatus;
use JayI\Impex\Domains\Run\Models\RunModel;
use JayI\Impex\Facades\Impex;
use JayI\Keystone\Domains\Asset\Models\AssetModel;
use JayI\Keystone\Domains\Product\Models\ProductModel;
use JayI\Keystone\Domains\Transfer\Mcp\Tools\StartExportTool;
use JayI\Keystone\Domains\Transfer\Mcp\Tools\StartImportTool;
use JayI\Keystone\Tests\Fixtures\Catalog;

beforeEach(function (): void {
    config()->set('keystone.media.disk', 'assets');
    Storage::fake('assets');

    Catalog::apparel();
    $this->postJson('/keystone/categories', ['code' => 'master'])->assertCreated();
});

function fileAsset(string $code, string $filename, string $contents): void
{
    Storage::disk('assets')->put('incoming/'.$filename, $contents);

    test()->postJson('/keystone/assets', ['code' => $code, 'path' => 'incoming/'.$filename])->assertCreated();
}

function finishedRun(string $id): RunModel
{
    $run = RunModel::query()->findOrFail($id);

    expect($run->status)->toBe(RunStatus::Completed, (string) json_encode($run->error));

    return $run;
}

it('registers its flows with Impex, one per feed', function (): void {
    config()->set('keystone.impex.feeds', ['web' => ['channel' => 'ecommerce']]);
    app()->forgetInstance(FlowRegistry::class);

    $flows = app(FlowRegistry::class);

    expect($flows->has('keystone:import-products'))->toBeTrue()
        ->and($flows->has('keystone:upsert-products'))->toBeTrue()
        ->and($flows->has('keystone:export-products'))->toBeTrue()
        ->and($flows->has('keystone:feed:web'))->toBeTrue();
});

it('imports a CSV file with Akeneo columns', function (): void {
    fileAsset('shirts-csv', 'shirts.csv', implode("\n", [
        "\u{FEFF}identifier,family,enabled,categories,name,description-en,description-fr,color,tags,weight,weight-unit,price-USD,price-EUR,organic,pack_size",
        'TEE-1,shirts,1,master,Classic tee,Soft,Doux,red,"summer,new",180,gram,19.99,17.50,1,3',
        'TEE-2,shirts,0,,Second tee,,,blue,,,,,,0,',
        'TEE-3,shirts,1,,Bad tee,,,purple,,,,,,,',
    ]));

    $id = $this->postJson('/keystone/imports', ['asset' => 'shirts-csv'])->assertStatus(202)->json('data.id');

    expect(Impex::result(finishedRun($id)))->toMatchArray(['total' => 3, 'succeeded' => 2, 'failed' => 1]);

    $this->getJson('/keystone/products/TEE-1')
        ->assertJsonPath('data.family', 'shirts')
        ->assertJsonPath('data.categories', ['master'])
        ->assertJsonPath('data.values.name.0.data', 'Classic tee')
        ->assertJsonPath('data.values.tags.0.data', ['summer', 'new'])
        ->assertJsonPath('data.values.weight.0.data', ['amount' => '180', 'unit' => 'gram'])
        ->assertJsonPath('data.values.price.0.data', [['amount' => '19.99', 'currency' => 'USD'], ['amount' => '17.50', 'currency' => 'EUR']])
        ->assertJsonPath('data.values.organic.0.data', true)
        ->assertJsonPath('data.values.pack_size.0.data', 3);

    $this->getJson('/keystone/products/TEE-2')->assertJsonPath('data.enabled', false)->assertJsonPath('data.values.organic.0.data', false);

    // The bad row's reason is kept with the batch item.
    $failed = collect(Impex::batchItems(Impex::result(finishedRun($id))['batch_id']))->firstWhere('status.value', 'failed');

    expect($failed?->error['message'] ?? '')->toContain('values.color.0.data');
});

it('imports JSONL in each mode', function (): void {
    $this->postJson('/keystone/products', ['identifier' => 'OLD', 'values' => ['name' => Catalog::value('Old name')]])->assertCreated();

    fileAsset('products-jsonl', 'products.jsonl', implode("\n", [
        json_encode(['identifier' => 'OLD', 'values' => ['name' => Catalog::value('New name')]]),
        json_encode(['identifier' => 'NEW', 'values' => ['name' => Catalog::value('Brand new')]]),
        '',
        'not json',
    ]));

    $create = $this->postJson('/keystone/imports', ['asset' => 'products-jsonl', 'mode' => 'create'])->json('data.id');

    expect(Impex::result(finishedRun($create)))->toMatchArray(['succeeded' => 2, 'failed' => 1]);
    expect(ProductModel::query()->where('identifier', 'OLD')->firstOrFail()->value('name'))->toBe('Old name');

    $update = $this->postJson('/keystone/imports', ['asset' => 'products-jsonl', 'mode' => 'update'])->json('data.id');
    finishedRun($update);

    expect(ProductModel::query()->where('identifier', 'OLD')->firstOrFail()->value('name'))->toBe('New name');
});

it('fetches the file to import from a URL', function (): void {
    // A fresh response per request: a streamed body is read once.
    Http::fake(['*' => fn () => Http::response(json_encode(['identifier' => 'URL-1'])."\n", 200, ['Content-Type' => 'application/x-ndjson'])]);

    $id = $this->postJson('/keystone/imports', ['url' => 'https://erp.example.test/export.jsonl'])->assertStatus(202)->json('data.id');

    finishedRun($id);
    expect(ProductModel::query()->where('identifier', 'URL-1')->exists())->toBeTrue();

    $this->postJson('/keystone/imports', ['url' => 'https://erp.example.test/export.xml'])
        ->assertUnprocessable()->assertJsonValidationErrors('format');
});

it('upserts records pushed by a connector', function (): void {
    $run = Impex::run('keystone:upsert-products', [['products' => [
        ['identifier' => 'ERP-1', 'values' => ['name' => Catalog::value('From the ERP')]],
        ['identifier' => 'ERP-2', 'family' => 'nope'],
    ]]]);

    expect(Impex::result(finishedRun($run->id)))->toMatchArray(['total' => 2, 'succeeded' => 1, 'failed' => 1]);
    expect(ProductModel::query()->where('identifier', 'ERP-1')->exists())->toBeTrue();
});

it('exports matching products to a JSONL or CSV asset', function (): void {
    $this->postJson('/keystone/products', ['identifier' => 'A', 'family' => 'shirts', 'categories' => ['master'], 'values' => [
        'name' => Catalog::value('Alpha'),
        'description' => [['locale' => 'en', 'scope' => null, 'data' => 'Soft'], ['locale' => 'fr', 'scope' => null, 'data' => 'Doux']],
        'weight' => Catalog::value(['amount' => 180, 'unit' => 'gram']),
    ]])->assertCreated();
    $this->postJson('/keystone/products', ['identifier' => 'B', 'values' => ['name' => Catalog::value('Beta')]])->assertCreated();

    $jsonl = $this->postJson('/keystone/exports', ['family' => 'shirts', 'locales' => ['en'], 'code' => 'shirts-export'])->assertStatus(202)->json('data.id');

    expect(Impex::result(finishedRun($jsonl)))->toBe(['asset' => 'shirts-export', 'count' => 1, 'format' => 'jsonl']);

    $asset = AssetModel::query()->where('code', 'shirts-export')->firstOrFail();
    $lines = array_values(array_filter(explode("\n", (string) Storage::disk('assets')->get($asset->path))));
    $record = json_decode($lines[0], true);

    expect($lines)->toHaveCount(1)
        ->and($record['identifier'])->toBe('A')
        ->and($record['values']['description'])->toBe([['locale' => 'en', 'scope' => null, 'data' => 'Soft']]);

    $csv = $this->postJson('/keystone/exports', ['format' => 'csv'])->json('data.id');
    $result = Impex::result(finishedRun($csv));

    $file = (string) Storage::disk('assets')->get(AssetModel::query()->where('code', $result['asset'])->firstOrFail()->path);
    $rows = array_map('str_getcsv', array_values(array_filter(explode("\n", $file))));

    expect($rows[0])->toContain('identifier', 'description-en', 'description-fr', 'weight', 'weight-unit')
        ->and($rows)->toHaveCount(3)
        ->and(array_combine($rows[0], $rows[1])['weight-unit'])->toBe('gram');

    // What was exported imports back.
    ProductModel::query()->delete();
    $back = $this->postJson('/keystone/imports', ['asset' => $result['asset']])->json('data.id');

    expect(Impex::result(finishedRun($back)))->toMatchArray(['succeeded' => 2, 'failed' => 0]);
});

it('exports live versions only when asked', function (): void {
    config()->set('keystone.workflow.require_approval', false);

    $this->postJson('/keystone/products', ['identifier' => 'LIVE', 'values' => ['name' => Catalog::value('Live name')]])->assertCreated();
    $this->postJson('/keystone/products/LIVE/transitions', ['transition' => 'publish'])->assertOk();
    $this->patchJson('/keystone/products/LIVE', ['values' => ['name' => Catalog::value('Draft name')]])->assertOk();
    $this->postJson('/keystone/products', ['identifier' => 'NEVER'])->assertCreated();

    $id = $this->postJson('/keystone/exports', ['published' => true, 'code' => 'live'])->json('data.id');

    expect(Impex::result(finishedRun($id))['count'])->toBe(1);

    $record = json_decode(trim((string) Storage::disk('assets')->get(AssetModel::query()->where('code', 'live')->firstOrFail()->path)), true);

    expect($record['values']['name'][0]['data'])->toBe('Live name');
});

it('builds and delivers a channel feed', function (): void {
    config()->set('keystone.workflow.require_approval', false);
    config()->set('keystone.impex.feeds', ['web' => ['channel' => 'print', 'format' => 'jsonl', 'url' => 'https://feeds.example.test/in', 'ledger_channel' => 'web-feed']]);
    app()->forgetInstance(FlowRegistry::class);

    Http::fake(['feeds.example.test/*' => Http::response(['ok' => true], 200)]);

    $this->postJson('/keystone/products', ['identifier' => 'LIVE', 'values' => ['description' => [
        ['locale' => 'en', 'scope' => null, 'data' => 'English'],
        ['locale' => 'fr', 'scope' => null, 'data' => 'French'],
    ]]])->assertCreated();
    $this->postJson('/keystone/products/LIVE/transitions', ['transition' => 'publish'])->assertOk();

    $run = Impex::run('keystone:feed:web');
    $result = Impex::result(finishedRun($run->id));

    expect($result['count'])->toBe(1)
        ->and($result['delivery'])->toBe(['status' => 200]);

    $record = json_decode(trim((string) Storage::disk('assets')->get(AssetModel::query()->where('code', $result['asset'])->firstOrFail()->path)), true);

    // The print channel publishes in English only.
    expect($record['values']['description'])->toBe([['locale' => 'en', 'scope' => null, 'data' => 'English']]);

    Http::assertSent(fn ($request): bool => $request->url() === 'https://feeds.example.test/in');
    expect(MessageModel::query()->where('channel', 'web-feed')->where('run_id', $run->id)->exists())->toBeTrue();
});

it('delivers a feed through a named Impex channel, the channel\'s way', function (): void {
    config()->set('keystone.workflow.require_approval', false);
    config()->set('impex.channels.partner-drop', [
        'direction' => 'outbound',
        'transport' => 'file',
        'options' => ['disk' => 'drops', 'path' => 'incoming/{id}.jsonl'],
    ]);
    config()->set('keystone.impex.feeds', ['partner' => ['channel' => 'print', 'deliver_through' => 'partner-drop']]);
    app()->forgetInstance(FlowRegistry::class);
    Storage::fake('drops');

    $this->postJson('/keystone/products', ['identifier' => 'LIVE'])->assertCreated();
    $this->postJson('/keystone/products/LIVE/transitions', ['transition' => 'publish'])->assertOk();

    $run = Impex::run('keystone:feed:partner');
    $result = Impex::result(finishedRun($run->id));

    Storage::disk('drops')->assertExists('incoming/'.$result['asset'].'.jsonl');

    expect(MessageModel::query()->where('channel', 'partner-drop')->where('run_id', $run->id)->sole()->transport)->toBe('file');
});

it('starts imports and exports over MCP', function (): void {
    fileAsset('products-jsonl', 'products.jsonl', json_encode(['identifier' => 'MCP-1'])."\n");

    mcpTool(StartImportTool::class, ['asset' => 'products-jsonl'])->assertOk()->assertSee('keystone:import-products');
    mcpTool(StartExportTool::class, ['format' => 'csv'])->assertOk()->assertSee('keystone:export-products');

    expect(ProductModel::query()->where('identifier', 'MCP-1')->exists())->toBeTrue();
});

it('says so when Impex is switched off', function (): void {
    config()->set('keystone.impex.enabled', false);

    $this->postJson('/keystone/exports', [])
        ->assertStatus(501)
        ->assertJsonPath('message', fn (string $message): bool => str_contains($message, 'composer require jayi/impex'));
});

it('fails the run when more rows fail than tolerated', function (): void {
    config()->set('keystone.impex.allow_failures', 0.0);

    fileAsset('bad-jsonl', 'bad.jsonl', "not json\n");

    $id = $this->postJson('/keystone/imports', ['asset' => 'bad-jsonl'])->json('data.id');

    expect(RunModel::query()->findOrFail($id)->status)->toBe(RunStatus::Failed);
});
