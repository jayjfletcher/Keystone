<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Association\Actions;

use JayI\Keystone\Domains\Association\Events\AssociationTypeCreatedActionEvent;
use JayI\Keystone\Domains\Association\Events\AssociationTypeCreatingActionEvent;
use JayI\Keystone\Domains\Association\Models\AssociationTypeModel;

final class CreateAssociationTypeAction
{
    /**
     * `is_two_way` and `is_quantified` are fixed once created, and a type is
     * not both: a quantity only makes sense in one direction.
     *
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/', 'unique:keystone_association_types,code'],
            'labels' => ['sometimes', 'nullable', 'array'],
            'labels.*' => ['nullable', 'string', 'max:255'],
            'is_two_way' => ['sometimes', 'boolean'],
            'is_quantified' => ['sometimes', 'boolean', 'declined_if:is_two_way,true,1'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): AssociationTypeModel
    {
        AssociationTypeCreatingActionEvent::dispatch($data);

        $result = $this->perform($data);

        AssociationTypeCreatedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(array $data): AssociationTypeModel
    {
        return AssociationTypeModel::query()->create([
            'code' => $data['code'],
            'labels' => $data['labels'] ?? [],
            'is_two_way' => (bool) ($data['is_two_way'] ?? false),
            'is_quantified' => (bool) ($data['is_quantified'] ?? false),
        ]);
    }
}
