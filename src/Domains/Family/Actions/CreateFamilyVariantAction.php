<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Family\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RefactorCircus\Showroom\Domains\Family\Concerns\WritesFamilyVariantLevels;
use RefactorCircus\Showroom\Domains\Family\Events\FamilyVariantCreatedActionEvent;
use RefactorCircus\Showroom\Domains\Family\Events\FamilyVariantCreatingActionEvent;
use RefactorCircus\Showroom\Domains\Family\Models\FamilyModel;
use RefactorCircus\Showroom\Domains\Family\Models\FamilyVariantModel;

final class CreateFamilyVariantAction
{
    use WritesFamilyVariantLevels;

    /**
     * `levels` lists one or two levels, each with its axes and the other
     * attributes set there: `[{"axes": ["color"], "attributes": ["image"]}]`.
     *
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/', 'unique:showroom_family_variants,code'],
            'family' => ['required', 'string', 'exists:showroom_families,code'],
            'labels' => ['sometimes', 'nullable', 'array'],
            'labels.*' => ['nullable', 'string', 'max:255'],
        ] + self::levelRules(required: true);
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function execute(array $data): FamilyVariantModel
    {
        FamilyVariantCreatingActionEvent::dispatch($data);

        $result = $this->perform($data);

        FamilyVariantCreatedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(array $data): FamilyVariantModel
    {
        return DB::transaction(function () use ($data): FamilyVariantModel {
            $family = FamilyModel::query()->where('code', $data['family'])->firstOrFail();

            $variant = new FamilyVariantModel([
                'code' => $data['code'],
                'labels' => $data['labels'] ?? [],
                'levels' => 1,
            ]);
            $variant->family()->associate($family);
            $variant->save();

            /** @var array<int, array{axes: array<int, string>, attributes?: array<int, string>}> $levels */
            $levels = $data['levels'];

            $this->placeLevels($variant, $levels);

            return $variant->load(['family.familyAttributes', 'variantAttributes']);
        });
    }
}
