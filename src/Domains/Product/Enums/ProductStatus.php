<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Product\Enums;

use RefactorCircus\Keystone\Atrium\Badges;

/**
 * Where a product's working copy stands in review. Publishing is separate:
 * a product has a published version or it has not.
 */
enum ProductStatus: string
{
    case Draft = 'draft';
    case InReview = 'in_review';
    case Approved = 'approved';
    case Archived = 'archived';

    /**
     * The Atrium colour the status is shown with, from Keystone's one
     * mapping of statuses to colours.
     */
    public function badge(): string
    {
        return Badges::forStatus($this);
    }
}
