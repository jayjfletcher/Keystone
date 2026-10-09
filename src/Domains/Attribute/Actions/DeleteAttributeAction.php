<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Actions;

use Illuminate\Database\Eloquent\Builder;
use RefactorCircus\Keystone\Domains\Attribute\Events\AttributeDeletedActionEvent;
use RefactorCircus\Keystone\Domains\Attribute\Events\AttributeDeletingActionEvent;
use RefactorCircus\Keystone\Domains\Attribute\Exceptions\AttributeLabelsFamiliesException;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeModel;
use RefactorCircus\Keystone\Domains\Family\Models\FamilyModel;
use RefactorCircus\Keystone\Domains\Family\Models\FamilyVariantModel;
use RefactorCircus\Keystone\Exceptions\ModelInUseException;
use RefactorCircus\Keystone\Jobs\PurgeAttributeValues;

final class DeleteAttributeAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(AttributeModel $attribute): AttributeModel
    {
        AttributeDeletingActionEvent::dispatch($attribute);

        $result = $this->perform($attribute);

        AttributeDeletedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * Options and family memberships go with the attribute; the foreign keys
     * cascade, and stored values are purged by a queued job. An attribute that
     * labels a family or shapes a family variant is refused instead.
     */
    private function perform(AttributeModel $attribute): AttributeModel
    {
        /** @var array<int, string> $families */
        $families = FamilyModel::query()->where('label_attribute_id', $attribute->id)->orderBy('code')->pluck('code')->all();

        if ($families !== []) {
            throw AttributeLabelsFamiliesException::for($attribute, $families);
        }

        /** @var array<int, string> $variants */
        $variants = FamilyVariantModel::query()
            ->whereHas('variantAttributes', fn (Builder $attributes): Builder => $attributes->whereKey($attribute->id))
            ->orderBy('code')
            ->pluck('code')
            ->all();

        if ($variants !== []) {
            throw ModelInUseException::attributeInFamilyVariants($attribute->code, $variants);
        }

        $attribute->delete();

        // Stored values outlive the attribute until the purge reaches them.
        PurgeAttributeValues::dispatch($attribute->code)->afterCommit();

        return $attribute;
    }
}
