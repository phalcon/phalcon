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

use Phalcon\Cli\Dispatcher;
use Phalcon\Tests\UnitTestCase;

/**
 * Class ForwardTest extends UnitTestCase
 */
final class ForwardTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Cli\Dispatcher :: forward()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testCliDispatcherForward(): void
    {
        $dispatcher = new Dispatcher();
        $dispatcher->setDefaultNamespace('Phalcon\Tests\Fixtures\Tasks');
        $dispatcher->setActionName('hello');
        $dispatcher->forward(
            [
                'action' => 'phalcon',
            ]
        );

        $expected = 'phalcon';
        $actual   = $dispatcher->getActionName();
        $this->assertSame($expected, $actual);
    }
}
