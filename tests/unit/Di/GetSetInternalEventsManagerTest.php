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
use Phalcon\Tests\AbstractUnitTestCase;

class GetSetInternalEventsManagerTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Phalcon\Di\Di ::
     * getInternalEventsManager()/setInternalEventsManager()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-09-09
     */
    public function testDiGetSetInternalEventsManager(): void
    {
        $container = new Di();

        $actual = $container->getInternalEventsManager();
        $this->assertNull($actual);

        $container->setInternalEventsManager(new Manager());

        $class  = ManagerInterface::class;
        $actual = $container->getInternalEventsManager();
        $this->assertInstanceOf($class, $actual);
    }
}
