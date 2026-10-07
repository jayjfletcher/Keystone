<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains;

use Illuminate\Support\ServiceProvider;
use JayI\Keystone\Domains\Asset\AssetServiceProvider;
use JayI\Keystone\Domains\Association\AssociationServiceProvider;
use JayI\Keystone\Domains\Attribute\AttributeServiceProvider;
use JayI\Keystone\Domains\Category\CategoryServiceProvider;
use JayI\Keystone\Domains\Channel\ChannelServiceProvider;
use JayI\Keystone\Domains\Family\FamilyServiceProvider;
use JayI\Keystone\Domains\Owner\OwnerServiceProvider;
use JayI\Keystone\Domains\Product\ProductServiceProvider;
use JayI\Keystone\Domains\ProductModel\ProductModelServiceProvider;
use JayI\Keystone\Domains\Search\SearchServiceProvider;
use JayI\Keystone\Domains\Transfer\TransferServiceProvider;
use JayI\Keystone\Domains\Workflow\WorkflowServiceProvider;

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
