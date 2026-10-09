<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Owner\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RefactorCircus\Keystone\Domains\Owner\Concerns\PlacesOwners;
use RefactorCircus\Keystone\Domains\Owner\Events\OwnerCreatedActionEvent;
use RefactorCircus\Keystone\Domains\Owner\Events\OwnerCreatingActionEvent;
use RefactorCircus\Keystone\Domains\Owner\Models\OwnerModel;
use RefactorCircus\Keystone\Domains\Owner\Models\OwnerTypeModel;

final class CreateOwnerAction
{
    use PlacesOwners;

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:191', 'regex:/^[A-Za-z0-9][A-Za-z0-9_.-]*$/', 'unique:keystone_owners,code'],
            'type' => ['required', 'string', 'exists:keystone_owner_types,code'],
            'parent' => ['sometimes', 'nullable', 'string', 'exists:keystone_owners,code'],
            'labels' => ['sometimes', 'nullable', 'array'],
            'labels.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function execute(array $data): OwnerModel
    {
        OwnerCreatingActionEvent::dispatch($data);

        $result = $this->perform($data);

        OwnerCreatedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(array $data): OwnerModel
    {
        return DB::transaction(function () use ($data): OwnerModel {
            $type = OwnerTypeModel::query()->with('parentTypes')->where('code', $data['type'])->firstOrFail();
            $parent = is_string($data['parent'] ?? null) ? OwnerModel::query()->where('code', $data['parent'])->firstOrFail() : null;

            $owner = new OwnerModel([
                'code' => $data['code'],
                'labels' => $data['labels'] ?? [],
                'path' => '',
            ]);
            $owner->type()->associate($type);

            $this->checkPlacement($owner, $type, $parent);

            // The path holds the owner's own id, which exists once saved.
            $owner->save();
            $this->place($owner, $parent);

            return $owner->load(['type', 'parent']);
        });
    }
}
