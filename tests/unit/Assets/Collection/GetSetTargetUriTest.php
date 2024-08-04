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

final class GetSetTargetUriTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Assets\Collection :: getTargetUri() / setTargetUri()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testAssetsCollectionGetTargetUri(): void
    {
        $collection = new Collection();
        $targetUri  = 'dist';
        $collection->setTargetUri($targetUri);

        $this->assertSame($targetUri, $collection->getTargetUri());
    }
}
