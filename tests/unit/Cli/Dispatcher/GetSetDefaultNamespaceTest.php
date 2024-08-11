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
use Phalcon\Tests\AbstractUnitTestCase;

final class GetSetDefaultNamespaceTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Cli\Dispatcher :: getDefaultNamespace() /
     * setDefaultNamespace()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testCliDispatcherGetSetDefaultNamespace(): void
    {
        $dispatcher = new Dispatcher();

        $this->assertSame('', $dispatcher->getDefaultNamespace());

        $namespace = "Phalcon\Rules";
        $dispatcher->setDefaultNamespace($namespace);

        $expected = $namespace;
        $actual   = $dispatcher->getDefaultNamespace();
        $this->assertSame($expected, $actual);
    }
}
