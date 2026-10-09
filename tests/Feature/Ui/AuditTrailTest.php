<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use RefactorCircus\Foundation\Audit\Contracts\AuditTrail;
use RefactorCircus\Foundation\Audit\Data\AuditEntry;
use RefactorCircus\Foundation\Audit\Data\AuditFilter;
use RefactorCircus\Foundation\Audit\Data\AuditPage;
use RefactorCircus\Keystone\Domains\Product\Models\ProductModel;
use RefactorCircus\Keystone\Tests\Fixtures\Catalog;

beforeEach(function (): void {
    // Atrium denies access outside local until a gate is defined.
    app()->detectEnvironment(fn (): string => 'local');

    Catalog::apparel();

    ProductModel::query()->create(['identifier' => 'TEE-1']);
});

it('shows no history until an audit log is installed', function (): void {
    $this->get(route('atrium.keystone.products.show', 'TEE-1'))
        ->assertOk()
        ->assertDontSee('data-testid="audit-trail"', false);

    $this->get(route('atrium.keystone.products.index'))
        ->assertOk()
        ->assertDontSee('data-testid="audit-trail"', false);
});

it('shows a product its own history from the installed audit log', function (): void {
    $trail = new class implements AuditTrail
    {
        public ?AuditFilter $filter = null;

        public function available(): bool
        {
            return true;
        }

        public function entries(AuditFilter $filter): AuditPage
        {
            $this->filter = $filter;

            return new AuditPage([new AuditEntry(
                id: 1,
                source: 'keystone',
                action: 'product.updated',
                surface: 'atrium',
                createdAt: CarbonImmutable::now()->subMinute(),
                actorId: '1',
                actorLabel: 'Ada Lovelace',
            )]);
        }
    };

    app()->instance(AuditTrail::class, $trail);

    $product = ProductModel::query()->where('identifier', 'TEE-1')->firstOrFail();

    $this->get(route('atrium.keystone.products.show', 'TEE-1'))
        ->assertOk()
        ->assertSee('data-testid="audit-trail"', false)
        ->assertSee('product.updated')
        ->assertSee('Ada Lovelace');

    expect($trail->filter?->source)->toBe('keystone')
        ->and($trail->filter?->subjectId)->toBe((string) $product->getKey());
});
