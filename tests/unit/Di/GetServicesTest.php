<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Di;

use Phalcon\Di\Di;
use stdClass;
use Phalcon\Tests\UnitTestCase;

class GetServicesTest extends UnitTestCase
{
    /**
     * Unit Tests Phalcon\Di :: getServices()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-09-09
     */
    public function testDiGetServices(): void
    {
        $container = new Di();

        $expected = [];
        $actual   = $container->getServices();
        $this->assertSame($expected, $actual);

        $container->set('service', stdClass::class);

        $count  = 1;
        $actual = $container->getServices();
        $this->assertCount($count, $actual);

        $container->remove('service');

        $expected = [];
        $actual   = $container->getServices();
        $this->assertSame($expected, $actual);
    }
}
