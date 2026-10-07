<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Asset\Actions;

use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use JayI\Keystone\Domains\Asset\Events\AssetDetachedActionEvent;
use JayI\Keystone\Domains\Asset\Events\AssetDetachingActionEvent;
use JayI\Keystone\Domains\Asset\Models\AssetModel;
use JayI\Keystone\Domains\Asset\Services\AssetLinks;

final class DetachAssetAction
{
    /**
     * Without `role`, the asset is unlinked from the record in every role.
     *
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(array_keys(AssetLinks::TYPES))],
            'target' => ['required', 'string', 'max:191'],
            'role' => ['sometimes', 'nullable', 'string', 'max:100'],
        ];
    }

    public function __construct(private readonly AssetLinks $links) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function execute(AssetModel $asset, array $data): AssetModel
    {
        AssetDetachingActionEvent::dispatch($asset, $data);

        $result = $this->perform($asset, $data);

        AssetDetachedActionEvent::dispatch($result, $data);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(AssetModel $asset, array $data): AssetModel
    {
        $record = $this->links->resolve((string) $data['type'], (string) $data['target']);

        $this->links->detach($asset, $record, is_string($data['role'] ?? null) ? $data['role'] : null);

        return $asset->load(['products', 'productModels', 'owners']);
    }
}
