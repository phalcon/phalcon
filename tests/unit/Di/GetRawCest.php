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

use Exception;
use Phalcon\Di\Di;
use Phalcon\Escaper\Escaper;
use UnitTester;

class GetRawCest
{
    /**
     * Tests Phalcon\Di :: getRaw()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function diGetRaw(UnitTester $I)
    {
        $I->wantToTest('Di - getRaw()');

        $container = new Di();

        // existing service
        $container->set('escaper', Escaper::class);

        $expected = Escaper::class;
        $actual   = $container->getRaw('escaper');

        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Di :: getRaw() - exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function diGetRawException(UnitTester $I)
    {
        $I->wantToTest('Di - getRaw() - exception');

        $container = new Di();

        // nonexistent service
        $I->expectThrowable(
            new Exception(
                "Service 'nonexistent-service' was not found " .
                "in the dependency injection container"
            ),
            function () use ($container) {
                $container->getRaw('nonexistent-service');
            }
        );
    }
}
