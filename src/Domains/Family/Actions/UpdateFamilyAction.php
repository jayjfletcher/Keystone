<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use JayI\Keystone\Domains\Family\Concerns\WritesFamilyAttributes;
use JayI\Keystone\Domains\Family\Events\FamilyUpdatedActionEvent;
use JayI\Keystone\Domains\Family\Events\FamilyUpdatingActionEvent;
use JayI\Keystone\Domains\Family\Models\FamilyModel;
use JayI\Keystone\Domains\Product\Models\ProductModel;
use JayI\Keystone\Domains\Search\Services\ProductIndex;

final class UpdateFamilyAction
{
    use WritesFamilyAttributes;

    /**
     * `attributes`, when given, replaces the family's whole membership.
     *
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            // A code is an identifier other systems hold on to; it never changes.
            'code' => ['prohibited'],
        ] + self::familyRules();
    }

    public function __construct(private readonly ProductIndex $index) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function execute(FamilyModel $family, array $data): FamilyModel
    {
        FamilyUpdatingActionEvent::dispatch($family, $data);

        $result = $this->perform($family, $data);

        FamilyUpdatedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(FamilyModel $family, array $data): FamilyModel
    {
        return DB::transaction(function () use ($family, $data): FamilyModel {
            if (array_key_exists('labels', $data)) {
                $family->labels = is_array($data['labels']) ? $data['labels'] : [];
            }

            if (array_key_exists('sort_order', $data)) {
                $family->sort_order = (int) $data['sort_order'];
            }

            if (array_key_exists('attributes', $data)) {
                $members = is_array($data['attributes']) ? $data['attributes'] : [];

                $this->keepVariantAttributes($family, $members);
                $this->replaceAttributes($family, $members);
            }

            // The label must still belong to the family after its membership
            // changed, so it is checked again whenever either moves.
            if (array_key_exists('label_attribute', $data)) {
                $this->assignLabelAttribute($family, is_string($data['label_attribute']) ? $data['label_attribute'] : null);
            } elseif (array_key_exists('attributes', $data) && $family->label_attribute_id !== null) {
                $this->assignLabelAttribute($family, $family->labelAttribute?->code);
            }

            $family->save();

            // Requirements feed every product's completeness.
            if (array_key_exists('attributes', $data)) {
                $this->index->queueQuery(ProductModel::query()->where('family_id', $family->id));
            }

            return $family->load(['labelAttribute', 'familyAttributes']);
        });
    }
}
