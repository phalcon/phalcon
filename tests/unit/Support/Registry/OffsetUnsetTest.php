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

final class OffsetUnsetTest extends UnitTestCase
{
    /**
     * Unit Tests Phalcon\Support\Registry :: offsetUnset()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-05-25
     */
    public function testSupportRegistryOffsetUnset(): void
    {
        $data = [
            'one'   => 'two',
            'three' => 'four',
            'five'  => 'six',
        ];

        $registry = new Registry($data);


        unset($registry['five']);

        $this->assertSame(
            [
                'one'   => 'two',
                'three' => 'four',
            ],
            $registry->toArray()
        );


        $registry->offsetUnset('one');

        $this->assertSame(
            [
                'three' => 'four',
            ],
            $registry->toArray()
        );
    }
}
