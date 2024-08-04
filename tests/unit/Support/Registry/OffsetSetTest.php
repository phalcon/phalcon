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

namespace Phalcon\Tests\Unit\Support\Registry;

use Phalcon\Support\Registry;
use Phalcon\Tests\UnitTestCase;

final class OffsetSetTest extends UnitTestCase
{
    /**
     * Unit Tests Phalcon\Support\Registry :: offsetSet()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-05-25
     */
    public function testSupportRegistryOffsetSet(): void
    {
        $registry = new Registry();


        $registry->offsetSet('three', 123);

        $this->assertSame(
            123,
            $registry->get('three')
        );


        $registry['three'] = 456;

        $this->assertSame(
            456,
            $registry->get('three')
        );
    }
}
