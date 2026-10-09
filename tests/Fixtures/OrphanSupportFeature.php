<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Tests\Fixtures;

use RefactorCircus\Keystone\Tests\Fixtures\Missing\AbsentLayeredFeature;

/**
 * A feature whose parent class is not installed, as KeystoneSupportFeature is
 * without refactor-circus/pennantplus: loading it fails rather than answering false.
 */
class OrphanSupportFeature extends AbsentLayeredFeature {}
