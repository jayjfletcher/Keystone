<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Family\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RefactorCircus\Showroom\Domains\Family\Concerns\WritesFamilyVariantLevels;
use RefactorCircus\Showroom\Domains\Family\Events\FamilyVariantUpdatedActionEvent;
use RefactorCircus\Showroom\Domains\Family\Events\FamilyVariantUpdatingActionEvent;
use RefactorCircus\Showroom\Domains\Family\Models\FamilyVariantModel;

final class UpdateFamilyVariantAction
{
    use WritesFamilyVariantLevels;

    /**
     * `levels` can change only while no product model uses the variant.
     *
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'code' => ['prohibited'],
            'family' => ['prohibited'],
            'labels' => ['sometimes', 'nullable', 'array'],
            'labels.*' => ['nullable', 'string', 'max:255'],
        ] + self::levelRules(required: false);
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function execute(FamilyVariantModel $familyVariant, array $data): FamilyVariantModel
    {
        FamilyVariantUpdatingActionEvent::dispatch($familyVariant, $data);

        $result = $this->perform($familyVariant, $data);

        FamilyVariantUpdatedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(FamilyVariantModel $familyVariant, array $data): FamilyVariantModel
    {
        return DB::transaction(function () use ($familyVariant, $data): FamilyVariantModel {
            if (array_key_exists('labels', $data)) {
                $familyVariant->labels = is_array($data['labels']) ? $data['labels'] : [];
                $familyVariant->save();
            }

            if (array_key_exists('levels', $data)) {
                // Stored values sit on the levels they were set at; moving an
                // attribute would strand them.
                if ($familyVariant->productModels()->exists()) {
                    throw ValidationException::withMessages([
                        'levels' => sprintf('Family variant "%s" has product models, so its levels cannot change.', $familyVariant->code),
                    ]);
                }

                /** @var array<int, array{axes: array<int, string>, attributes?: array<int, string>}> $levels */
                $levels = $data['levels'];

                $this->placeLevels($familyVariant, $levels);
            }

            return $familyVariant->load(['family.familyAttributes', 'variantAttributes']);
        });
    }
}
