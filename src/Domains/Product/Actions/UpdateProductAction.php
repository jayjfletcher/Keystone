<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Product\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use JayI\Keystone\Domains\Association\Services\Associations;
use JayI\Keystone\Domains\Attribute\Services\UniqueValues;
use JayI\Keystone\Domains\Category\Concerns\AssignsCategories;
use JayI\Keystone\Domains\Family\Models\FamilyModel;
use JayI\Keystone\Domains\Owner\Concerns\AssignsOwners;
use JayI\Keystone\Domains\Product\Concerns\WritesProducts;
use JayI\Keystone\Domains\Product\Enums\ProductStatus;
use JayI\Keystone\Domains\Product\Events\ProductUpdatedActionEvent;
use JayI\Keystone\Domains\Product\Events\ProductUpdatingActionEvent;
use JayI\Keystone\Domains\Product\Models\ProductModel;
use JayI\Keystone\Domains\Search\Services\ProductIndex;
use JayI\Keystone\Domains\Workflow\Services\Versions;

final class UpdateProductAction
{
    use AssignsCategories;
    use AssignsOwners;
    use WritesProducts;

    /**
     * `values` patches only the slots sent; `data: null` clears one.
     *
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'identifier' => ['prohibited'],
            'parent' => ['prohibited'],
            'family' => ['sometimes', 'nullable', 'string', 'exists:keystone_families,code'],
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
    public function execute(ProductModel $product, array $data): ProductModel
    {
        ProductUpdatingActionEvent::dispatch($product, $data);

        $result = $this->perform($product, $data);

        ProductUpdatedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(ProductModel $product, array $data): ProductModel
    {
        return DB::transaction(function () use ($product, $data): ProductModel {
            $parent = $product->parent()->with('familyVariant.family.familyAttributes')->first();

            if (array_key_exists('family', $data)) {
                if ($parent !== null) {
                    throw ValidationException::withMessages([
                        'family' => 'A variant product\'s family comes from its family variant and cannot be changed.',
                    ]);
                }

                // Values outside the new family stay stored, as in any PIM,
                // and reappear if the product moves back.
                $product->family()->associate(is_string($data['family'])
                    ? FamilyModel::query()->where('code', $data['family'])->firstOrFail()
                    : null);
            }

            if (array_key_exists('owner', $data)) {
                if ($parent !== null) {
                    $this->refuseInheritedOwner('variant product');
                }

                $product->owner()->associate($this->ownerFor($data['owner']));
            }

            if (array_key_exists('enabled', $data)) {
                $product->enabled = (bool) $data['enabled'];
            }

            if (array_key_exists('values', $data)) {
                [$settable, $why] = $this->settable($product->family, $parent);

                $values = $this->patchValues($product->ownValues(), $data['values'], $settable, $why);

                if ($parent !== null) {
                    $this->checkVariantAxes($parent, $values, $product);
                }

                $product->values = $values;
            }

            $product->save();

            $this->assignCategories($product, $data);
            app(Associations::class)->write($product, $data);
            $this->unique->sync($product);

            // An approved product that changes needs review again; the
            // published version is untouched until the next publish.
            $versions = app(Versions::class);

            if ($product->status === ProductStatus::Approved && $versions->hasChanges($product)) {
                $product->status = ProductStatus::Draft;
                $product->save();
            }

            $versions->record($product, 'updated');
            $this->index->queue([$product->id]);

            return $product->load(['family', 'owner', 'categories', 'parent.parent', 'completeness.channel', 'completeness.locale', ...Associations::eagerLoads()]);
        });
    }
}
