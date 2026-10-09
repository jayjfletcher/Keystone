<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Owner\Actions;

use Illuminate\Support\Facades\DB;
use RefactorCircus\Keystone\Domains\Owner\Concerns\WritesOwnerTypes;
use RefactorCircus\Keystone\Domains\Owner\Events\OwnerTypeCreatedActionEvent;
use RefactorCircus\Keystone\Domains\Owner\Events\OwnerTypeCreatingActionEvent;
use RefactorCircus\Keystone\Domains\Owner\Models\OwnerTypeModel;

final class CreateOwnerTypeAction
{
    use WritesOwnerTypes;

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/', 'unique:keystone_owner_types,code'],
        ] + self::typeRules();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): OwnerTypeModel
    {
        OwnerTypeCreatingActionEvent::dispatch($data);

        $result = $this->perform($data);

        OwnerTypeCreatedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(array $data): OwnerTypeModel
    {
        return DB::transaction(function () use ($data): OwnerTypeModel {
            $type = OwnerTypeModel::query()->create([
                'code' => $data['code'],
                'labels' => $data['labels'] ?? [],
                'can_be_root' => (bool) ($data['can_be_root'] ?? true),
                'owns_products' => (bool) ($data['owns_products'] ?? true),
                'sort_order' => $data['sort_order'] ?? 0,
            ]);

            /** @var array<int, string>|null $parents */
            $parents = $data['parent_types'] ?? null;

            $this->setParentTypes($type, $parents);

            return $type->load('parentTypes');
        });
    }
}
