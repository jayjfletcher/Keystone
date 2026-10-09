<?php

declare(strict_types=1);

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use RefactorCircus\Keystone\Domains\Channel\Models\ChannelModel;
use RefactorCircus\Keystone\Domains\Channel\Models\LocaleModel;
use RefactorCircus\Keystone\Domains\Product\Models\ProductModel;
use RefactorCircus\Keystone\Tests\Fixtures\Catalog;

beforeEach(function (): void {
    ValidateCsrfToken::except(['*']);

    // Atrium denies access outside local until a gate is defined.
    app()->detectEnvironment(fn (): string => 'local');
});

it('manages locales and channels on one page', function (): void {
    $this->get(route('atrium.keystone.channels.index'))->assertOk()->assertSee(__('keystone::keystone.no_channels'));

    $this->post(route('atrium.keystone.locales.store'), ['code' => 'en_US'])->assertRedirect(route('atrium.keystone.channels.index'));
    $this->post(route('atrium.keystone.locales.store'), ['code' => 'fr_FR'])->assertRedirect();

    $this->post(route('atrium.keystone.channels.store'), ['code' => 'web', 'locales' => ['en_US', 'fr_FR'], 'currencies' => 'usd, eur'])
        ->assertRedirect(route('atrium.keystone.channels.show', 'web'));

    $channel = ChannelModel::query()->with('locales')->firstOrFail();

    expect($channel->currencies)->toBe(['USD', 'EUR'])
        ->and($channel->locales->pluck('code')->all())->toBe(['en_US', 'fr_FR']);

    $this->get(route('atrium.keystone.channels.index'))->assertOk()->assertSee('data-locale="en_US"', false)->assertSee('web');

    $this->patch(route('atrium.keystone.channels.update', 'web'), ['locales' => ['en_US'], 'currencies' => 'USD', 'category_tree' => ''])->assertSessionHasNoErrors();

    $this->from(route('atrium.keystone.channels.index'))
        ->delete(route('atrium.keystone.locales.destroy', 'en_US'))
        ->assertSessionHasErrors('locale');

    $this->delete(route('atrium.keystone.locales.destroy', 'fr_FR'))->assertRedirect();
    expect(LocaleModel::query()->pluck('code')->all())->toBe(['en_US']);

    $this->delete(route('atrium.keystone.channels.destroy', 'web'))->assertRedirect(route('atrium.keystone.channels.index'));
});

it('edits values per locale and channel', function (): void {
    Catalog::apparel();
    $this->postJson('/keystone/attributes', ['code' => 'copy', 'type' => 'text', 'is_localizable' => true, 'is_scopable' => true]);
    ProductModel::factory()->create(['identifier' => 'TEE-1']);

    $this->get(route('atrium.keystone.products.show', ['product' => 'TEE-1', 'add' => 'copy', 'locale' => 'fr', 'channel' => 'ecommerce']))
        ->assertOk()
        ->assertSee('data-testid="slot-picker"', false)
        ->assertSee('data-attribute="copy"', false);

    $this->patch(route('atrium.keystone.products.update', 'TEE-1'), ['add' => 'copy', 'locale' => 'fr', 'channel' => 'ecommerce', 'v' => ['copy' => 'Texte web']])
        ->assertRedirect(route('atrium.keystone.products.show', ['product' => 'TEE-1', 'locale' => 'fr', 'channel' => 'ecommerce']));

    $this->patch(route('atrium.keystone.products.update', 'TEE-1'), ['add' => 'copy', 'locale' => 'en', 'channel' => 'print', 'v' => ['copy' => 'Print copy']]);

    $product = ProductModel::query()->firstOrFail();

    expect($product->value('copy', 'ecommerce', 'fr'))->toBe('Texte web')
        ->and($product->value('copy', 'print', 'en'))->toBe('Print copy');

    $this->get(route('atrium.keystone.products.show', ['product' => 'TEE-1', 'locale' => 'fr', 'channel' => 'ecommerce']))->assertOk()->assertSee('Texte web');
});
