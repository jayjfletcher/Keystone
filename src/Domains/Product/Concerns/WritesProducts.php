<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Product\Concerns;

use Illuminate\Support\Collection;
use JayI\Keystone\Domains\Attribute\Concerns\WritesValues;
use JayI\Keystone\Domains\Attribute\Models\AttributeModel;
use JayI\Keystone\Domains\Family\Models\FamilyModel;
use JayI\Keystone\Domains\Product\Models\ProductModel;
use JayI\Keystone\Domains\ProductModel\Models\ProductModelModel;

/**
 * Which attributes a product may set, shared by create and update.
 */
trait WritesProducts
{
    use WritesValues;

    /**
     * A variant sets the last level of its family variant; a product with a
     * family sets that family's attributes; one without, any attribute.
     *
     * @return array{0: Collection<int, AttributeModel>|null, 1: string}
     */
    private function settable(?FamilyModel $family, ?ProductModelModel $parent): array
    {
        if ($parent !== null) {
            $variant = $parent->familyVariant;

            return [
                $variant->attributesAt($variant->levels),
                sprintf('is not set on variant products of family variant "%s".', $variant->code),
            ];
        }

        if ($family !== null) {
            return [
                $family->familyAttributes()->get(),
                sprintf('is not in family "%s".', $family->code),
            ];
        }

        return [null, ''];
    }

    /**
     * @param  array<string, array<string, array<string, mixed>>>  $values
     */
    private function checkVariantAxes(ProductModelModel $parent, array $values, ?ProductModel $self = null): void
    {
        $variant = $parent->familyVariant;

        $siblings = ProductModel::query()
            ->where('parent_id', $parent->id)
            ->when($self !== null, fn ($query) => $query->whereKeyNot($self?->getKey()))
            ->get()
            ->map(fn (ProductModel $sibling): array => $sibling->ownValues());

        $this->checkAxes($variant, $variant->levels, $values, $siblings);
    }
}
