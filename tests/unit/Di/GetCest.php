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

namespace Phalcon\Tests\Unit\Di;

use Phalcon\Di\Di;
use Phalcon\Di\Exception;
use Phalcon\Di\Service;
use Phalcon\Escaper\Escaper;
use UnitTester;

class GetCest
{
    /**
     * Unit Tests Phalcon\Di :: get()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-09-09
     */
    public function diGet(UnitTester $I)
    {
        $I->wantToTest('Di - get()');

        // setup
        $container = new Di();

        // set a service and get it to check
        $actual = $container->set('escaper', Escaper::class);

        $I->assertInstanceOf(Service::class, $actual);
        $I->assertFalse($actual->isShared());

        // get escaper service
        $actual   = $container->get('escaper');
        $expected = new Escaper();

        $I->assertInstanceOf(Escaper::class, $actual);
        $I->assertEquals($expected, $actual);
    }

    /**
     * Unit Tests Phalcon\Di :: get() - exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-09-09
     */
    public function diGetException(UnitTester $I)
    {
        $I->wantToTest('Di - get() - exception');

        // setup
        $container = new Di();

        // non exists service
        $I->expectThrowable(
            new Exception(
                'Service "non-exists" was not found in the ' .
                'dependency injection container'
            ),
            function () use ($container) {
                $container->get('non-exists');
            }
        );
    }
}
