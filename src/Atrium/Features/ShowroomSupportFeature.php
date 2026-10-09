<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Atrium\Features;

use RefactorCircus\PennantPlus\Domains\Feature\Support\OnLayeredFeature;

/**
 * Switches Showroom in Atrium on and off: its navigation, widgets, settings,
 * search and pages. On until its global value is set. The `SupportFeature`
 * suffix matches PennantPlus's default `gate.global_only` pattern, so only
 * the global value counts and per-user access stays with Showroom's policies.
 *
 * Needs refactor-circus/pennantplus. Point `showroom.atrium.features` at a subclass to
 * change the default, or at your own feature instead.
 */
class ShowroomSupportFeature extends OnLayeredFeature {}
