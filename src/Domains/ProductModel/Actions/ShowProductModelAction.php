<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\ProductModel\Actions;

use RefactorCircus\Keystone\Domains\Association\Services\Associations;
use RefactorCircus\Keystone\Domains\Attribute\Data\ValueFilter;
use RefactorCircus\Keystone\Domains\ProductModel\Events\ProductModelShowingActionEvent;
use RefactorCircus\Keystone\Domains\ProductModel\Events\ProductModelShownActionEvent;
use RefactorCircus\Keystone\Domains\ProductModel\Models\ProductModelModel;

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
