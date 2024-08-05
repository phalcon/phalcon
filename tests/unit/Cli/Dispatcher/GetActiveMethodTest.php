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

use Phalcon\Tests\UnitTestCase;
use Phalcon\Cli\Dispatcher;

/**
 * Class GetActiveMethodTest extends UnitTestCase
 */
final class GetActiveMethodTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Cli\Dispatcher :: getActiveMethod()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testCliDispatcherGetActiveMethod(): void
    {
        $dispatcher = new Dispatcher();

        $dispatcher->setActionName("phalcon");

        $expected = "phalconAction";
        $actual   = $dispatcher->getActiveMethod();
        $this->assertSame($expected, $actual);
    }
}
