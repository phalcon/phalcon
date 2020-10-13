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
use Phalcon\Events\Manager;
use Phalcon\Events\ManagerInterface;
use UnitTester;

class SetInternalEventsManagerCest
{
    /**
     * Unit Tests Phalcon\Di :: setInternalEventsManager()
     *
     * @param  UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-09-09
     */
    public function diSetInternalEventsManager(UnitTester $I)
    {
        $I->wantToTest('Di - setInternalEventsManager()');

        $container = new Di();

        $actual = $container->getInternalEventsManager();
        $I->assertNull($actual);

        $container->setInternalEventsManager(new Manager());

        $class  = ManagerInterface::class;
        $actual = $container->getInternalEventsManager();
        $I->assertInstanceOf($class, $actual);
    }
}
