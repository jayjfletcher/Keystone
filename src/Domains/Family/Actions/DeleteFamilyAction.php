<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Actions;

use Illuminate\Database\Eloquent\Builder;
use JayI\Keystone\Domains\Family\Events\FamilyDeletedActionEvent;
use JayI\Keystone\Domains\Family\Events\FamilyDeletingActionEvent;
use JayI\Keystone\Domains\Family\Models\FamilyModel;
use JayI\Keystone\Domains\ProductModel\Models\ProductModelModel;
use JayI\Keystone\Exceptions\ModelInUseException;

final class DeleteFamilyAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(FamilyModel $family): FamilyModel
    {
        FamilyDeletingActionEvent::dispatch($family);

        $result = $this->perform($family);

        FamilyDeletedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * Membership rows and unused family variants go with the family; the
     * foreign keys cascade. A family with products is refused.
     */
    private function perform(FamilyModel $family): FamilyModel
    {
        $products = $family->products()->count();
        $models = ProductModelModel::query()
            ->whereHas('familyVariant', fn (Builder $variants): Builder => $variants->where('family_id', $family->id))
            ->count();

        if ($products > 0 || $models > 0) {
            throw ModelInUseException::familyHasProducts($family->code, $products, $models);
        }

        $family->delete();

        return $family;
    }
}
