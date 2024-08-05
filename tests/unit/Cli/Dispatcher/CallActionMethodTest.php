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
use Phalcon\Tests\Fixtures\Tasks\MainTask;

final class CallActionMethodTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Cli\Dispatcher :: callActionMethod()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-06-24
     */
    public function testCliDispatcherCallActionMethod(): void
    {
        $dispatcher = new Dispatcher();

        $task = new MainTask();

        $returnedValue = $dispatcher->callActionMethod(
            $task,
            'helloAction',
            [
                'Sid',
                '.',
            ]
        );

        $expected = 'Hello Sid.';
        $actual   = $returnedValue;
        $this->assertSame($expected, $actual);
    }
}
