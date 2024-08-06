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
 * Class GetSetModuleNameTest extends UnitTestCase
 */
final class GetSetModuleNameTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Cli\Dispatcher - getModuleName() / setModuleName()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testCliDispatcherGetModuleName(): void
    {
        $dispatcher = new Dispatcher();

        $expected = '';
        $actual   = $dispatcher->getModuleName();
        $this->assertSame($expected, $actual);

        $moduleName = "Phalcon";
        $dispatcher->setModuleName($moduleName);

        $expected = $moduleName;
        $actual   = $dispatcher->getModuleName();
        $this->assertSame($expected, $actual);
    }
}
