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

final class GetSetTargetIsLocalTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Assets\Collection :: getTargetIsLocal()/setTargetIsLocal()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testAssetsCollectionGetSetTargetIsLocal(): void
    {
        $collection = new Collection();
        $this->assertSame(true, $collection->getTargetIsLocal());

        $collection->setTargetIsLocal(false);
        $this->assertSame(false, $collection->getTargetIsLocal());
    }
}
