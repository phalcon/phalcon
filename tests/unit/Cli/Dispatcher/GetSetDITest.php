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

namespace Phalcon\Tests\Unit\Cli\Dispatcher;

use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\UnitTestCase;

final class GetSetDITest extends UnitTestCase
{
    use DiTrait;

    /**
     * Tests Phalcon\Cli\Dispatcher :: getDI()/setDI()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testCliDispatcherGetSetDI(): void
    {
        $container = $this->newService('cliFactoryDefault');
        $console   = $this->newService('console');

        $console->setDI($container);

        $actual = $console->getDI();
        $this->assertSame($container, $actual);
    }
}
