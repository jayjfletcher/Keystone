<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Association\Actions;

use RefactorCircus\Keystone\Domains\Association\Events\AssociationTypeUpdatedActionEvent;
use RefactorCircus\Keystone\Domains\Association\Events\AssociationTypeUpdatingActionEvent;
use RefactorCircus\Keystone\Domains\Association\Models\AssociationTypeModel;

final class UpdateAssociationTypeAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            // Existing associations are shaped by these; they never change.
            'code' => ['prohibited'],
            'is_two_way' => ['prohibited'],
            'is_quantified' => ['prohibited'],
            'labels' => ['sometimes', 'nullable', 'array'],
            'labels.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(AssociationTypeModel $associationType, array $data): AssociationTypeModel
    {
        AssociationTypeUpdatingActionEvent::dispatch($associationType, $data);

        $result = $this->perform($associationType, $data);

        AssociationTypeUpdatedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(AssociationTypeModel $associationType, array $data): AssociationTypeModel
    {
        if (array_key_exists('labels', $data)) {
            $associationType->labels = is_array($data['labels']) ? $data['labels'] : [];
            $associationType->save();
        }

        return $associationType;
    }
}
