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

final class GetSetTaskNameTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Cli\Dispatcher :: getTaskName()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testCliDispatcherGetTaskName(): void
    {
        $dispatcher = new Dispatcher();

        $expected = '';
        $actual   = $dispatcher->getTaskName();
        $this->assertSame($expected, $actual);

        $value = "Phalcon";
        $dispatcher->setTaskName($value);

        $expected = $value;
        $actual   = $dispatcher->getTaskName();
        $this->assertSame($expected, $actual);
    }
}
