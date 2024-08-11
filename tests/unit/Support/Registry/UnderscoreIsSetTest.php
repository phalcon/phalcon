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
use Phalcon\Tests\AbstractUnitTestCase;

final class UnderscoreIsSetTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Phalcon\Support\Registry :: __isset()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-05-25
     */
    public function testSupportRegistryUnderscoreIsSet(): void
    {
        $data = [
            'one'   => 'two',
            'three' => 'four',
            'five'  => 'six',
        ];

        $registry = new Registry($data);

        $this->assertTrue(
            isset($registry->three)
        );

        $this->assertFalse(
            isset($registry->unknown)
        );
    }
}
