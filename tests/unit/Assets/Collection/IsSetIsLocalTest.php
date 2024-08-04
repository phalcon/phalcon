<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Assets\Collection;

use Phalcon\Assets\Collection;
use Phalcon\Tests\UnitTestCase;

final class IsSetIsLocalTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Assets\Collection :: isLocal() / setIsLocal()
     *
     * @author Jeremy PASTOURET <https://github.com/jenovateurs>
     * @since  2020-02-15
     */
    public function testAssetsCollectionIsSetLocal(): void
    {
        $collection = new Collection();
        $this->assertTrue($collection->isLocal());

        $collection->setIsLocal(false);
        $this->assertFalse($collection->isLocal());
    }
}
