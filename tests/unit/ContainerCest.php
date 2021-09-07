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

namespace Phalcon\Tests\Unit;

use Phalcon\Container;
use Phalcon\Di\Di;
use Phalcon\Di\Exception;
use stdClass;
use UnitTester;

final class ContainerCest
{
    /**
     * Tests Phalcon\Container :: get
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-01-04
     */
    public function get(UnitTester $I): void
    {
        $I->wantToTest('PSR-11 Container - get');

        $di = new Di();
        $di->setShared('service', function () {
            return new stdClass();
        });

        $container = new Container($di);

        $I->expectThrowable(Exception::class, function () use ($container) {
            $container->get('empty');
        });

        $I->assertInstanceOf(stdClass::class, $container->get('service'));
    }

    /**
     * Tests Phalcon\Container :: has
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-01-04
     */
    public function has(UnitTester $I): void
    {
        $I->wantToTest('PSR-11 Container - has');

        $di = new Di();
        $di->setShared('issetService', function () {
            return true;
        });

        $container = new Container($di);

        $I->assertFalse($container->has('empty'));
        $I->assertTrue($container->has('issetService'));
    }
}
