<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\ProductModel\Actions;

use Illuminate\Support\Facades\DB;
use RefactorCircus\Keystone\Domains\Asset\Services\AssetLinks;
use RefactorCircus\Keystone\Domains\Association\Services\Associations;
use RefactorCircus\Keystone\Domains\Product\Models\ProductModel;
use RefactorCircus\Keystone\Domains\ProductModel\Events\ProductModelDeletedActionEvent;
use RefactorCircus\Keystone\Domains\ProductModel\Events\ProductModelDeletingActionEvent;
use RefactorCircus\Keystone\Domains\ProductModel\Models\ProductModelModel;
use RefactorCircus\Keystone\Domains\Search\Services\ProductIndex;
use RefactorCircus\Keystone\Domains\Workflow\Services\Versions;

final class DeleteProductModelAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function __construct(private readonly ProductIndex $index) {}

    public function execute(ProductModelModel $productModel): ProductModelModel
    {
        ProductModelDeletingActionEvent::dispatch($productModel);

        $result = $this->perform($productModel);

        ProductModelDeletedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * Sub-models and variant products go with the model, as in any PIM:
     * a variant cannot exist without the model it varies.
     */
    private function perform(ProductModelModel $productModel): ProductModelModel
    {
        return DB::transaction(function () use ($productModel): ProductModelModel {
            $products = $this->index->productIdsUnder($productModel);
            $models = [$productModel->id, ...$productModel->children()->pluck('id')->all()];

            $productModel->delete();

            // Links are polymorphic; nothing cascades them.
            $links = app(AssetLinks::class);
            $links->forget(ProductModelModel::class, $models);
            $links->forget(ProductModel::class, $products);

            $associations = app(Associations::class);
            $associations->forget(ProductModelModel::class, $models);
            $associations->forget(ProductModel::class, $products);
            app(Versions::class)->forget($products);

            $this->index->queue($products);

            return $productModel;
        });
    }
}
