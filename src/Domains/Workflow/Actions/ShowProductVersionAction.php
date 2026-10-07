<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Workflow\Actions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use JayI\Keystone\Domains\Product\Models\ProductModel;
use JayI\Keystone\Domains\Workflow\Events\ProductVersionShowingActionEvent;
use JayI\Keystone\Domains\Workflow\Events\ProductVersionShownActionEvent;
use JayI\Keystone\Domains\Workflow\Models\VersionModel;
use JayI\Keystone\Domains\Workflow\Services\Versions;

final class ShowProductVersionAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function __construct(private readonly Versions $versions) {}

    /**
     * A version by number, or `published` for the one storefronts read, or
     * `latest`.
     *
     * @throws ModelNotFoundException<VersionModel>
     */
    public function execute(ProductModel $product, string $version): VersionModel
    {
        ProductVersionShowingActionEvent::dispatch($product, $version);

        $result = $this->perform($product, $version);

        ProductVersionShownActionEvent::dispatch($product, $result);

        return $result;
    }

    private function perform(ProductModel $product, string $version): VersionModel
    {
        $found = match (true) {
            $version === 'latest' => $this->versions->latest($product),
            $version === 'published' => $product->published_version === null ? null : $this->versions->find($product, $product->published_version),
            ctype_digit($version) => $this->versions->find($product, (int) $version),
            default => null,
        };

        if ($found === null) {
            throw (new ModelNotFoundException)->setModel(VersionModel::class, [$version]);
        }

        return $found->load('author');
    }
}
