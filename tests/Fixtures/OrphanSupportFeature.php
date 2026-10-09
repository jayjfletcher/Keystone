<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Tests\Fixtures;

use RefactorCircus\Showroom\Tests\Fixtures\Missing\AbsentLayeredFeature;

/**
 * A feature whose parent class is not installed, as ShowroomSupportFeature is
 * without refactor-circus/pennantplus: loading it fails rather than answering false.
 */
class OrphanSupportFeature extends AbsentLayeredFeature {}
