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

final class GetSetTargetPathTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Assets\Collection :: getTargetPath() / setTargetPath()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testAssetsCollectionGetSetTargetPath(): void
    {
        $collection = new Collection();
        $targetPath = '/assets';
        $collection->setTargetPath($targetPath);

        $this->assertSame($targetPath, $collection->getTargetPath());
    }
}
