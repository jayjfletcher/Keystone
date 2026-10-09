<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains;

use Illuminate\Support\ServiceProvider;
use RefactorCircus\Keystone\Domains\Asset\AssetServiceProvider;
use RefactorCircus\Keystone\Domains\Association\AssociationServiceProvider;
use RefactorCircus\Keystone\Domains\Attribute\AttributeServiceProvider;
use RefactorCircus\Keystone\Domains\Category\CategoryServiceProvider;
use RefactorCircus\Keystone\Domains\Channel\ChannelServiceProvider;
use RefactorCircus\Keystone\Domains\Family\FamilyServiceProvider;
use RefactorCircus\Keystone\Domains\Owner\OwnerServiceProvider;
use RefactorCircus\Keystone\Domains\Product\ProductServiceProvider;
use RefactorCircus\Keystone\Domains\ProductModel\ProductModelServiceProvider;
use RefactorCircus\Keystone\Domains\Search\SearchServiceProvider;
use RefactorCircus\Keystone\Domains\Transfer\TransferServiceProvider;
use RefactorCircus\Keystone\Domains\Workflow\WorkflowServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    /**
     * The domain service providers.
     *
     * @var array<int, class-string<ServiceProvider>>
     */
    private array $providers = [
        AssetServiceProvider::class,
        AssociationServiceProvider::class,
        AttributeServiceProvider::class,
        CategoryServiceProvider::class,
        ChannelServiceProvider::class,
        FamilyServiceProvider::class,
        OwnerServiceProvider::class,
        ProductServiceProvider::class,
        ProductModelServiceProvider::class,
        SearchServiceProvider::class,
        TransferServiceProvider::class,
        WorkflowServiceProvider::class,
    ];

    public function register(): void
    {
        foreach ($this->providers as $provider) {
            $this->app->register($provider);
        }
    }
}
