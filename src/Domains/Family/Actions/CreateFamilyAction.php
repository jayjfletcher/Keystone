<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Family\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RefactorCircus\Keystone\Domains\Family\Concerns\WritesFamilyAttributes;
use RefactorCircus\Keystone\Domains\Family\Events\FamilyCreatedActionEvent;
use RefactorCircus\Keystone\Domains\Family\Events\FamilyCreatingActionEvent;
use RefactorCircus\Keystone\Domains\Family\Models\FamilyModel;

final class CreateFamilyAction
{
    use WritesFamilyAttributes;

    /**
     * `attributes` is the family's whole membership:
     * `[{"attribute": "color", "is_required": true}]`.
     *
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/', 'unique:keystone_families,code'],
        ] + self::familyRules();
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function execute(array $data): FamilyModel
    {
        FamilyCreatingActionEvent::dispatch($data);

        $result = $this->perform($data);

        FamilyCreatedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(array $data): FamilyModel
    {
        return DB::transaction(function () use ($data): FamilyModel {
            $family = FamilyModel::query()->create([
                'code' => $data['code'],
                'labels' => $data['labels'] ?? [],
                'sort_order' => $data['sort_order'] ?? 0,
            ]);

            $this->replaceAttributes($family, is_array($data['attributes'] ?? null) ? $data['attributes'] : []);

            if (is_string($data['label_attribute'] ?? null)) {
                $this->assignLabelAttribute($family, $data['label_attribute']);
                $family->save();
            }

            return $family->load(['labelAttribute', 'familyAttributes']);
        });
    }
}
