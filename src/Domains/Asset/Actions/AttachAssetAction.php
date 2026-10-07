<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Asset\Actions;

use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use JayI\Keystone\Domains\Asset\Events\AssetAttachedActionEvent;
use JayI\Keystone\Domains\Asset\Events\AssetAttachingActionEvent;
use JayI\Keystone\Domains\Asset\Models\AssetModel;
use JayI\Keystone\Domains\Asset\Services\AssetLinks;

final class AttachAssetAction
{
    /**
     * Link the asset to a product (by identifier), a product model or an
     * owner (by code), under a role. Attaching again updates the order.
     *
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(array_keys(AssetLinks::TYPES))],
            'target' => ['required', 'string', 'max:191'],
            'role' => ['sometimes', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
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
        AssetAttachingActionEvent::dispatch($asset, $data);

        $result = $this->perform($asset, $data);

        AssetAttachedActionEvent::dispatch($result, $data);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(AssetModel $asset, array $data): AssetModel
    {
        $record = $this->links->resolve((string) $data['type'], (string) $data['target']);

        $this->links->attach(
            $asset,
            $record,
            is_string($data['role'] ?? null) ? $data['role'] : 'media',
            isset($data['sort_order']) ? (int) $data['sort_order'] : 0,
        );

        return $asset->load(['products', 'productModels', 'owners']);
    }
}
