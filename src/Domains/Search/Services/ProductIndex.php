<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Search\Services;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Events\Dispatcher as Events;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use JayI\Keystone\Domains\Category\Models\CategoryModel;
use JayI\Keystone\Domains\Owner\Models\OwnerModel;
use JayI\Keystone\Domains\Product\Models\ProductModel;
use JayI\Keystone\Domains\ProductModel\Models\ProductModelModel;
use JayI\Keystone\Domains\Search\Contracts\SearchEngine;
use JayI\Keystone\Domains\Search\Events\ProductsQueuedForSync;
use JayI\Keystone\Domains\Workflow\Services\CompletenessCalculator;
use JayI\Keystone\Jobs\SyncProductIndex;

/**
 * Keeps what is derived from products — completeness scores and the search
 * engine's index — in step with the products table.
 */
final class ProductIndex
{
    public function __construct(
        private readonly SearchEngine $engine,
        private readonly Config $config,
        private readonly CompletenessCalculator $completeness,
        private readonly Events $events,
    ) {}

    /**
     * Queue a sync of these products, after the surrounding transaction
     * commits.
     *
     * @param  array<int, string>  $ids
     */
    public function queue(array $ids): void
    {
        if ($ids === []) {
            return;
        }

        $ids = array_values(array_unique($ids));

        $this->events->dispatch(new ProductsQueuedForSync($ids));

        foreach (array_chunk($ids, 500) as $chunk) {
            SyncProductIndex::dispatch($chunk)
                ->onConnection($this->config->get('keystone.search.queue.connection'))
                ->onQueue($this->config->get('keystone.search.queue.queue'))
                ->afterCommit();
        }
    }

    /**
     * Queue a sync of every product a query matches, in chunks.
     *
     * @param  Builder<ProductModel>  $query
     */
    public function queueQuery(Builder $query): void
    {
        $query->select('id')->chunkById(500, function (Collection $products): void {
            /** @var array<int, string> $ids */
            $ids = $products->modelKeys();

            $this->queue($ids);
        });
    }

    /**
     * Write these products to the index, and remove those that no longer exist.
     *
     * @param  array<int, string>  $ids
     */
    public function sync(array $ids): void
    {
        $products = $this->withContext(ProductModel::query()->whereKey($ids))->get();

        // Completeness first: the index carries it.
        $this->completeness->refresh($products);

        if (! $this->engine->maintainsIndex()) {
            return;
        }

        $this->engine->index($products->load('completeness.channel', 'completeness.locale'));

        $missing = array_values(array_diff($ids, $products->modelKeys()));

        if ($missing !== []) {
            $this->engine->remove($missing);
        }
    }

    /**
     * Empty the index and write every product to it again.
     *
     * @param  (callable(int): void)|null  $progress  Called with each chunk's size.
     */
    public function rebuild(?callable $progress = null): int
    {
        $indexed = $this->engine->maintainsIndex();

        if ($indexed) {
            $this->engine->reset();
        }

        $count = 0;

        $this->withContext(ProductModel::query())->chunkById(500, function (Collection $products) use (&$count, $progress, $indexed): void {
            $this->completeness->refresh($products);

            if ($indexed) {
                $this->engine->index($products->load('completeness.channel', 'completeness.locale'));
            }

            $count += $products->count();

            if ($progress !== null) {
                $progress($products->count());
            }
        });

        return $count;
    }

    /**
     * The ids of every variant product under a product model, at any depth.
     *
     * @return array<int, string>
     */
    public function productIdsUnder(ProductModelModel $model): array
    {
        $models = [$model->id, ...ProductModelModel::query()->where('parent_id', $model->id)->pluck('id')->all()];

        /** @var array<int, string> $ids */
        $ids = ProductModel::query()->whereIn('parent_id', $models)->pluck('id')->all();

        return $ids;
    }

    /**
     * The ids of every product owned by this owner or anything beneath it,
     * directly or through a product model.
     *
     * @return array<int, string>
     */
    public function productIdsOwnedWithin(OwnerModel $owner): array
    {
        $owners = OwnerModel::query()->subtreeOf($owner)->pluck('id')->all();

        $models = ProductModelModel::query()->whereIn('owner_id', $owners)->pluck('id')->all();
        $models = [...$models, ...ProductModelModel::query()->whereIn('parent_id', $models)->pluck('id')->all()];

        /** @var array<int, string> $ids */
        $ids = ProductModel::query()
            ->whereIn('owner_id', $owners)
            ->orWhereIn('parent_id', $models)
            ->pluck('id')
            ->all();

        return $ids;
    }

    /**
     * The ids of every product in this category or any category beneath it,
     * directly or through a product model.
     *
     * @return array<int, string>
     */
    public function productIdsInCategories(CategoryModel $category): array
    {
        $categories = CategoryModel::query()->subtreeOf($category)->pluck('id')->all();

        $models = ProductModelModel::query()
            ->whereHas('categories', fn (Builder $query): Builder => $query->whereKey($categories))
            ->pluck('id')
            ->all();
        $models = [...$models, ...ProductModelModel::query()->whereIn('parent_id', $models)->pluck('id')->all()];

        /** @var array<int, string> $ids */
        $ids = ProductModel::query()
            ->whereHas('categories', fn (Builder $query): Builder => $query->whereKey($categories))
            ->orWhereIn('parent_id', $models)
            ->pluck('id')
            ->all();

        return $ids;
    }

    /**
     * @param  Builder<ProductModel>  $query
     * @return Builder<ProductModel>
     */
    private function withContext(Builder $query): Builder
    {
        return $query->with(['family', 'owner', 'categories', 'parent.owner', 'parent.categories', 'parent.parent.owner', 'parent.parent.categories']);
    }
}
