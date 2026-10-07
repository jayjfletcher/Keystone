<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Owner\Actions;

use Illuminate\Support\Facades\DB;
use JayI\Keystone\Domains\Owner\Concerns\WritesOwnerTypes;
use JayI\Keystone\Domains\Owner\Events\OwnerTypeUpdatedActionEvent;
use JayI\Keystone\Domains\Owner\Events\OwnerTypeUpdatingActionEvent;
use JayI\Keystone\Domains\Owner\Models\OwnerTypeModel;

final class UpdateOwnerTypeAction
{
    use WritesOwnerTypes;

    /**
     * Rules apply to later writes; owners already placed stay where they are.
     *
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'code' => ['prohibited'],
        ] + self::typeRules();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(OwnerTypeModel $ownerType, array $data): OwnerTypeModel
    {
        OwnerTypeUpdatingActionEvent::dispatch($ownerType, $data);

        $result = $this->perform($ownerType, $data);

        OwnerTypeUpdatedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(OwnerTypeModel $ownerType, array $data): OwnerTypeModel
    {
        return DB::transaction(function () use ($ownerType, $data): OwnerTypeModel {
            if (array_key_exists('labels', $data)) {
                $ownerType->labels = is_array($data['labels']) ? $data['labels'] : [];
            }

            foreach (['can_be_root', 'owns_products'] as $flag) {
                if (array_key_exists($flag, $data)) {
                    $ownerType->setAttribute($flag, (bool) $data[$flag]);
                }
            }

            if (array_key_exists('sort_order', $data)) {
                $ownerType->sort_order = (int) $data['sort_order'];
            }

            $ownerType->save();

            if (array_key_exists('parent_types', $data)) {
                /** @var array<int, string>|null $parents */
                $parents = $data['parent_types'];

                $this->setParentTypes($ownerType, $parents);
            }

            return $ownerType->load('parentTypes');
        });
    }
}
