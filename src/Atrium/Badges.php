<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Atrium;

use RefactorCircus\Showroom\Domains\Product\Enums\ProductStatus;

/**
 * The Atrium colour of every status Showroom shows, as a status dot, in one
 * place. Pending - awaiting someone's decision - has its own colour, `info`,
 * used by no other status: a product in review, an import or export queued or
 * waiting. `success` is done or live, `danger` failed, `primary` in progress
 * and `neutral` over or switched off.
 */
final class Badges
{
    /**
     * A product's place in review.
     */
    public static function forStatus(ProductStatus $status): string
    {
        return match ($status) {
            ProductStatus::InReview => 'info',
            ProductStatus::Approved => 'success',
            ProductStatus::Draft => 'primary',
            ProductStatus::Archived => 'neutral',
        };
    }

    /**
     * Whether a product is enabled.
     */
    public static function forEnabled(bool $enabled): string
    {
        return $enabled ? 'success' : 'neutral';
    }

    /**
     * A product with a published version is live.
     */
    public static function forPublished(): string
    {
        return 'success';
    }

    /**
     * An Impex import or export run, by its status value, so Showroom needs
     * no Impex class to colour it.
     */
    public static function forRun(string $status): string
    {
        return match ($status) {
            'pending', 'waiting' => 'info',
            'running', 'rolling_back' => 'primary',
            'completed' => 'success',
            'failed' => 'danger',
            default => 'neutral',
        };
    }
}
