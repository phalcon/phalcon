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

namespace Phalcon\Tests\Unit\Http\Response;

use Phalcon\Events\Manager;
use Phalcon\Http\Response;
use UnitTester;

class GetSetEventsManagerCest
{
    /**
     * Unit Tests Phalcon\Http\Response :: getEventsManager() /
     * setEventsManager()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-01-17
     */
    public function httpResponseGetSetEventsManager(UnitTester $I)
    {
        $I->wantToTest(
            'Http\Response - getEventsManager() / setEventsManager()'
        );

        $response = new Response();
        $manager  = new Manager();

        $response->setEventsManager($manager);

        $expected = $manager;
        $actual   = $response->getEventsManager();
        $I->assertSame($expected, $actual);

        $class = Manager::class;
        $I->assertInstanceOf($class, $manager);
    }
}
