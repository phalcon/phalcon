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

final class InitTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Support\Registry :: init()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testSupportRegistryInit(): void
    {
        $data = [
            'one'   => 'two',
            'three' => 'four',
            'five'  => 'six',
        ];

        $registry = new Registry();

        $this->assertSame(
            0,
            $registry->count()
        );

        $registry->init($data);

        $this->assertSame(
            $data,
            $registry->toArray()
        );
    }
}
