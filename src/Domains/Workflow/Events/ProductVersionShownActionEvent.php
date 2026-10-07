<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Workflow\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionFinishedEvent;
use JayI\Keystone\Domains\Product\Models\ProductModel;
use JayI\Keystone\Domains\Workflow\Models\VersionModel;

/**
 * A product version was shown.
 */
final class ProductVersionShownActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public ProductModel $product,
        public VersionModel $version,
    ) {}
}
