<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Product\Actions;

use RefactorCircus\Keystone\Domains\Asset\Services\AssetLinks;
use RefactorCircus\Keystone\Domains\Association\Services\Associations;
use RefactorCircus\Keystone\Domains\Product\Events\ProductDeletedActionEvent;
use RefactorCircus\Keystone\Domains\Product\Events\ProductDeletingActionEvent;
use RefactorCircus\Keystone\Domains\Product\Models\ProductModel;
use RefactorCircus\Keystone\Domains\Search\Services\ProductIndex;
use RefactorCircus\Keystone\Domains\Workflow\Services\Versions;

final class DeleteProductAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function __construct(private readonly ProductIndex $index) {}

    public function execute(ProductModel $product): ProductModel
    {
        ProductDeletingActionEvent::dispatch($product);

        $result = $this->perform($product);

        ProductDeletedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * Unique values go with the product; the foreign key cascades.
     */
    private function perform(ProductModel $product): ProductModel
    {
        $product->delete();

        app(AssetLinks::class)->forget(ProductModel::class, [$product->id]);
        app(Associations::class)->forget(ProductModel::class, [$product->id]);
        app(Versions::class)->forget([$product->id]);

        $this->index->queue([$product->id]);

        return $product;
    }
}
