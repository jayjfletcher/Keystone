<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Product\Actions;

use RefactorCircus\Keystone\Domains\Association\Services\Associations;
use RefactorCircus\Keystone\Domains\Attribute\Data\ValueFilter;
use RefactorCircus\Keystone\Domains\Product\Events\ProductShowingActionEvent;
use RefactorCircus\Keystone\Domains\Product\Events\ProductShownActionEvent;
use RefactorCircus\Keystone\Domains\Product\Models\ProductModel;

final class ShowProductAction
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
    public function execute(ProductModel $product, array $options = []): ProductModel
    {
        ProductShowingActionEvent::dispatch($product);

        $result = $this->perform($product)->useValueFilter(ValueFilter::fromArray($options));

        ProductShownActionEvent::dispatch($result);

        return $result;
    }

    private function perform(ProductModel $product): ProductModel
    {
        return $product->load(['family', 'owner', 'categories', 'assets', 'parent.assets', 'parent.parent.assets', 'parent.categories', 'parent.parent.owner', 'parent.parent.categories', 'parent.owner', 'parent.familyVariant', 'completeness.channel', 'completeness.locale', ...Associations::eagerLoads()]);
    }
}
