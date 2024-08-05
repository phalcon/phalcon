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
 * Class GetSetNamespaceNameTest extends UnitTestCase
 */
final class GetSetNamespaceNameTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Cli\Dispatcher - getNamespaceName() / setNamespaceName()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testCliDispatcherGetSetNamespaceName(): void
    {
        $dispatcher = new Dispatcher();

        $expected = '';
        $actual   = $dispatcher->getNamespaceName();
        $this->assertSame($expected, $actual);

        $namespace = "Phalcon";
        $dispatcher->setNamespaceName($namespace);

        $expected = $namespace;
        $actual   = $dispatcher->getNamespaceName();
        $this->assertSame($expected, $actual);
    }
}
