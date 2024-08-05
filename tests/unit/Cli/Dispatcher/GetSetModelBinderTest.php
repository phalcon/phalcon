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
use Phalcon\Mvc\Model\Binder;

/**
 * Class GetSetModelBinderTest extends UnitTestCase
 */
final class GetSetModelBinderTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Cli\Dispatcher - getModelBinder() / setModelBinder()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testCliDispatcherGetModelBinder(): void
    {
        $dispatcher = new Dispatcher();
        $this->assertNull($dispatcher->getModelBinder());

        $modelBinder = new Binder();
        $dispatcher->setModelBinder($modelBinder);

        $expected = $modelBinder;
        $actual   = $dispatcher->getModelBinder();
        $this->assertSame($expected, $actual);
    }
}
