<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Atrium\Features;

use RefactorCircus\PennantPlus\Domains\Feature\Support\OnLayeredFeature;

/**
 * Switches Keystone in Atrium on and off: its navigation, widgets, settings,
 * search and pages. On until its global value is set. The `SupportFeature`
 * suffix matches PennantPlus's default `gate.global_only` pattern, so only
 * the global value counts and per-user access stays with Keystone's policies.
 *
 * Needs refactor-circus/pennantplus. Point `keystone.atrium.features` at a subclass to
 * change the default, or at your own feature instead.
 */
class KeystoneSupportFeature extends OnLayeredFeature {}
