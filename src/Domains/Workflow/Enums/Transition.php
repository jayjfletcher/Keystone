<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Workflow\Enums;

use RefactorCircus\Keystone\Domains\Product\Enums\ProductStatus;

/**
 * A step through the product workflow, and the version action it records.
 */
enum Transition: string
{
    case Submit = 'submit';
    case Approve = 'approve';
    case Reject = 'reject';
    case Publish = 'publish';
    case Unpublish = 'unpublish';
    case Archive = 'archive';
    case Restore = 'restore';

    /**
     * The statuses the transition starts from; `require_approval` off lets a
     * product publish from any status but archived.
     *
     * @return array<int, ProductStatus>
     */
    public function startsFrom(bool $requireApproval = true): array
    {
        return match ($this) {
            self::Submit => [ProductStatus::Draft],
            self::Approve, self::Reject => [ProductStatus::InReview],
            self::Publish => $requireApproval
                ? [ProductStatus::Approved]
                : [ProductStatus::Draft, ProductStatus::InReview, ProductStatus::Approved],
            self::Unpublish => [ProductStatus::Draft, ProductStatus::InReview, ProductStatus::Approved],
            self::Archive => [ProductStatus::Draft, ProductStatus::InReview, ProductStatus::Approved],
            self::Restore => [ProductStatus::Archived],
        };
    }

    /**
     * The status the product ends in; null keeps it.
     */
    public function to(): ?ProductStatus
    {
        return match ($this) {
            self::Submit => ProductStatus::InReview,
            self::Approve => ProductStatus::Approved,
            self::Reject, self::Restore => ProductStatus::Draft,
            self::Archive => ProductStatus::Archived,
            self::Publish, self::Unpublish => null,
        };
    }

    /**
     * The action a version records for the transition.
     */
    public function action(): string
    {
        return match ($this) {
            self::Submit => 'submitted',
            self::Approve => 'approved',
            self::Reject => 'rejected',
            self::Publish => 'published',
            self::Unpublish => 'unpublished',
            self::Archive => 'archived',
            self::Restore => 'restored',
        };
    }
}
