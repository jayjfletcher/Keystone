<?php

declare(strict_types=1);

namespace JayI\Keystone\Tests\Fixtures;

use JayI\Keystone\Tests\Fixtures\Missing\AbsentLayeredFeature;

/**
 * A feature whose parent class is not installed, as KeystoneSupportFeature is
 * without jayi/pennantplus: loading it fails rather than answering false.
 */
class OrphanSupportFeature extends AbsentLayeredFeature {}
