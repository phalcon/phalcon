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

namespace Phalcon\Tests\Unit\Mvc\Dispatcher;

use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\UnitTestCase;

class GetSetDITest extends UnitTestCase
{
    use DiTrait;

    /**
     * Tests Phalcon\Mvc\Dispatcher :: getDI()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-05-22
     */
    public function testMvcDispatcherGetDI(): void
    {
        $dispatcher = new Dispatcher();

        $di = new FactoryDefault();

        $dispatcher->setDI($di);

        $this->assertEquals(
            $di,
            $dispatcher->getDI()
        );
    }
}
