<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\ProductModel\Actions;

use Illuminate\Support\Facades\DB;
use JayI\Keystone\Domains\Asset\Services\AssetLinks;
use JayI\Keystone\Domains\Association\Services\Associations;
use JayI\Keystone\Domains\Product\Models\ProductModel;
use JayI\Keystone\Domains\ProductModel\Events\ProductModelDeletedActionEvent;
use JayI\Keystone\Domains\ProductModel\Events\ProductModelDeletingActionEvent;
use JayI\Keystone\Domains\ProductModel\Models\ProductModelModel;
use JayI\Keystone\Domains\Search\Services\ProductIndex;
use JayI\Keystone\Domains\Workflow\Services\Versions;

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
