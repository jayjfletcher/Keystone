<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Owner\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RefactorCircus\Keystone\Domains\Owner\Concerns\PlacesOwners;
use RefactorCircus\Keystone\Domains\Owner\Events\OwnerUpdatedActionEvent;
use RefactorCircus\Keystone\Domains\Owner\Events\OwnerUpdatingActionEvent;
use RefactorCircus\Keystone\Domains\Owner\Models\OwnerModel;
use RefactorCircus\Keystone\Domains\Search\Services\ProductIndex;

final class UpdateOwnerAction
{
    use PlacesOwners;

    /**
     * Sending `parent` moves the owner, with everything beneath it.
     *
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'code' => ['prohibited'],
            'type' => ['prohibited'],
            'parent' => ['sometimes', 'nullable', 'string', 'exists:keystone_owners,code'],
            'labels' => ['sometimes', 'nullable', 'array'],
            'labels.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function __construct(private readonly ProductIndex $index) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function execute(OwnerModel $owner, array $data): OwnerModel
    {
        OwnerUpdatingActionEvent::dispatch($owner, $data);

        $result = $this->perform($owner, $data);

        OwnerUpdatedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(OwnerModel $owner, array $data): OwnerModel
    {
        return DB::transaction(function () use ($owner, $data): OwnerModel {
            if (array_key_exists('labels', $data)) {
                $owner->labels = is_array($data['labels']) ? $data['labels'] : [];
                $owner->save();
            }

            if (array_key_exists('parent', $data)) {
                $parent = is_string($data['parent']) ? OwnerModel::query()->where('code', $data['parent'])->firstOrFail() : null;

                if ($parent?->id !== $owner->parent_id) {
                    $type = $owner->type()->with('parentTypes')->firstOrFail();

                    $this->checkPlacement($owner, $type, $parent);
                    $this->place($owner, $parent);

                    // Every product beneath now sits in a different chain.
                    $this->index->queue($this->index->productIdsOwnedWithin($owner));
                }
            }

            return $owner->load(['type', 'parent']);
        });
    }
}
