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

namespace Phalcon\Tests\Unit\Http\Response\Cookies;

use Phalcon\Di\Di;
use Phalcon\Http\Response\Cookies;
use UnitTester;

class GetSetDICest
{
    /**
     * Tests Phalcon\Http\Response\Cookies :: getDI() / setDI()
     *
     * @author Jeremy PASTOURET <https://github.com/jenovateurs>
     * @since  2019-12-07
     */
    public function httpResponseCookiesGetSetDI(UnitTester $I)
    {
        $I->wantToTest('Http\Response\Cookies - getDI() / setDI()');

        $container = new Di();

        $cookies = new Cookies();
        $cookies->setDI($container);

        $expected = $container;
        $actual   = $cookies->getDI();
        $I->assertSame($expected, $actual);

        $class  = Di::class;
        $actual = $cookies->getDI();
        $I->assertInstanceOf($class, $actual);
    }
}
