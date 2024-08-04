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

namespace Phalcon\Tests\Unit\Dispatcher;

use Phalcon\Dispatcher\Exception;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\UnitTestCase;

final class GetSetHasParamTest extends UnitTestCase
{
    use DiTrait;

    /**
     * Tests Phalcon\Dispatcher :: getParam()/setParam()/hasParam()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-11-17
     */
    public function testDispatcherGetSetHasParam(): void
    {
        $dispatcher = new Dispatcher();

        $actual = $dispatcher->hasParam('one');
        $this->assertFalse($actual);

        $dispatcher->setParam('one', 'two');

        $actual = $dispatcher->hasParam('one');
        $this->assertTrue($actual);

        $expected = 'two';
        $actual   = $dispatcher->getParam('one');
        $this->assertSame($expected, $actual);

        $dispatcher = new Dispatcher();

        $actual = $dispatcher->hasParameter('one');
        $this->assertFalse($actual);

        $dispatcher->setParameter('one', 'two');

        $actual = $dispatcher->hasParameter('one');
        $this->assertTrue($actual);

        $expected = 'two';
        $actual   = $dispatcher->getParameter('one');
        $this->assertSame($expected, $actual);

        $expected = 'default';
        $actual   = $dispatcher->getParameter('unknown', [], 'default');
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Dispatcher :: getParam()/setParam()/hasParam()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-11-17
     */
    public function testDispatcherGetSetHasParameterFiltered(): void
    {
        $this->setNewFactoryDefault();

        $dispatcher = new Dispatcher();
        $dispatcher->setDI($this->container);

        $dispatcher->setParameter('one', '1234');

        $expected = 1234;
        $actual   = $dispatcher->getParameter('one', 'int');
        $this->assertSame($expected, $actual);
    }
}
