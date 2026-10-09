<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\ProductModel\Actions;

use RefactorCircus\Showroom\Domains\Association\Services\Associations;
use RefactorCircus\Showroom\Domains\Attribute\Data\ValueFilter;
use RefactorCircus\Showroom\Domains\ProductModel\Events\ProductModelShowingActionEvent;
use RefactorCircus\Showroom\Domains\ProductModel\Events\ProductModelShownActionEvent;
use RefactorCircus\Showroom\Domains\ProductModel\Models\ProductModelModel;

final class ShowProductModelAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        // Return only one channel's and some locales' values.
        return ValueFilter::rules();
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function execute(ProductModelModel $productModel, array $options = []): ProductModelModel
    {
        ProductModelShowingActionEvent::dispatch($productModel);

        $result = $this->perform($productModel)->useValueFilter(ValueFilter::fromArray($options));

        ProductModelShownActionEvent::dispatch($result);

        return $result;
    }

    private function perform(ProductModelModel $productModel): ProductModelModel
    {
        return $productModel->load(['assets', 'parent.assets', 'familyVariant.family', 'parent.owner', 'parent.categories', 'owner', 'categories', 'children', 'products', ...Associations::eagerLoads()]);
    }
}
