<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Product\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RefactorCircus\Keystone\Domains\Association\Services\Associations;
use RefactorCircus\Keystone\Domains\Attribute\Services\UniqueValues;
use RefactorCircus\Keystone\Domains\Category\Concerns\AssignsCategories;
use RefactorCircus\Keystone\Domains\Family\Models\FamilyModel;
use RefactorCircus\Keystone\Domains\Owner\Concerns\AssignsOwners;
use RefactorCircus\Keystone\Domains\Product\Concerns\WritesProducts;
use RefactorCircus\Keystone\Domains\Product\Events\ProductCreatedActionEvent;
use RefactorCircus\Keystone\Domains\Product\Events\ProductCreatingActionEvent;
use RefactorCircus\Keystone\Domains\Product\Models\ProductModel;
use RefactorCircus\Keystone\Domains\ProductModel\Models\ProductModelModel;
use RefactorCircus\Keystone\Domains\Search\Services\ProductIndex;
use RefactorCircus\Keystone\Domains\Workflow\Services\Versions;

final class CreateProductAction
{
    use AssignsCategories;
    use AssignsOwners;
    use WritesProducts;

    /**
     * `values` in standard shape: `{"name": [{"locale": null, "scope": null, "data": "Tee"}]}`.
     * A variant product names its `parent` product model and takes its family.
     *
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'identifier' => ['required', 'string', 'max:191', 'regex:/^[A-Za-z0-9][A-Za-z0-9_.-]*$/', 'unique:keystone_products,identifier'],
            'family' => ['sometimes', 'nullable', 'string', 'exists:keystone_families,code'],
            'parent' => ['sometimes', 'nullable', 'string', 'exists:keystone_product_models,code'],
            'owner' => ['sometimes', 'nullable', 'string', 'exists:keystone_owners,code'],
            'enabled' => ['sometimes', 'boolean'],
            'values' => ['sometimes', 'nullable', 'array'],
        ] + self::categoryRules() + Associations::rules();
    }

    public function __construct(
        private readonly UniqueValues $unique,
        private readonly ProductIndex $index,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function execute(array $data): ProductModel
    {
        ProductCreatingActionEvent::dispatch($data);

        $result = $this->perform($data);

        ProductCreatedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(array $data): ProductModel
    {
        return DB::transaction(function () use ($data): ProductModel {
            $parent = is_string($data['parent'] ?? null)
                ? ProductModelModel::query()->with('familyVariant.family.familyAttributes')->where('code', $data['parent'])->firstOrFail()
                : null;

            $family = is_string($data['family'] ?? null)
                ? FamilyModel::query()->where('code', $data['family'])->firstOrFail()
                : null;

            if ($parent !== null) {
                if (! $parent->holdsProducts()) {
                    throw ValidationException::withMessages([
                        'parent' => sprintf('Product model "%s" holds sub-models, not products. Choose one of its sub-models.', $parent->code),
                    ]);
                }

                $variantFamily = $parent->familyVariant->family;

                if ($family !== null && ! $family->is($variantFamily)) {
                    throw ValidationException::withMessages([
                        'family' => sprintf('A variant product belongs to its family variant\'s family, "%s".', $variantFamily->code),
                    ]);
                }

                $family = $variantFamily;

                if (is_string($data['owner'] ?? null)) {
                    $this->refuseInheritedOwner('variant product');
                }
            }

            $owner = $parent === null ? $this->ownerFor($data['owner'] ?? null) : null;

            [$settable, $why] = $this->settable($family, $parent);

            $values = $this->patchValues([], $data['values'] ?? null, $settable, $why);

            if ($parent !== null) {
                $this->checkVariantAxes($parent, $values);
            }

            $product = new ProductModel([
                'identifier' => $data['identifier'],
                'enabled' => (bool) ($data['enabled'] ?? true),
                'values' => $values,
            ]);
            $product->family()->associate($family);
            $product->parent()->associate($parent);
            $product->owner()->associate($owner);
            $product->save();

            $this->assignCategories($product, $data);
            app(Associations::class)->write($product, $data);
            $this->unique->sync($product);
            app(Versions::class)->record($product, 'created');
            $this->index->queue([$product->id]);

            return $product->load(['family', 'owner', 'categories', 'parent.parent', 'completeness.channel', 'completeness.locale', ...Associations::eagerLoads()]);
        });
    }
}
