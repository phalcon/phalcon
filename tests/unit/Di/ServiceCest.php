<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Tests\Unit\Di;

use Phalcon\Di\Di;
use Phalcon\Di\Exception;
use Phalcon\Escaper\Escaper;
use UnitTester;

class ServiceCest
{
    /**
     * Tests resolving service
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testResolvingService(UnitTester $I)
    {
        $container = new Di();

        $container->set(
            'resolved',
            function () {
                return new Escaper();
            }
        );

        $container->set(
            'notResolved',
            function () {
                return new Escaper();
            }
        );

        $actual = $container->getService('resolved')->isResolved();
        $I->assertFalse($actual);

        $actual = $container->getService('notResolved')->isResolved();
        $I->assertFalse($actual);


        $container->get('resolved');

        $actual = $container->getService('resolved')->isResolved();
        $I->assertTrue($actual);

        $actual = $container->getService('notResolved')->isResolved();
        $I->assertFalse($actual);
    }

    /**
     * @param UnitTester $I
     *
     * @throws Exception
     */
    public function testAlias(UnitTester $I)
    {
        $escaper = new Escaper();

        $container = new Di();
        $container->set('resolved', Escaper::class);
        $container->set(Escaper::class, $escaper);

        $actual = $container->get('resolved');
        $I->assertSame($escaper, $actual);
    }
}
