<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\ProductModel\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RefactorCircus\Keystone\Domains\Association\Services\Associations;
use RefactorCircus\Keystone\Domains\Attribute\Concerns\WritesValues;
use RefactorCircus\Keystone\Domains\Category\Concerns\AssignsCategories;
use RefactorCircus\Keystone\Domains\Owner\Concerns\AssignsOwners;
use RefactorCircus\Keystone\Domains\ProductModel\Events\ProductModelUpdatedActionEvent;
use RefactorCircus\Keystone\Domains\ProductModel\Events\ProductModelUpdatingActionEvent;
use RefactorCircus\Keystone\Domains\ProductModel\Models\ProductModelModel;
use RefactorCircus\Keystone\Domains\Search\Services\ProductIndex;

final class UpdateProductModelAction
{
    use AssignsCategories;
    use AssignsOwners;
    use WritesValues;

    /**
     * `values` patches only the slots sent; `data: null` clears one.
     *
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'code' => ['prohibited'],
            'family_variant' => ['prohibited'],
            'parent' => ['prohibited'],
            'owner' => ['sometimes', 'nullable', 'string', 'exists:keystone_owners,code'],
            'values' => ['sometimes', 'nullable', 'array'],
        ] + self::categoryRules() + Associations::rules();
    }

    public function __construct(private readonly ProductIndex $index) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function execute(ProductModelModel $productModel, array $data): ProductModelModel
    {
        ProductModelUpdatingActionEvent::dispatch($productModel, $data);

        $result = $this->perform($productModel, $data);

        ProductModelUpdatedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(ProductModelModel $productModel, array $data): ProductModelModel
    {
        return DB::transaction(function () use ($productModel, $data): ProductModelModel {
            if (array_key_exists('values', $data)) {
                $variant = $productModel->familyVariant()->with('family.familyAttributes')->firstOrFail();
                $level = $productModel->level();

                $values = $this->patchValues(
                    $productModel->ownValues(),
                    $data['values'],
                    $variant->attributesAt($level),
                    sprintf('is not set at level %d of family variant "%s".', $level, $variant->code),
                );

                if ($level === 1) {
                    $siblings = ProductModelModel::query()
                        ->where('parent_id', $productModel->parent_id)
                        ->whereKeyNot($productModel->getKey())
                        ->get()
                        ->map(fn (ProductModelModel $sibling): array => $sibling->ownValues());

                    $this->checkAxes($variant, 1, $values, $siblings);
                }

                $productModel->values = $values;
                $productModel->save();

                // Variant products carry the model's values in the index.
                $this->index->queue($this->index->productIdsUnder($productModel));
            }

            if (array_key_exists('owner', $data)) {
                if ($productModel->parent_id !== null) {
                    $this->refuseInheritedOwner('sub-model');
                }

                $productModel->owner()->associate($this->ownerFor($data['owner']));
                $productModel->save();

                $this->index->queue($this->index->productIdsUnder($productModel));
            }

            app(Associations::class)->write($productModel, $data);

            if (array_key_exists('categories', $data)) {
                $this->assignCategories($productModel, $data);

                // Variants inherit the model's categories.
                $this->index->queue($this->index->productIdsUnder($productModel));
            }

            return $productModel->load(['familyVariant', 'parent', 'owner', 'categories', ...Associations::eagerLoads()]);
        });
    }
}
