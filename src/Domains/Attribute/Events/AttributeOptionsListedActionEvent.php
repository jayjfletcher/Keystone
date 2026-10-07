<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Events;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionFinishedEvent;
use JayI\Keystone\Domains\Attribute\Models\AttributeModel;
use JayI\Keystone\Domains\Attribute\Models\AttributeOptionModel;

/**
 * The options of an attribute were listed.
 */
final class AttributeOptionsListedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  CursorPaginator<int, AttributeOptionModel>  $options
     */
    public function __construct(
        public AttributeModel $attribute,
        public CursorPaginator $options,
    ) {}
}
